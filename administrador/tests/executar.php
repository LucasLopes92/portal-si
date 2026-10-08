<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/autenticacao.php';
require_once __DIR__ . '/../models/UsuarioAdmin.php';
require_once __DIR__ . '/../models/ConteudoAdmin.php';
require_once __DIR__ . '/../models/PainelAdmin.php';
require_once __DIR__ . '/../services/AutenticacaoAdminService.php';
require_once __DIR__ . '/../services/ConteudoAdminService.php';
require_once __DIR__ . '/../controllers/ConteudoAdminController.php';
require_once __DIR__ . '/../../app/models/Conteudo.php';

/** @var array<int, string> $resultados */
$resultados = [];

function teste_garantir(bool $condicao, string $mensagem): void
{
    if (!$condicao) {
        throw new RuntimeException($mensagem);
    }
}

function teste_etapa(string $nome, callable $teste): void
{
    global $resultados;

    $teste();
    $resultados[] = $nome;
}

function teste_contagens(PDO $pdo): array
{
    return [
        'usuarios' => (int) $pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn(),
        'categorias' => (int) $pdo->query('SELECT COUNT(*) FROM categorias')->fetchColumn(),
        'conteudos' => (int) $pdo->query('SELECT COUNT(*) FROM conteudos')->fetchColumn(),
    ];
}

$contagensIniciais = teste_contagens($pdo);
$erro = null;
$pdo->beginTransaction();

try {
    teste_etapa('estrutura obrigatória do banco', function () use ($pdo): void {
        $tabelasObrigatorias = [
            'auditoria',
            'categorias',
            'conteudo_tags',
            'conteudos',
            'eventos',
            'midias',
            'tags',
            'usuarios',
        ];
        $tabelas = $pdo->query(
            "SELECT table_name
             FROM information_schema.tables
             WHERE table_schema = 'public'"
        )->fetchAll(PDO::FETCH_COLUMN);

        foreach ($tabelasObrigatorias as $tabela) {
            teste_garantir(in_array($tabela, $tabelas, true), "Tabela obrigatória ausente: {$tabela}.");
        }

        $colunas = $pdo->query(
            "SELECT column_name
             FROM information_schema.columns
             WHERE table_schema = 'public' AND table_name = 'conteudos'"
        )->fetchAll(PDO::FETCH_COLUMN);

        foreach (['slug', 'corpo', 'autor_id', 'status', 'destaque', 'publicado_em'] as $coluna) {
            teste_garantir(in_array($coluna, $colunas, true), "Coluna obrigatória ausente: conteudos.{$coluna}.");
        }
    });

    $sufixo = bin2hex(random_bytes(6));
    $email = "teste.painel.{$sufixo}@example.test";
    $senha = 'Teste-Painel-2026!';
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $stmtUsuario = $pdo->prepare(
        "INSERT INTO usuarios (nome, email, senha, senha_hash, perfil, status)
         VALUES (:nome, :email, :senha_legada, :senha_hash, 'admin', 'ativo')
         RETURNING id"
    );
    $stmtUsuario->execute([
        'nome' => 'Usuário Temporário do Painel',
        'email' => $email,
        'senha_legada' => $senhaHash,
        'senha_hash' => $senhaHash,
    ]);
    $autorId = (int) $stmtUsuario->fetchColumn();

    teste_etapa('autenticação e autorização por perfil', function () use ($pdo, $email, $senha): void {
        iniciar_sessao_segura();
        $service = new AutenticacaoAdminService(new UsuarioAdmin($pdo));

        $_SESSION = [];
        teste_garantir(!$service->autenticar($email, 'senha-incorreta'), 'Senha incorreta foi aceita.');

        $_SESSION = [];
        teste_garantir($service->autenticar($email, $senha), 'Perfil admin não conseguiu autenticar.');
        teste_garantir(admin_perfil_autorizado(), 'Perfil admin não recebeu autorização.');

        $stmt = $pdo->prepare("UPDATE usuarios SET perfil = 'editor' WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $_SESSION = [];
        teste_garantir($service->autenticar($email, $senha), 'Perfil editor não conseguiu autenticar.');
        teste_garantir(admin_perfil_autorizado(), 'Perfil editor não recebeu autorização.');

        $stmt = $pdo->prepare("UPDATE usuarios SET perfil = 'aluno' WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $_SESSION = [];
        teste_garantir(!$service->autenticar($email, $senha), 'Perfil aluno recebeu acesso administrativo.');

        $stmt = $pdo->prepare("UPDATE usuarios SET perfil = 'admin' WHERE email = :email");
        $stmt->execute(['email' => $email]);
    });

    teste_etapa('CSRF e escape de saída', function (): void {
        $_SESSION = [];
        $token = obter_token_csrf();

        teste_garantir(strlen($token) === 64, 'Token CSRF não possui o tamanho esperado.');
        teste_garantir(validar_token_csrf($token), 'Token CSRF válido foi rejeitado.');
        teste_garantir(!validar_token_csrf(str_repeat('0', 64)), 'Token CSRF inválido foi aceito.');
        teste_garantir(
            e('<script>alert("x")</script>') === '&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;',
            'Escape HTML não protegeu o conteúdo.'
        );
    });

    $categoriaId = (int) $pdo->query(
        'SELECT id FROM categorias WHERE ativo = TRUE ORDER BY ordem, id LIMIT 1'
    )->fetchColumn();
    teste_garantir($categoriaId > 0, 'Não existe categoria ativa para testar o CRUD.');

    $conteudoModel = new ConteudoAdmin($pdo);
    $conteudoService = new ConteudoAdminService($conteudoModel);
    $conteudoController = new ConteudoAdminController($conteudoModel, $conteudoService);
    $tituloCriado = "Teste integrado do painel {$sufixo}";
    $conteudoId = 0;

    teste_etapa('validação do cadastro', function () use ($conteudoService, $autorId): void {
        $resultado = $conteudoService->criar([
            'titulo' => '',
            'categoria_id' => '0',
            'corpo' => '',
            'link_youtube' => 'https://example.com/video',
            'status' => 'inexistente',
        ], $autorId);

        teste_garantir(!$resultado['sucesso'], 'Cadastro inválido foi aceito.');
        foreach (['titulo', 'categoria_id', 'corpo', 'link_youtube', 'status'] as $campo) {
            teste_garantir(isset($resultado['erros'][$campo]), "Validação ausente para o campo {$campo}.");
        }
    });

    teste_etapa('cadastro e listagem paginada', function () use (
        $conteudoController,
        $conteudoModel,
        $autorId,
        $categoriaId,
        $tituloCriado,
        &$conteudoId
    ): void {
        $resultado = $conteudoController->cadastrar([
            'titulo' => $tituloCriado,
            'categoria_id' => (string) $categoriaId,
            'resumo' => 'Resumo criado pelo teste automatizado.',
            'corpo' => 'Texto completo criado dentro de uma transação de teste.',
            'link_youtube' => 'https://youtu.be/dQw4w9WgXcQ',
            'status' => 'publicado',
            'destaque' => '1',
        ], $autorId);

        teste_garantir($resultado['sucesso'], 'Cadastro válido falhou.');
        $conteudoId = (int) $resultado['id'];
        $salvo = $conteudoModel->buscarPorId($conteudoId);
        teste_garantir($salvo !== null, 'Conteúdo cadastrado não foi localizado.');
        teste_garantir($salvo['status'] === 'publicado', 'Status publicado não foi salvo.');
        teste_garantir($salvo['publicado_em'] !== null, 'Data de publicação não foi preenchida.');

        $paginacao = $conteudoController->listar(1, 2);
        teste_garantir(count($paginacao['itens']) <= 2, 'Limite da paginação não foi respeitado.');
        teste_garantir($paginacao['total'] >= 1, 'Total da paginação está incorreto.');
    });

    teste_etapa('integração com a Home pública', function () use ($pdo, $conteudoId, $tituloCriado): void {
        $publicados = (new Conteudo($pdo))->listarPublicados(1000);
        $ids = array_map(static fn (array $item): int => (int) $item['id'], $publicados);
        teste_garantir(in_array($conteudoId, $ids, true), 'Conteúdo publicado não foi retornado pelo portal público.');

        $_GET = [];
        ob_start();
        require __DIR__ . '/../../public/index.php';
        $html = (string) ob_get_clean();
        teste_garantir(str_contains($html, e($tituloCriado)), 'Conteúdo publicado não apareceu no HTML da Home.');
    });

    teste_etapa('resumo do dashboard', function () use ($pdo, $contagensIniciais): void {
        $resumo = (new PainelAdmin($pdo))->obterResumo();
        teste_garantir(
            $resumo['total_conteudos'] === $contagensIniciais['conteudos'] + 1,
            'Dashboard não contabilizou o novo conteúdo.'
        );
        teste_garantir($resumo['categorias_ativas'] > 0, 'Dashboard não contabilizou as categorias ativas.');
    });

    $tituloEditado = "Teste integrado editado {$sufixo}";
    teste_etapa('edição e retirada da Home', function () use (
        $conteudoController,
        $conteudoModel,
        $pdo,
        $conteudoId,
        $categoriaId,
        $tituloEditado
    ): void {
        $resultado = $conteudoController->editar($conteudoId, [
            'titulo' => $tituloEditado,
            'categoria_id' => (string) $categoriaId,
            'resumo' => 'Resumo alterado pelo teste.',
            'corpo' => 'Texto alterado pelo fluxo automatizado.',
            'link_youtube' => '',
            'status' => 'arquivado',
        ]);

        teste_garantir($resultado['sucesso'], 'Edição válida falhou.');
        $editado = $conteudoModel->buscarPorId($conteudoId);
        teste_garantir($editado !== null && $editado['titulo'] === $tituloEditado, 'Título não foi atualizado.');
        teste_garantir($editado['status'] === 'arquivado', 'Conteúdo não foi arquivado.');
        teste_garantir($editado['publicado_em'] === null, 'Conteúdo arquivado manteve a data de publicação.');

        $publicados = (new Conteudo($pdo))->listarPublicados(1000);
        $ids = array_map(static fn (array $item): int => (int) $item['id'], $publicados);
        teste_garantir(!in_array($conteudoId, $ids, true), 'Conteúdo arquivado continuou na Home.');
    });

    teste_etapa('exclusão definitiva', function () use ($conteudoController, $conteudoModel, $conteudoId): void {
        $resultado = $conteudoController->excluir($conteudoId);
        teste_garantir($resultado['sucesso'], 'Exclusão válida falhou.');
        teste_garantir($conteudoModel->buscarPorId($conteudoId) === null, 'Conteúdo excluído ainda existe.');

        $repetida = $conteudoController->excluir($conteudoId);
        teste_garantir(!$repetida['sucesso'] && $repetida['nao_encontrado'], 'Exclusão repetida não foi tratada.');
    });

    teste_etapa('proteção das rotas administrativas', function (): void {
        foreach ([
            'index.php',
            'conteudos_listar.php',
            'conteudos_cadastrar.php',
            'conteudos_editar.php',
            'conteudos_excluir.php',
        ] as $arquivo) {
            $codigo = (string) file_get_contents(__DIR__ . '/../' . $arquivo);
            teste_garantir(
                str_contains($codigo, 'admin_exigir_acesso();'),
                "Rota sem verificação administrativa: {$arquivo}."
            );
        }

        $codigoExclusao = (string) file_get_contents(__DIR__ . '/../conteudos_excluir.php');
        teste_garantir(str_contains($codigoExclusao, "REQUEST_METHOD'] === 'POST'"), 'Exclusão não exige POST.');
        teste_garantir(str_contains($codigoExclusao, 'validar_token_csrf'), 'Exclusão não valida o token CSRF.');
    });
} catch (Throwable $exception) {
    $erro = $exception;
} finally {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION = [];
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
}

try {
    teste_garantir(
        teste_contagens($pdo) === $contagensIniciais,
        'Os registros temporários não foram removidos ao final.'
    );
    $resultados[] = 'rollback dos registros temporários';
} catch (Throwable $exception) {
    $erro ??= $exception;
}

foreach ($resultados as $indice => $resultado) {
    printf("[OK] %02d - %s\n", $indice + 1, $resultado);
}

if ($erro !== null) {
    fwrite(STDERR, '[FALHOU] ' . $erro->getMessage() . PHP_EOL);
    exit(1);
}

printf("\nTodos os %d testes do painel passaram.\n", count($resultados));

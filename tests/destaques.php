<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/services/ConteudoAdminService.php';
require __DIR__ . '/../app/models/Conteudo.php';

function garantir_destaque(bool $ok, string $mensagem): void
{
    if (!$ok) { throw new RuntimeException($mensagem); }
}
$pdo->beginTransaction();
try {
    $autor = (int) $pdo->query("SELECT id FROM usuarios WHERE perfil='admin' AND status='ativo' ORDER BY id LIMIT 1")->fetchColumn();
    $categoria = (int) $pdo->query("SELECT id FROM categorias WHERE slug='noticias' AND ativo=TRUE")->fetchColumn();
    $admin = new ConteudoAdmin($pdo);
    $service = new ConteudoAdminService($admin);
    $publico = new Conteudo($pdo);
    $sufixo = bin2hex(random_bytes(5));
    $ids = [];
    for ($i = 0; $i < 4; $i++) {
        $resultado = $service->criar(['titulo' => "Destaque teste {$sufixo} {$i}", 'categoria_id' => $categoria, 'corpo' => 'Teste temporário de destaque.', 'status' => 'publicado'], $autor);
        garantir_destaque($resultado['sucesso'], 'Falha ao criar conteúdo temporário.');
        $ids[] = $resultado['id'];
    }
    $antigo = $pdo->prepare("UPDATE conteudos SET publicado_em='2001-01-01 12:00:00-03' WHERE id=:id");
    $antigo->execute(['id' => $ids[0]]);
    $alterar = static function (int $id, bool $marcado, string $status = 'publicado') use ($service, $admin): void {
        $item = $admin->buscarPorId($id);
        $dados = ['titulo' => $item['titulo'], 'categoria_id' => $item['categoria_id'], 'corpo' => $item['corpo'], 'status' => $status];
        if ($marcado) { $dados['destaque'] = '1'; }
        garantir_destaque($service->atualizar($id, $dados)['sucesso'], 'Falha ao alterar destaque.');
    };
    $primeiro = static fn (): int => (int) $publico->listarPublicados(1, $categoria, 0, [], true)[0]['id'];
    $instante = static function (int $id) use ($pdo): ?string {
        $q = $pdo->prepare('SELECT destaque_em FROM conteudos WHERE id=:id'); $q->execute(['id' => $id]);
        return $q->fetchColumn() ?: null;
    };
    $alterar($ids[0], true);
    garantir_destaque($primeiro() === $ids[0], 'Uma publicação antiga marcada agora não chegou à manchete.');
    $horaOriginal = $instante($ids[0]);
    $alterar($ids[1], true);
    garantir_destaque($primeiro() === $ids[1], 'O destaque marcado mais recentemente não ficou primeiro.');
    $item = $admin->buscarPorId($ids[0]);
    $service->atualizar($ids[0], ['titulo' => $item['titulo'] . ' editado', 'categoria_id' => $categoria, 'corpo' => 'Texto revisado.', 'status' => 'publicado', 'destaque' => '1']);
    garantir_destaque($instante($ids[0]) === $horaOriginal && $primeiro() === $ids[1], 'Editar o texto mudou indevidamente a prioridade.');
    $alterar($ids[1], true, 'arquivado');
    garantir_destaque($primeiro() === $ids[0], 'Destaque arquivado continuou na manchete.');
    $alterar($ids[1], true);
    garantir_destaque($primeiro() === $ids[1], 'Destaque republicado perdeu sua prioridade original.');
    $alterar($ids[1], false);
    garantir_destaque($instante($ids[1]) === null && $primeiro() === $ids[0], 'Desmarcar não liberou a manchete.');
    $alterar($ids[2], true);
    $alterar($ids[0], false);
    $alterar($ids[0], true);
    garantir_destaque($primeiro() === $ids[0] && $instante($ids[0]) > $horaOriginal, 'Remarcar não promoveu o conteúdo.');
    $todos = $publico->listarPublicados(null, $categoria, 0, [], true);
    $paginados = [];
    for ($offset = 0; $offset < count($todos); $offset += 6) {
        $paginados = array_merge($paginados, $publico->listarPublicados(6, $categoria, $offset, [], true));
    }
    garantir_destaque(array_column($todos, 'id') === array_column($paginados, 'id'), 'A paginação perdeu ou repetiu itens.');
    $porData = $publico->listarPublicados(null, $categoria, 0, ['palavra' => $sufixo]);
    garantir_destaque((int) end($porData)['id'] === $ids[0], 'A busca não manteve a ordem cronológica.');
    echo "[OK] Marcação recente, publicação antiga, edição, arquivamento, remoção, remarcação, busca e paginação.\n";
} finally {
    $pdo->rollBack();
}

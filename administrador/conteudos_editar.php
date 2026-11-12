<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/admin.php';
require_once __DIR__ . '/../app/models/ConteudoAdmin.php';
require_once __DIR__ . '/../app/services/ConteudoAdminService.php';
require_once __DIR__ . '/../app/controllers/ConteudoAdminController.php';
require_once __DIR__ . '/../app/services/ImagensPendentesService.php';

admin_exigir_acesso();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!is_int($id) || $id <= 0) {
    admin_definir_flash('error', 'Identificador de conteúdo inválido.');
    header('Location: ' . admin_url('conteudos_listar.php'));
    exit;
}

$model = new ConteudoAdmin($pdo);
$controller = new ConteudoAdminController($model, new ConteudoAdminService($model));
$conteudo = $controller->buscar($id);

if ($conteudo === null) {
    admin_definir_flash('error', 'Conteúdo não encontrado.');
    header('Location: ' . admin_url('conteudos_listar.php'));
    exit;
}

$categorias = $controller->categorias();
$erros = [];
$avisoImagens = null;
$imagensPendentes = new ImagensPendentesService();
$loteImagens = $imagensPendentes->lote($_POST['lote_imagens'] ?? null);
$dados = [
    'titulo' => $conteudo['titulo'],
    'categoria_id' => (int) $conteudo['categoria_id'],
    'resumo' => $conteudo['resumo'] ?? '',
    'corpo' => $conteudo['corpo'],
    'imagem_capa' => $conteudo['imagem_capa'] ?? '',
    'link_youtube' => $conteudo['link_youtube'] ?? '',
    'status' => $conteudo['status'],
    'destaque' => (bool) $conteudo['destaque'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $erros['imagens'] = 'O envio excedeu o limite do servidor. Envie até 6 MB de imagens por vez.';
    } elseif (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erros['geral'] = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
        $dados = array_merge($dados, $_POST);
    } else {
        // As imagens vão primeiro para a pasta temporária, para aparecerem na prévia do formulário.
        $removerExistentes = is_array($_POST['remover_imagens'] ?? null) ? $_POST['remover_imagens'] : [];
        $totalExistentes = count(imagens_publicacao($conteudo));
        $mantidasExistentes = count(array_filter(
            range(0, max(0, $totalExistentes - 1)),
            fn (int $indice): bool => $indice < $totalExistentes && !in_array((string) $indice, $removerExistentes, true)
        ));
        $erroImagens = $imagensPendentes->atualizar($loteImagens, $_POST['remover_pendentes'] ?? null, $_FILES['imagens'] ?? [], $mantidasExistentes);
        $dados['remover_imagens'] = $removerExistentes;

        if (($_POST['acao'] ?? '') === 'enviar_imagens' || $erroImagens !== null) {
            $dados = array_merge($dados, array_intersect_key($_POST, $dados));
            $dados['destaque'] = isset($_POST['destaque']);
            if ($erroImagens !== null) {
                $erros['imagens'] = $erroImagens;
            } else {
                $avisoImagens = 'Imagens atualizadas. Confira a prévia abaixo e salve as alterações para publicá-las.';
            }
        } else {
            $resultado = $controller->editar($id, $_POST, [], $imagensPendentes->listar($loteImagens));
            $erros = $resultado['erros'];
            $dados = array_merge($dados, $resultado['dados']);
        }

        if (isset($resultado) && $resultado['sucesso']) {
            $imagensPendentes->limpar($loteImagens);
            admin_renovar_csrf();
            admin_definir_flash('sucesso', 'Conteúdo atualizado com sucesso.');
            header('Location: ' . admin_url('conteudos_listar.php'));
            exit;
        }

        if (!empty($resultado['nao_encontrado'])) {
            $imagensPendentes->limpar($loteImagens);
            admin_definir_flash('error', 'Conteúdo não encontrado.');
            header('Location: ' . admin_url('conteudos_listar.php'));
            exit;
        }
    }
}

$tituloPagina = 'Editar conteúdo';
$paginaAtual = 'conteudos';
$tituloFormulario = 'Editar conteúdo #' . $id;
$descricaoFormulario = 'Atualize as informações editoriais e o estado de publicação.';
$acaoFormulario = admin_url('conteudos_editar.php?id=' . $id);
$rotuloBotao = 'Salvar alterações';
$statusDisponiveis = [
    'rascunho' => 'Rascunho',
    'publicado' => 'Publicado',
    'arquivado' => 'Arquivado',
];

require __DIR__ . '/../app/views/admin/painel/layout/header.php';
require __DIR__ . '/../app/views/admin/painel/conteudos/formulario.php';
require __DIR__ . '/../app/views/admin/painel/layout/footer.php';

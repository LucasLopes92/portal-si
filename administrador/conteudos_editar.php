<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacao.php';
require_once __DIR__ . '/models/ConteudoAdmin.php';
require_once __DIR__ . '/services/ConteudoAdminService.php';
require_once __DIR__ . '/controllers/ConteudoAdminController.php';

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
$dados = [
    'titulo' => $conteudo['titulo'],
    'categoria_id' => (int) $conteudo['categoria_id'],
    'resumo' => $conteudo['resumo'] ?? '',
    'corpo' => $conteudo['corpo'],
    'link_youtube' => $conteudo['link_youtube'] ?? '',
    'status' => $conteudo['status'],
    'destaque' => (bool) $conteudo['destaque'],
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erros['geral'] = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
        $dados = array_merge($dados, $_POST);
    } else {
        $resultado = $controller->editar($id, $_POST);
        $erros = $resultado['erros'];
        $dados = array_merge($dados, $resultado['dados']);

        if ($resultado['sucesso']) {
            admin_renovar_csrf();
            admin_definir_flash('sucesso', 'Conteúdo atualizado com sucesso.');
            header('Location: ' . admin_url('conteudos_listar.php'));
            exit;
        }

        if (!empty($resultado['nao_encontrado'])) {
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

require __DIR__ . '/includes/header.php';
require __DIR__ . '/views/conteudos/formulario.php';
require __DIR__ . '/includes/footer.php';

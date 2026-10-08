<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacao.php';
require_once __DIR__ . '/models/ConteudoAdmin.php';
require_once __DIR__ . '/services/ConteudoAdminService.php';
require_once __DIR__ . '/controllers/ConteudoAdminController.php';

admin_exigir_acesso();

$metodoPost = $_SERVER['REQUEST_METHOD'] === 'POST';
$idInformado = $metodoPost ? ($_POST['id'] ?? null) : filter_input(INPUT_GET, 'id');
$id = filter_var($idInformado, FILTER_VALIDATE_INT);

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

$erro = null;
if ($metodoPost) {
    if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erro = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
    } else {
        $resultado = $controller->excluir($id);

        if ($resultado['sucesso']) {
            admin_renovar_csrf();
            admin_definir_flash('sucesso', 'Conteúdo excluído com sucesso.');
            header('Location: ' . admin_url('conteudos_listar.php'));
            exit;
        }

        admin_definir_flash('error', (string) $resultado['erro']);
        header('Location: ' . admin_url('conteudos_listar.php'));
        exit;
    }
}

$tituloPagina = 'Excluir conteúdo';
$paginaAtual = 'conteudos';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/views/conteudos/excluir.php';
require __DIR__ . '/includes/footer.php';

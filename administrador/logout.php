<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/models/UsuarioAdmin.php';
require_once __DIR__ . '/services/AutenticacaoAdminService.php';
require_once __DIR__ . '/controllers/AutenticacaoAdminController.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método não permitido.');
}

if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Token de segurança inválido.');
}

$controller = new AutenticacaoAdminController(
    new AutenticacaoAdminService(new UsuarioAdmin($pdo))
);
$controller->logout();

header('Location: ' . admin_url('login.php?msg=desconectado'));
exit;

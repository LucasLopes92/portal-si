<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/admin.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Allow: POST');
    http_response_code(405);
    exit('Método não permitido.');
}

if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Token de segurança inválido.');
}

admin_encerrar_sessao_atual();

header('Location: ' . app_base_url() . '/public/index.php?msg=desconectado');
exit;

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacao.php';
require_once __DIR__ . '/models/PainelAdmin.php';
require_once __DIR__ . '/controllers/PainelAdminController.php';

admin_exigir_acesso();

$controller = new PainelAdminController(new PainelAdmin($pdo));
$resumo = $controller->resumo();
$tituloPagina = 'Visão geral';
$paginaAtual = 'dashboard';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/views/dashboard.php';
require __DIR__ . '/includes/footer.php';

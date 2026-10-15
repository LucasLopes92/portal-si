<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/admin.php';
require_once __DIR__ . '/../app/models/PainelAdmin.php';
require_once __DIR__ . '/../app/controllers/PainelAdminController.php';

admin_exigir_acesso();

$controller = new PainelAdminController(new PainelAdmin($pdo));
$resumo = $controller->resumo();
$tituloPagina = 'Visão geral';
$paginaAtual = 'dashboard';

require __DIR__ . '/../app/views/admin/painel/layout/header.php';
require __DIR__ . '/../app/views/admin/painel/dashboard.php';
require __DIR__ . '/../app/views/admin/painel/layout/footer.php';

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacao.php';
require_once __DIR__ . '/models/ConteudoAdmin.php';
require_once __DIR__ . '/controllers/ConteudoAdminController.php';

admin_exigir_acesso();

$pagina = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
$pagina = is_int($pagina) && $pagina > 0 ? $pagina : 1;

$controller = new ConteudoAdminController(new ConteudoAdmin($pdo));
$paginacao = $controller->listar($pagina);
$flash = admin_consumir_flash();
$tituloPagina = 'Conteúdos';
$paginaAtual = 'conteudos';

require __DIR__ . '/includes/header.php';
require __DIR__ . '/views/conteudos/listar.php';
require __DIR__ . '/includes/footer.php';

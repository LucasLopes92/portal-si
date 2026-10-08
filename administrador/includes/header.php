<?php

declare(strict_types=1);

$tituloPagina = $tituloPagina ?? 'Painel administrativo';
$paginaAtual = $paginaAtual ?? '';
$cssAdmin = __DIR__ . '/../assets/css/admin.css';
$cssVersao = is_file($cssAdmin)
    ? substr((string) hash_file('sha256', $cssAdmin), 0, 12)
    : '1';
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($tituloPagina) ?> | Administração Portal SI</title>
    <link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css?v=' . $cssVersao)) ?>">
</head>
<body class="admin-shell">
    <a class="admin-skip-link" href="#conteudo-admin">Ir para o conteúdo</a>

    <aside class="admin-sidebar">
        <a class="admin-brand" href="<?= e(admin_url()) ?>" aria-label="Página inicial da administração">
            <span class="admin-brand__mark" aria-hidden="true">e</span>
            <span><strong>ESUCRI</strong><small>Administração Portal SI</small></span>
        </a>

        <nav class="admin-navigation" aria-label="Navegação administrativa">
            <a<?= $paginaAtual === 'dashboard' ? ' class="is-current" aria-current="page"' : '' ?> href="<?= e(admin_url()) ?>">Visão geral</a>
            <a<?= $paginaAtual === 'conteudos' ? ' class="is-current" aria-current="page"' : '' ?> href="<?= e(admin_url('conteudos_listar.php')) ?>">Conteúdos</a>
            <a href="<?= e(app_base_url() . '/public/index.php') ?>">Ver portal público</a>
        </nav>

        <div class="admin-sidebar__account">
            <span>Usuário conectado</span>
            <strong><?= e((string) ($_SESSION['usuario_nome'] ?? '')) ?></strong>
            <small><?= e((string) ($_SESSION['usuario_perfil'] ?? '')) ?></small>
            <form method="post" action="<?= e(admin_url('logout.php')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">
                <button type="submit">Encerrar sessão</button>
            </form>
        </div>
    </aside>

    <div class="admin-workspace">
        <header class="admin-topbar">
            <div>
                <p>Portal de Comunicação SI</p>
                <strong><?= e($tituloPagina) ?></strong>
            </div>
            <a href="<?= e(app_base_url() . '/public/index.php') ?>">Abrir Home <span aria-hidden="true">↗</span></a>
        </header>

        <main id="conteudo-admin" class="admin-main">

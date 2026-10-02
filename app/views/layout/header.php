<?php
// O ponto de entrada fornece $titulo_pagina e $categorias_menu.
$url_home = app_base_url() . '/public/index.php';
$url_login = app_base_url() . '/app/views/admin/login.php';
$url_painel = app_base_url() . '/app/views/admin/dashboard.php';
$url_sair = app_base_url() . '/app/views/admin/logout.php';
$mostrar_aviso_logout = ($_GET['msg'] ?? null) === 'desconectado' && !isset($_SESSION['usuario_id']);
$css_version = substr((string) hash_file('sha256', __DIR__ . '/../../../public/assets/css/style.css'), 0, 12);
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Portal de Comunicação do curso de Sistemas de Informação das Faculdades ESUCRI.">
    <title><?= e($titulo_pagina ?? 'Portal SI') ?> | Portal SI - ESUCRI</title>
    <link rel="stylesheet" href="<?= e(app_base_url()) ?>/public/assets/css/style.css?v=<?= e($css_version) ?>">
</head>
<body class="portal-page">
    <a class="skip-link" href="#conteudo">Ir para o conteúdo</a>
    <?php if ($mostrar_aviso_logout): ?>
        <div class="portal-logout-notice" role="status">Logout realizado com sucesso.</div>
    <?php endif; ?>
    <div class="site-topbar"><div class="site-topbar__inner">Faculdades ESUCRI <span aria-hidden="true">·</span> Sistemas de Informação</div></div>
    <header class="site-header portal-header">
        <div class="site-header__inner portal-header__inner">
            <a class="brand" href="<?= e($url_home) ?>" aria-label="Portal SI ESUCRI - página inicial">
                <span class="brand__mark" aria-hidden="true">e</span>
                <span><span class="brand__name">ESUCRI</span><span class="brand__sub">PORTAL DE COMUNICAÇÃO SI</span></span>
            </a>
            <?php if (isset($_SESSION['usuario_id'])): ?>
                <div class="portal-header__identity portal-header__identity--signed-in">Olá, <strong><?= e((string) ($_SESSION['usuario_nome'] ?? '')) ?></strong></div>
            <?php else: ?>
                <div class="portal-header__identity">Um espaço para conectar ideias, pessoas e oportunidades.</div>
            <?php endif; ?>
        </div>
        <nav class="portal-nav" aria-label="Navegação principal">
            <div class="portal-nav__inner">
                <a class="portal-nav__home<?= $slug_categoria === null ? ' is-current' : '' ?>" href="<?= e($url_home) ?>"<?= $slug_categoria === null ? ' aria-current="page"' : '' ?>>Início</a>
                <ul class="portal-nav__list portal-nav__list--desktop">
                    <?php foreach ($categorias_menu as $categoria): ?>
                        <?php $ativa = $categoria_atual !== null && $categoria_atual['id'] === $categoria['id']; ?>
                        <li><a href="<?= e($url_home . '?categoria=' . rawurlencode($categoria['slug'])) ?>"<?= $ativa ? ' class="is-current" aria-current="page"' : '' ?>><?= e($categoria['nome']) ?></a></li>
                    <?php endforeach; ?>
                </ul>
                <details class="portal-nav__categories">
                    <summary><?= $categoria_atual === null ? 'Categorias' : e($categoria_atual['nome']) ?></summary>
                    <ul class="portal-nav__list">
                        <?php foreach ($categorias_menu as $categoria): ?>
                            <?php $ativa = $categoria_atual !== null && $categoria_atual['id'] === $categoria['id']; ?>
                            <li><a href="<?= e($url_home . '?categoria=' . rawurlencode($categoria['slug'])) ?>"<?= $ativa ? ' class="is-current" aria-current="page"' : '' ?>><?= e($categoria['nome']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </details>
                <div class="portal-nav__account">
                    <?php if (isset($_SESSION['usuario_id'])): ?>
                        <a class="portal-nav__account-action" href="<?= e($url_painel) ?>">Painel</a>
                        <a class="portal-nav__account-action portal-nav__account-action--secondary" href="<?= e($url_sair) ?>">Sair</a>
                    <?php else: ?>
                        <a class="portal-nav__account-action" href="<?= e($url_login) ?>">Fazer login</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>
    <main id="conteudo" class="portal-main">

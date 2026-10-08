<?php

declare(strict_types=1);

require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/seguranca.php';
require_login();

$urlHome = app_base_url() . '/public/index.php';
$cssVersion = substr((string) hash_file('sha256', __DIR__ . '/../../../public/assets/css/style.css'), 0, 12);
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - Portal SI</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css?v=<?= e($cssVersion) ?>">
</head>
<body class="dashboard-page">
    <div class="site-topbar">Faculdades ESUCRI · Portal de Comunicação SI</div>
    <header class="site-header">
        <div class="site-header__inner">
            <a class="brand" href="<?= e($urlHome) ?>" aria-label="ESUCRI — ir para a Home do Portal SI">
                <span class="brand__mark" aria-hidden="true">e</span>
                <span><span class="brand__name">ESUCRI</span><span class="brand__sub">PORTAL DE COMUNICAÇÃO SI</span></span>
            </a>
        </div>
    </header>
    <main class="dashboard-main">
        <section class="dashboard-hero" aria-labelledby="titulo-painel">
            <div class="dashboard-hero__intro">
                <p class="auth-card__eyebrow">Painel administrativo</p>
                <h1 id="titulo-painel">Olá, <?= e($_SESSION['usuario_nome'] ?? '') ?></h1>
                <p>Perfil de acesso: <strong><?= e($_SESSION['usuario_perfil'] ?? '') ?></strong></p>
            </div>
            <a class="dashboard-hero__home" href="<?= e($urlHome) ?>">Ir para a Home <span aria-hidden="true">↗</span></a>
        </section>
        <section class="dashboard-grid" aria-label="Módulos do portal">
            <article class="dashboard-tile"><div class="dashboard-tile__accent"></div><h2>Conteúdos</h2><p>Gerencie notícias, projetos e comunicados do curso.</p></article>
            <article class="dashboard-tile"><div class="dashboard-tile__accent"></div><h2>Eventos</h2><p>Organize palestras, encontros e atividades acadêmicas.</p></article>
            <article class="dashboard-tile"><div class="dashboard-tile__accent"></div><h2>Conta</h2><p><a href="logout.php">Encerrar sessão com segurança</a></p></article>
        </section>
    </main>
    <footer class="site-footer"><div class="site-footer__inner"><p>ESUCRI · Criciúma - SC</p><p>Portal de Comunicação SI</p></div></footer>
</body>
</html>

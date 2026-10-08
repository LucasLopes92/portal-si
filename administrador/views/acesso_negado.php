<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Acesso negado | Portal SI</title>
    <link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>">
</head>
<body class="admin-foundation">
    <main class="foundation-card" aria-labelledby="titulo-acesso-negado">
        <p class="foundation-card__eyebrow">Acesso restrito</p>
        <h1 id="titulo-acesso-negado">403 — Acesso negado</h1>
        <p>Seu perfil não possui permissão para utilizar o painel administrativo.</p>
        <a class="foundation-card__link" href="<?= e(admin_url('login.php')) ?>">Voltar ao login</a>
    </main>
</body>
</html>

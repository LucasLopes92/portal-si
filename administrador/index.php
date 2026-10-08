<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacao.php';

admin_exigir_acesso();

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Painel Administrativo | Portal SI</title>
    <link rel="stylesheet" href="assets/css/admin.css">
</head>
<body class="admin-foundation">
    <main class="foundation-card" aria-labelledby="titulo-painel">
        <p class="foundation-card__eyebrow">Portal SI · ESUCRI</p>
        <h1 id="titulo-painel">Painel administrativo em preparação</h1>
        <p>Olá, <strong><?= e((string) ($_SESSION['usuario_nome'] ?? '')) ?></strong>. Sua sessão administrativa está protegida e ativa.</p>
        <div class="foundation-card__actions">
            <a class="foundation-card__link" href="../public/index.php">Voltar ao portal</a>
            <form method="post" action="<?= e(admin_url('logout.php')) ?>">
                <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">
                <button class="foundation-card__link foundation-card__link--secondary" type="submit">Sair</button>
            </form>
        </div>
    </main>
</body>
</html>

<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/bootstrap.php';
require_once __DIR__ . '/includes/autenticacao.php';
require_once __DIR__ . '/models/UsuarioAdmin.php';
require_once __DIR__ . '/services/AutenticacaoAdminService.php';
require_once __DIR__ . '/controllers/AutenticacaoAdminController.php';

iniciar_sessao_segura();

if (admin_esta_autenticado() && admin_perfil_autorizado()) {
    header('Location: ' . admin_url());
    exit;
}

$controller = new AutenticacaoAdminController(
    new AutenticacaoAdminService(new UsuarioAdmin($pdo))
);
$erro = null;
$mensagem = match ((string) ($_GET['msg'] ?? '')) {
    'desconectado' => 'Sessão administrativa encerrada com segurança.',
    default => null,
};

$erroRota = (string) ($_GET['erro'] ?? '');
if ($erroRota === 'restrito') {
    $erro = 'Acesse sua conta administrativa para continuar.';
} elseif ($erroRota === 'expirado') {
    $erro = 'Sua sessão expirou após 30 minutos de inatividade.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erro = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
    } elseif ($controller->login($_POST)) {
        header('Location: ' . admin_url());
        exit;
    } else {
        $erro = 'E-mail ou senha inválidos, ou perfil sem acesso administrativo.';
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Login administrativo | Portal SI</title>
    <link rel="stylesheet" href="<?= e(admin_url('assets/css/admin.css')) ?>">
</head>
<body class="admin-foundation">
    <main class="foundation-card admin-login" aria-labelledby="titulo-login">
        <p class="foundation-card__eyebrow">Área administrativa</p>
        <h1 id="titulo-login">Acessar o painel</h1>
        <p>Entre com uma conta de perfil administrador ou editor.</p>

        <?php if ($mensagem !== null): ?>
            <p class="admin-message admin-message--success" role="status"><?= e($mensagem) ?></p>
        <?php endif; ?>

        <?php if ($erro !== null): ?>
            <p class="admin-message admin-message--error" role="alert"><?= e($erro) ?></p>
        <?php endif; ?>

        <form class="admin-form" method="post" action="<?= e(admin_url('login.php')) ?>">
            <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">

            <label for="email">E-mail</label>
            <input id="email" name="email" type="email" required autocomplete="username" value="<?= e((string) ($_POST['email'] ?? '')) ?>">

            <label for="senha">Senha</label>
            <input id="senha" name="senha" type="password" required autocomplete="current-password">

            <button type="submit">Entrar</button>
        </form>

        <a class="admin-secondary-link" href="../public/index.php">Voltar ao portal</a>
    </main>
</body>
</html>

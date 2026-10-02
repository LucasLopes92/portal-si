<?php

declare(strict_types=1);

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../../../config/session.php';
require_once __DIR__ . '/../../models/Usuario.php';
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../helpers/seguranca.php';

iniciar_sessao_segura();
$controller = new AuthController(new AuthService(new Usuario($pdo)));
$mensagem = null;
if (isset($_GET['msg']) && $_GET['msg'] === 'sucesso_cadastro') {
    $mensagem = 'Cadastro realizado. Faça login para continuar.';
}
if (isset($_GET['msg']) && $_GET['msg'] === 'desconectado') {
    $mensagem = 'Sessão encerrada com segurança.';
}
if (isset($_GET['erro']) && $_GET['erro'] === 'expirado') {
    $mensagem = 'Sua sessão expirou por inatividade.';
}

$erroLogin = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erroLogin = 'A sessão do formulário expirou. Tente novamente.';
    } elseif ($controller->login($_POST)) {
        header('Location: dashboard.php');
        exit;
    } else {
        $erroLogin = 'E-mail ou senha inválidos.';
    }
}
?><!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Portal SI</title>
    <link rel="stylesheet" href="../../../public/assets/css/style.css?v=<?= e(substr((string) hash_file('sha256', __DIR__ . '/../../../public/assets/css/style.css'), 0, 12)) ?>">
</head>
<body>
    <div class="site-topbar">Faculdades ESUCRI · Portal de Comunicação SI</div>
    <header class="site-header">
        <div class="site-header__inner">
            <a class="brand" href="../../../public/index.php" aria-label="Voltar à página inicial do Portal SI">
                <span class="brand__mark" aria-hidden="true">e</span>
                <span><span class="brand__name">ESUCRI</span><span class="brand__sub">PORTAL DE COMUNICAÇÃO SI</span></span>
            </a>
        </div>
    </header>
    <main class="page-shell">
        <section class="auth-card" aria-labelledby="titulo-login">
            <p class="auth-card__eyebrow">Área restrita</p>
            <h1 id="titulo-login">Acesso ao Portal SI</h1>
            <p class="auth-card__intro">Entre com suas credenciais acadêmicas para acessar o portal.</p>
            <?php if ($mensagem !== null): ?><p class="message message--success" role="status"><?= e($mensagem) ?></p><?php endif; ?>
            <?php if ($erroLogin !== null): ?><p class="message" role="alert"><?= e($erroLogin) ?></p><?php endif; ?>
            <form method="post" action="login.php">
                <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">
                <div class="form-group"><label for="email">E-mail</label><input id="email" name="email" type="email" required autocomplete="username"></div>
                <div class="form-group"><label for="senha">Senha</label><input id="senha" name="senha" type="password" required autocomplete="current-password"></div>
                <button type="submit">Entrar</button>
            </form>
            <p class="form-links"><a href="cadastro.php">Ainda não tenho uma conta</a></p>
            <p class="form-links form-links--secondary"><a href="../../../public/index.php">Voltar ao portal</a></p>
        </section>
    </main>
    <footer class="site-footer"><div class="site-footer__inner"><p>ESUCRI · Criciúma - SC</p><p>Portal de Comunicação SI</p></div></footer>
</body>
</html>

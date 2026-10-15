<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/seguranca.php';

function admin_base_url(): string
{
    return app_base_url() . '/administrador';
}

function admin_url(string $caminho = ''): string
{
    $caminho = ltrim($caminho, '/');

    return admin_base_url() . ($caminho === '' ? '/' : '/' . $caminho);
}

function admin_esta_autenticado(): bool
{
    iniciar_sessao_segura();

    return isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] > 0;
}

function admin_perfil_autorizado(): bool
{
    return (string) ($_SESSION['usuario_perfil'] ?? '') === 'admin';
}

function admin_encerrar_sessao_atual(): void
{
    iniciar_sessao_segura();
    $_SESSION = [];

    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires' => time() - 42000,
            'path' => $params['path'],
            'domain' => $params['domain'],
            'secure' => (bool) $params['secure'],
            'httponly' => (bool) $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Lax',
        ]);
    }

    session_destroy();
}

function admin_exigir_acesso(int $limiteInatividade = 1800): void
{
    iniciar_sessao_segura();

    if (!admin_esta_autenticado()) {
        header('Location: ' . app_base_url() . '/app/views/admin/login.php?erro=restrito_admin');
        exit;
    }

    $ultimoAcesso = (int) ($_SESSION['ultimo_acesso'] ?? time());
    if (time() - $ultimoAcesso > $limiteInatividade) {
        admin_encerrar_sessao_atual();
        header('Location: ' . app_base_url() . '/app/views/admin/login.php?erro=expirado');
        exit;
    }

    if (!admin_perfil_autorizado()) {
        http_response_code(403);
        require __DIR__ . '/../views/admin/painel/acesso_negado.php';
        exit;
    }

    $_SESSION['ultimo_acesso'] = time();
}

function admin_definir_flash(string $tipo, string $mensagem): void
{
    iniciar_sessao_segura();
    $_SESSION['admin_flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function admin_consumir_flash(): ?array
{
    iniciar_sessao_segura();
    $flash = $_SESSION['admin_flash'] ?? null;
    unset($_SESSION['admin_flash']);

    return is_array($flash) ? $flash : null;
}

function admin_renovar_csrf(): void
{
    iniciar_sessao_segura();
    unset($_SESSION['csrf_token']);
}

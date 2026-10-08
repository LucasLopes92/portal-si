<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';
require_once __DIR__ . '/../../app/helpers/seguranca.php';
require_once __DIR__ . '/conexao.php';

function admin_base_url(): string
{
    return app_base_url() . '/administrador';
}

function admin_url(string $caminho = ''): string
{
    $base = admin_base_url();
    $caminho = ltrim($caminho, '/');

    return $caminho === '' ? $base . '/' : $base . '/' . $caminho;
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

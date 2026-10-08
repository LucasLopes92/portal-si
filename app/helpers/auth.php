<?php

declare(strict_types=1);

require_once __DIR__ . '/../../config/session.php';

/**
 * Guardas de autenticação e autorização.
 *
 * Essas funções ficam fora das Views para que qualquer Controller ou página
 * protegida utilize exatamente a mesma regra de acesso.
 */
function is_logged_in(): bool
{
    iniciar_sessao_segura();

    return isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] > 0;
}

function require_login(): void
{
    if (!is_logged_in()) {
        header('Location: ' . app_base_url() . '/app/views/admin/login.php?erro=restrito');
        exit;
    }

    validar_tempo_inatividade();
}

function require_perfil(array $perfisAutorizados): void
{
    require_login();

    $perfilAtual = (string) ($_SESSION['usuario_perfil'] ?? '');

    if (!in_array($perfilAtual, $perfisAutorizados, true)) {
        http_response_code(403);
        require __DIR__ . '/../views/errors/403.php';
        exit;
    }
}

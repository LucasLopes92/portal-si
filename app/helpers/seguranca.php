<?php

declare(strict_types=1);

/**
 * Escape de saída para HTML.
 *
 * Dados vindos do banco ou de formulários devem passar por e() antes de serem
 * renderizados no HTML. A função evita que textos sejam interpretados como
 * tags ou scripts pelo navegador.
 */
function e(?string $valor): string
{
    return htmlspecialchars(
        (string) $valor,
        ENT_QUOTES | ENT_SUBSTITUTE,
        'UTF-8'
    );
}

/**
 * Token CSRF para formulários POST.
 *
 * O token fica vinculado à sessão e deve ser incluído em formulários e
 * validado antes de executar operações que alterem dados.
 */
function obter_token_csrf(): string
{
    require_once __DIR__ . '/../../config/session.php';
    iniciar_sessao_segura();

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function validar_token_csrf(?string $token): bool
{
    require_once __DIR__ . '/../../config/session.php';
    iniciar_sessao_segura();

    return is_string($token)
        && isset($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

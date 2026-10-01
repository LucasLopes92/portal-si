<?php

declare(strict_types=1);

/** Prefixo da instalação no servidor web. Pode ser alterado por ambiente. */
function app_base_url(): string
{
    return rtrim((string) (getenv('PORTAL_APP_BASE_URL') ?: '/portal-si'), '/');
}

/**
 * Inicialização centralizada da sessão.
 *
 * Todos os pontos que precisarem de sessão devem chamar
 * iniciar_sessao_segura(). A função é idempotente e não reinicia uma sessão
 * que já esteja ativa.
 */
function iniciar_sessao_segura(): void
{
    if (session_status() !== PHP_SESSION_NONE) {
        return;
    }

    // Os parâmetros reduzem o risco de roubo e envio indevido do cookie de
    // sessão. Em produção com HTTPS, secure deve ser alterado para true.
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => false,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

/**
 * Controle simples de inatividade.
 *
 * A cada requisição protegida, o horário do último acesso é conferido. Depois
 * de 30 minutos sem atividade, a sessão é encerrada e o usuário volta ao login.
 */
function validar_tempo_inatividade(int $limiteSegundos = 1800): void
{
    iniciar_sessao_segura();

    if (isset($_SESSION['ultimo_acesso'])) {
        $tempoInativo = time() - (int) $_SESSION['ultimo_acesso'];

        if ($tempoInativo > $limiteSegundos) {
            $_SESSION = [];
            session_destroy();
            header('Location: ' . app_base_url() . '/app/views/admin/login.php?erro=expirado');
            exit;
        }
    }

    $_SESSION['ultimo_acesso'] = time();
}

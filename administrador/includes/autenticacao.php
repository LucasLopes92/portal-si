<?php

declare(strict_types=1);

require_once __DIR__ . '/bootstrap.php';

function admin_esta_autenticado(): bool
{
    iniciar_sessao_segura();

    return isset($_SESSION['usuario_id']) && (int) $_SESSION['usuario_id'] > 0;
}

function admin_perfil_autorizado(): bool
{
    $perfil = (string) ($_SESSION['usuario_perfil'] ?? '');

    return in_array($perfil, ['admin', 'editor'], true);
}

function admin_exigir_acesso(int $limiteInatividade = 1800): void
{
    iniciar_sessao_segura();

    if (!admin_esta_autenticado()) {
        header('Location: ' . admin_url('login.php?erro=restrito'));
        exit;
    }

    $ultimoAcesso = (int) ($_SESSION['ultimo_acesso'] ?? time());
    if (time() - $ultimoAcesso > $limiteInatividade) {
        admin_encerrar_sessao_atual();
        header('Location: ' . admin_url('login.php?erro=expirado'));
        exit;
    }

    if (!admin_perfil_autorizado()) {
        http_response_code(403);
        require __DIR__ . '/../views/acesso_negado.php';
        exit;
    }

    $_SESSION['ultimo_acesso'] = time();
}

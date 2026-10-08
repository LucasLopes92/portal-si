<?php

declare(strict_types=1);

require_once __DIR__ . '/../includes/bootstrap.php';
require_once __DIR__ . '/../models/UsuarioAdmin.php';

final class AutenticacaoAdminService
{
    public function __construct(private UsuarioAdmin $usuarioModel)
    {
    }

    public function autenticar(string $email, string $senha): bool
    {
        $email = trim($email);
        if ($email === '' || $senha === '') {
            return false;
        }

        $usuario = $this->usuarioModel->buscarAtivoPorEmail($email);
        if (
            $usuario === null
            || !password_verify($senha, (string) $usuario['senha_hash'])
            || !in_array((string) $usuario['perfil'], ['admin', 'editor'], true)
        ) {
            return false;
        }

        iniciar_sessao_segura();
        session_regenerate_id(true);

        $_SESSION['usuario_id'] = (int) $usuario['id'];
        $_SESSION['usuario_nome'] = (string) $usuario['nome'];
        $_SESSION['usuario_email'] = (string) $usuario['email'];
        $_SESSION['usuario_perfil'] = (string) $usuario['perfil'];
        $_SESSION['ultimo_acesso'] = time();

        return true;
    }

    public function encerrar(): void
    {
        admin_encerrar_sessao_atual();
    }
}

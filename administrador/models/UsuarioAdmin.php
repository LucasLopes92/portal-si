<?php

declare(strict_types=1);

final class UsuarioAdmin
{
    public function __construct(private PDO $pdo)
    {
    }

    public function buscarAtivoPorEmail(string $email): ?array
    {
        $stmt = $this->pdo->prepare(
            "SELECT id, nome, email, senha_hash, perfil, status
             FROM usuarios
             WHERE email = :email AND status = 'ativo'
             LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }
}

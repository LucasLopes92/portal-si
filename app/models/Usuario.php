<?php

declare(strict_types=1);

/**
 * Model da tabela usuarios.
 *
 * Esta classe concentra apenas acesso a dados. As regras de cadastro,
 * autenticação e sessão ficam no AuthService.
 */
final class Usuario
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Busca uma conta ativa pelo e-mail usando consulta parametrizada. */
    public function buscarPorEmail(string $email): ?array
    {
        $sql = <<<'SQL'
            SELECT id, nome, email, senha_hash, perfil, status
            FROM usuarios
            WHERE email = :email AND status = 'ativo'
            LIMIT 1
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        return $usuario ?: null;
    }

    /** Insere um usuário já validado e já convertido em hash pelo Service. */
    public function criar(string $nome, string $email, string $senhaHash, string $perfil = 'aluno'): int
    {
        $sql = <<<'SQL'
            INSERT INTO usuarios (nome, email, senha_hash, perfil, status)
            VALUES (:nome, :email, :senha_hash, :perfil, 'ativo')
            RETURNING id
        SQL;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha_hash' => $senhaHash,
            'perfil' => $perfil,
        ]);

        return (int) $stmt->fetchColumn();
    }
}

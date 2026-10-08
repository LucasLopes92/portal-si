<?php

declare(strict_types=1);

final class ConteudoAdmin
{
    public function __construct(private PDO $pdo)
    {
    }

    public function contarTodos(): int
    {
        return (int) $this->pdo->query('SELECT COUNT(*) FROM conteudos')->fetchColumn();
    }

    /** @return array<int, array<string, mixed>> */
    public function listarPaginado(int $limite, int $offset): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT c.id, c.titulo, c.slug, c.status, c.destaque,
                    c.publicado_em, c.criado_em, c.atualizado_em,
                    cat.nome AS categoria_nome, u.nome AS autor_nome
             FROM conteudos c
             INNER JOIN categorias cat ON cat.id = c.categoria_id
             INNER JOIN usuarios u ON u.id = c.autor_id
             ORDER BY COALESCE(c.publicado_em, c.criado_em) DESC, c.id DESC
             LIMIT :limite OFFSET :offset'
        );
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

<?php

declare(strict_types=1);

final class Categoria
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listarAtivas(): array
    {
        $stmt = $this->db->prepare(
            'SELECT id, nome, slug, descricao, icone
             FROM categorias
             WHERE ativo = TRUE
             ORDER BY ordem ASC, id ASC'
        );
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

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
            'SELECT cat.id, cat.nome, cat.slug, cat.descricao, cat.icone,
                    COUNT(c.id) AS total_publicados
             FROM categorias cat
             LEFT JOIN conteudos c
               ON c.categoria_id = cat.id
              AND c.status = :status
              AND c.publicado_em IS NOT NULL
             WHERE cat.ativo = TRUE
             GROUP BY cat.id, cat.nome, cat.slug, cat.descricao, cat.icone, cat.ordem
             ORDER BY cat.ordem ASC, cat.id ASC'
        );
        $stmt->bindValue(':status', 'publicado');
        $stmt->execute();

        return $stmt->fetchAll();
    }
}

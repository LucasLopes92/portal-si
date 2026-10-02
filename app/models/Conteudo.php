<?php

declare(strict_types=1);

final class Conteudo
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listarPublicados(int $limite = 9, ?int $categoriaId = null): array
    {
        $sql = 'SELECT c.id, c.titulo, c.slug, c.resumo, c.corpo, c.imagem_capa,
                       c.publicado_em, cat.nome AS categoria_nome,
                       cat.slug AS categoria_slug, u.nome AS autor_nome
                FROM conteudos c
                INNER JOIN categorias cat ON cat.id = c.categoria_id
                INNER JOIN usuarios u ON u.id = c.autor_id
                WHERE c.status = :status
                  AND c.publicado_em IS NOT NULL
                  AND cat.ativo = TRUE';

        if ($categoriaId !== null) {
            $sql .= ' AND c.categoria_id = :categoria_id';
        }

        $sql .= ' ORDER BY c.publicado_em DESC, c.id DESC LIMIT :limite';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', 'publicado');
        if ($categoriaId !== null) {
            $stmt->bindValue(':categoria_id', $categoriaId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

}

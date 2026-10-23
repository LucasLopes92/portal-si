<?php

declare(strict_types=1);

final class Conteudo
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listarPublicados(int $limite = 9, ?int $categoriaId = null, int $offset = 0): array
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

        $sql .= ' ORDER BY c.publicado_em DESC, c.id DESC LIMIT :limite OFFSET :offset';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', 'publicado');
        if ($categoriaId !== null) {
            $stmt->bindValue(':categoria_id', $categoriaId, PDO::PARAM_INT);
        }
        $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
        $stmt->bindValue(':offset', max(0, $offset), PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contarPublicados(?int $categoriaId = null): int
    {
        $sql = 'SELECT COUNT(*)
                FROM conteudos c
                INNER JOIN categorias cat ON cat.id = c.categoria_id
                WHERE c.status = :status
                  AND c.publicado_em IS NOT NULL
                  AND cat.ativo = TRUE';
        if ($categoriaId !== null) {
            $sql .= ' AND c.categoria_id = :categoria_id';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', 'publicado');
        if ($categoriaId !== null) {
            $stmt->bindValue(':categoria_id', $categoriaId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /** Retorna somente conteúdo publicado e pertencente a uma categoria ativa. */
    public function buscarPublicadoPorId(int $id): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT c.id, c.titulo, c.resumo, c.corpo, c.imagem_capa,
                    c.link_youtube, c.publicado_em,
                    cat.nome AS categoria_nome, cat.slug AS categoria_slug,
                    u.nome AS autor_nome
             FROM conteudos c
             INNER JOIN categorias cat ON cat.id = c.categoria_id
             INNER JOIN usuarios u ON u.id = c.autor_id
             WHERE c.id = :id
               AND c.status = :status
               AND c.publicado_em IS NOT NULL
               AND cat.ativo = TRUE
             LIMIT 1'
        );
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->bindValue(':status', 'publicado');
        $stmt->execute();
        $conteudo = $stmt->fetch();

        return $conteudo ?: null;
    }

}

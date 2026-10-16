<?php

declare(strict_types=1);

final class Conteudo
{
    public function __construct(private PDO $db)
    {
    }

    /** @return array<int, array<string, mixed>> */
    public function listarPublicados(?int $limite = 9, ?int $categoriaId = null, int $offset = 0, array $filtros = []): array
    {
        $sql = 'SELECT c.id, c.titulo, c.slug, c.resumo, c.corpo, c.imagem_capa,
                       c.publicado_em, cat.nome AS categoria_nome,
                       cat.slug AS categoria_slug, u.nome AS autor_nome '
            . $this->origemPublicados($categoriaId, $filtros)
            . ' ORDER BY c.publicado_em DESC, c.id DESC';
        if ($limite !== null) {
            $sql .= ' LIMIT :limite OFFSET :offset';
        }
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':status', 'publicado');
        if ($categoriaId !== null) {
            $stmt->bindValue(':categoria_id', $categoriaId, PDO::PARAM_INT);
        }
        if ($limite !== null) {
            $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
            $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        }
        $this->vincularFiltros($stmt, $filtros);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    public function contarPublicados(?int $categoriaId = null, array $filtros = []): int
    {
        $stmt = $this->db->prepare('SELECT COUNT(*) ' . $this->origemPublicados($categoriaId, $filtros));
        $stmt->bindValue(':status', 'publicado');
        if ($categoriaId !== null) {
            $stmt->bindValue(':categoria_id', $categoriaId, PDO::PARAM_INT);
        }
        $this->vincularFiltros($stmt, $filtros);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    private function origemPublicados(?int $categoriaId, array $filtros = []): string
    {
        return 'FROM conteudos c
                INNER JOIN categorias cat ON cat.id = c.categoria_id
                INNER JOIN usuarios u ON u.id = c.autor_id
                WHERE c.status = :status
                  AND c.publicado_em IS NOT NULL
                  AND cat.ativo = TRUE
                  AND cat.slug IN (\'noticias\', \'eventos\')'
            . ($categoriaId === null ? '' : ' AND c.categoria_id = :categoria_id')
            . (($filtros['palavra'] ?? '') === '' ? '' : " AND concat_ws(' ', c.titulo, c.resumo, c.corpo) ILIKE :palavra ESCAPE '!'")
            . (empty($filtros['data']) ? '' : ' AND c.publicado_em >= :data_inicio AND c.publicado_em < :data_fim');
    }

    private function vincularFiltros(PDOStatement $stmt, array $filtros): void
    {
        if (($filtros['palavra'] ?? '') !== '') {
            $palavra = strtr($filtros['palavra'], ['!' => '!!', '%' => '!%', '_' => '!_']);
            $stmt->bindValue(':palavra', '%' . $palavra . '%');
        }
        if (!empty($filtros['data'])) {
            $inicio = new DateTimeImmutable($filtros['data'], new DateTimeZone('America/Sao_Paulo'));
            $stmt->bindValue(':data_inicio', $inicio->format(DateTimeInterface::ATOM));
            $stmt->bindValue(':data_fim', $inicio->modify('+1 day')->format(DateTimeInterface::ATOM));
        }
    }
}

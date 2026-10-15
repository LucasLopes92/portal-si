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

    /** @return array<int, array<string, mixed>> */
    public function listarCategoriasAtivas(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, nome
             FROM categorias
             WHERE ativo = TRUE
             ORDER BY ordem ASC, nome ASC'
        );

        return $stmt->fetchAll();
    }

    public function categoriaAtivaExiste(int $categoriaId): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT EXISTS (
                SELECT 1 FROM categorias WHERE id = :id AND ativo = TRUE
            )'
        );
        $stmt->execute(['id' => $categoriaId]);

        return (bool) $stmt->fetchColumn();
    }

    public function slugExiste(string $slug, ?int $ignorarId = null): bool
    {
        $sql = 'SELECT EXISTS (SELECT 1 FROM conteudos WHERE slug = :slug';
        if ($ignorarId !== null) {
            $sql .= ' AND id <> :ignorar_id';
        }
        $sql .= ')';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':slug', $slug);
        if ($ignorarId !== null) {
            $stmt->bindValue(':ignorar_id', $ignorarId, PDO::PARAM_INT);
        }
        $stmt->execute();

        return (bool) $stmt->fetchColumn();
    }

    public function buscarPorId(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, titulo, slug, resumo, corpo, link_youtube, categoria_id,
                    autor_id, status, destaque, publicado_em, imagem_capa
             FROM conteudos
             WHERE id = :id'
        );
        $stmt->execute(['id' => $id]);
        $conteudo = $stmt->fetch();

        return $conteudo ?: null;
    }

    public function criar(array $dados): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO conteudos (
                titulo, slug, resumo, corpo, link_youtube, categoria_id,
                autor_id, status, destaque, publicado_em
             ) VALUES (
                :titulo, :slug, :resumo, :corpo, :link_youtube, :categoria_id,
                :autor_id, :status, :destaque, :publicado_em
             )
             RETURNING id'
        );
        $stmt->bindValue(':titulo', $dados['titulo']);
        $stmt->bindValue(':slug', $dados['slug']);
        $stmt->bindValue(':resumo', $dados['resumo']);
        $stmt->bindValue(':corpo', $dados['corpo']);
        $stmt->bindValue(':link_youtube', $dados['link_youtube']);
        $stmt->bindValue(':categoria_id', $dados['categoria_id'], PDO::PARAM_INT);
        $stmt->bindValue(':autor_id', $dados['autor_id'], PDO::PARAM_INT);
        $stmt->bindValue(':status', $dados['status']);
        $stmt->bindValue(':destaque', $dados['destaque'], PDO::PARAM_BOOL);
        $stmt->bindValue(':publicado_em', $dados['publicado_em']);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    public function atualizar(int $id, array $dados): bool
    {
        $stmt = $this->pdo->prepare(
            'UPDATE conteudos
             SET titulo = :titulo,
                 slug = :slug,
                 resumo = :resumo,
                 corpo = :corpo,
                 link_youtube = :link_youtube,
                 categoria_id = :categoria_id,
                 status = :status,
                 destaque = :destaque,
                 publicado_em = :publicado_em
             WHERE id = :id'
        );
        $stmt->bindValue(':titulo', $dados['titulo']);
        $stmt->bindValue(':slug', $dados['slug']);
        $stmt->bindValue(':resumo', $dados['resumo']);
        $stmt->bindValue(':corpo', $dados['corpo']);
        $stmt->bindValue(':link_youtube', $dados['link_youtube']);
        $stmt->bindValue(':categoria_id', $dados['categoria_id'], PDO::PARAM_INT);
        $stmt->bindValue(':status', $dados['status']);
        $stmt->bindValue(':destaque', $dados['destaque'], PDO::PARAM_BOOL);
        $stmt->bindValue(':publicado_em', $dados['publicado_em']);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() === 1;
    }

    public function excluir(int $id): bool
    {
        $stmt = $this->pdo->prepare('DELETE FROM conteudos WHERE id = :id');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount() === 1;
    }
}

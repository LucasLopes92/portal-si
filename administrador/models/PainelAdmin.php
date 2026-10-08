<?php

declare(strict_types=1);

final class PainelAdmin
{
    public function __construct(private PDO $pdo)
    {
    }

    public function obterResumo(): array
    {
        $conteudos = $this->pdo->query(
            "SELECT COUNT(*) AS total,
                    COUNT(*) FILTER (WHERE status = 'publicado') AS publicados,
                    COUNT(*) FILTER (WHERE status = 'rascunho') AS rascunhos,
                    COUNT(*) FILTER (WHERE destaque = TRUE) AS destaques
             FROM conteudos"
        )->fetch();

        $categoriasAtivas = (int) $this->pdo->query(
            'SELECT COUNT(*) FROM categorias WHERE ativo = TRUE'
        )->fetchColumn();

        return [
            'total_conteudos' => (int) ($conteudos['total'] ?? 0),
            'publicados' => (int) ($conteudos['publicados'] ?? 0),
            'rascunhos' => (int) ($conteudos['rascunhos'] ?? 0),
            'destaques' => (int) ($conteudos['destaques'] ?? 0),
            'categorias_ativas' => $categoriasAtivas,
        ];
    }
}

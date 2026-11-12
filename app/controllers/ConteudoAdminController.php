<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ConteudoAdmin.php';
require_once __DIR__ . '/../services/ConteudoAdminService.php';

final class ConteudoAdminController
{
    public function __construct(
        private ConteudoAdmin $model,
        private ?ConteudoAdminService $service = null
    )
    {
    }

    public function listar(int $paginaSolicitada, int $porPagina = 10, ?string $categoria = null): array
    {
        $porPagina = max(1, min($porPagina, 50));
        $categoria = in_array($categoria, ['noticias', 'eventos'], true) ? $categoria : null;
        $total = $this->model->contarTodos($categoria);
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = max(1, min($paginaSolicitada, $totalPaginas));
        $offset = ($pagina - 1) * $porPagina;

        return [
            'itens' => $this->model->listarPaginado($porPagina, $offset, $categoria),
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'total' => $total,
            'total_paginas' => $totalPaginas,
            'categoria' => $categoria,
        ];
    }

    public function categorias(): array
    {
        return $this->model->listarCategoriasAtivas();
    }

    public function cadastrar(array $dados, int $autorId, array $arquivos = [], array $pendentes = []): array
    {
        if ($this->service === null) {
            $this->service = new ConteudoAdminService($this->model);
        }

        return $this->service->criar($dados, $autorId, $arquivos, $pendentes);
    }

    public function buscar(int $id): ?array
    {
        return $this->model->buscarPorId($id);
    }

    public function editar(int $id, array $dados, array $arquivos = [], array $pendentes = []): array
    {
        if ($this->service === null) {
            $this->service = new ConteudoAdminService($this->model);
        }

        return $this->service->atualizar($id, $dados, $arquivos, $pendentes);
    }

    public function excluir(int $id): array
    {
        if ($this->service === null) {
            $this->service = new ConteudoAdminService($this->model);
        }

        return $this->service->excluir($id);
    }
}

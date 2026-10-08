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

    public function listar(int $paginaSolicitada, int $porPagina = 10): array
    {
        $porPagina = max(1, min($porPagina, 50));
        $total = $this->model->contarTodos();
        $totalPaginas = max(1, (int) ceil($total / $porPagina));
        $pagina = max(1, min($paginaSolicitada, $totalPaginas));
        $offset = ($pagina - 1) * $porPagina;

        return [
            'itens' => $this->model->listarPaginado($porPagina, $offset),
            'pagina' => $pagina,
            'por_pagina' => $porPagina,
            'total' => $total,
            'total_paginas' => $totalPaginas,
        ];
    }

    public function categorias(): array
    {
        return $this->model->listarCategoriasAtivas();
    }

    public function cadastrar(array $dados, int $autorId): array
    {
        if ($this->service === null) {
            $this->service = new ConteudoAdminService($this->model);
        }

        return $this->service->criar($dados, $autorId);
    }

    public function buscar(int $id): ?array
    {
        return $this->model->buscarPorId($id);
    }

    public function editar(int $id, array $dados): array
    {
        if ($this->service === null) {
            $this->service = new ConteudoAdminService($this->model);
        }

        return $this->service->atualizar($id, $dados);
    }
}

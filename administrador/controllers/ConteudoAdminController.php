<?php

declare(strict_types=1);

require_once __DIR__ . '/../models/ConteudoAdmin.php';

final class ConteudoAdminController
{
    public function __construct(private ConteudoAdmin $model)
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
}

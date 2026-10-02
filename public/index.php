<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../app/helpers/seguranca.php';
require_once __DIR__ . '/../app/models/Categoria.php';
require_once __DIR__ . '/../app/models/Conteudo.php';

iniciar_sessao_segura();

$categorias_menu = (new Categoria($pdo))->listarAtivas();
$categoria_atual = null;
$slug_categoria = isset($_GET['categoria']) && is_string($_GET['categoria'])
    ? $_GET['categoria']
    : null;

if ($slug_categoria !== null) {
    foreach ($categorias_menu as $categoria) {
        if ($categoria['slug'] === $slug_categoria) {
            $categoria_atual = $categoria;
            break;
        }
    }

    if ($categoria_atual === null) {
        http_response_code(404);
    }
}

$conteudos_recentes = $categoria_atual !== null || $slug_categoria === null
    ? (new Conteudo($pdo))->listarPublicados(9, $categoria_atual === null ? null : (int) $categoria_atual['id'])
    : [];

$titulo_pagina = $slug_categoria !== null && $categoria_atual === null
    ? 'Categoria não encontrada'
    : ($categoria_atual === null ? 'Página inicial' : $categoria_atual['nome']);

require_once __DIR__ . '/../app/views/home/index.php';

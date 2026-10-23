<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/session.php';
require_once __DIR__ . '/../app/helpers/seguranca.php';
require_once __DIR__ . '/../app/helpers/publico.php';
require_once __DIR__ . '/../app/models/Categoria.php';
require_once __DIR__ . '/../app/models/Conteudo.php';

iniciar_sessao_segura();

$categorias_menu = (new Categoria($pdo))->listarAtivas();
$conteudoModel = new Conteudo($pdo);
$id_conteudo = filter_input(INPUT_GET, 'conteudo', FILTER_VALIDATE_INT);

if (array_key_exists('conteudo', $_GET)) {
    $conteudo_detalhe = is_int($id_conteudo) && $id_conteudo > 0
        ? $conteudoModel->buscarPublicadoPorId($id_conteudo)
        : null;
    if ($conteudo_detalhe === null) {
        http_response_code(404);
    }

    $categoria_atual = null;
    $slug_categoria = null;
    $conteudos_recentes = [];
    $titulo_pagina = $conteudo_detalhe['titulo'] ?? 'Conteúdo não encontrado';
    require_once __DIR__ . '/../app/views/home/detalhe.php';
    exit;
}

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

$pagina_solicitada = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
$pagina_solicitada = is_int($pagina_solicitada) && $pagina_solicitada > 0 ? $pagina_solicitada : 1;
$por_pagina = $categoria_atual === null ? 9 : 6;
$total_publicados = $categoria_atual === null
    ? 0
    : $conteudoModel->contarPublicados((int) $categoria_atual['id']);
$total_paginas = max(1, (int) ceil($total_publicados / $por_pagina));
$pagina_atual = min($pagina_solicitada, $total_paginas);
$offset = ($pagina_atual - 1) * $por_pagina;

$conteudos_recentes = $categoria_atual !== null || $slug_categoria === null
    ? $conteudoModel->listarPublicados(
        $por_pagina,
        $categoria_atual === null ? null : (int) $categoria_atual['id'],
        $categoria_atual === null ? 0 : $offset
    )
    : [];

$titulo_pagina = $slug_categoria !== null && $categoria_atual === null
    ? 'Categoria não encontrada'
    : ($categoria_atual === null ? 'Página inicial' : $categoria_atual['nome']);

require_once __DIR__ . '/../app/views/home/index.php';

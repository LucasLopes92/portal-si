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

$todas_publicacoes = ($_GET['publicacoes'] ?? null) === 'todas';
$palavra_busca = isset($_GET['busca']) && is_string($_GET['busca']) ? mb_substr(trim($_GET['busca']), 0, 200) : '';
$data_busca = isset($_GET['data']) && is_string($_GET['data']) ? trim($_GET['data']) : '';
$busca_ativa = $palavra_busca !== '' || $data_busca !== '';
$listagem_publica = $categoria_atual !== null || $todas_publicacoes || $busca_ativa;
$erro_busca = '';
if ($data_busca !== '') {
    $data_validada = preg_match('/^[0-9]{4}-[0-9]{2}-[0-9]{2}$/D', $data_busca)
        ? DateTimeImmutable::createFromFormat('!Y-m-d', $data_busca, new DateTimeZone('America/Sao_Paulo'))
        : false;
    if ($data_validada === false || $data_validada->format('Y-m-d') !== $data_busca) {
        $erro_busca = 'Informe uma data de publicação válida.';
    }
}
$filtros_busca = ['palavra' => $palavra_busca, 'data' => $data_busca];
$conteudos_recentes = [];
$total_publicacoes = 0;
if (($categoria_atual !== null || $slug_categoria === null) && $erro_busca === '') {
    $model_conteudo = new Conteudo($pdo);
    $categoria_id = $categoria_atual === null ? null : (int) $categoria_atual['id'];
    $conteudos_recentes = $model_conteudo->listarPublicados(null, $categoria_id, 0, $filtros_busca);
    $total_publicacoes = count($conteudos_recentes);
}

$contexto_busca = $categoria_atual !== null
    ? ['categoria' => $categoria_atual['slug']]
    : ($todas_publicacoes ? ['publicacoes' => 'todas'] : []);

$titulo_pagina = $slug_categoria !== null && $categoria_atual === null
    ? 'Categoria não encontrada'
    : ($categoria_atual['nome'] ?? ($todas_publicacoes ? 'Todas as publicações' : 'Página inicial'));

require_once __DIR__ . '/../app/views/home/index.php';

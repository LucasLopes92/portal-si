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
$model_conteudo = new Conteudo($pdo);
$url_home = app_base_url() . '/public/index.php';
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

$todas_publicacoes = isset($_GET['publicacoes'])
    && is_string($_GET['publicacoes'])
    && $_GET['publicacoes'] === 'todas';
$palavra_busca = isset($_GET['busca']) && is_string($_GET['busca'])
    ? mb_substr(trim($_GET['busca']), 0, 200)
    : '';
$data_busca = isset($_GET['data']) && is_string($_GET['data'])
    ? trim($_GET['data'])
    : '';
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
$pagina_solicitada = filter_input(INPUT_GET, 'pagina', FILTER_VALIDATE_INT);
$pagina_solicitada = is_int($pagina_solicitada) && $pagina_solicitada > 0 ? $pagina_solicitada : 1;
$por_pagina = 6;
$total_paginas = 1;
$pagina_atual = 1;
$categoria_id = $categoria_atual === null ? null : (int) $categoria_atual['id'];

if (($categoria_atual !== null || $slug_categoria === null) && $erro_busca === '') {
    if ($listagem_publica) {
        $total_publicacoes = $model_conteudo->contarPublicados($categoria_id, $filtros_busca);
        if ($categoria_atual !== null) {
            $total_paginas = max(1, (int) ceil($total_publicacoes / $por_pagina));
            $pagina_atual = min($pagina_solicitada, $total_paginas);
            $conteudos_recentes = $model_conteudo->listarPublicados(
                $por_pagina,
                $categoria_id,
                ($pagina_atual - 1) * $por_pagina,
                $filtros_busca
            );
        } else {
            $conteudos_recentes = $model_conteudo->listarPublicados(null, null, 0, $filtros_busca);
        }
    } else {
        $conteudos_recentes = $model_conteudo->listarPublicados(9);
    }
}

$contexto_busca = [];
if ($categoria_atual !== null) {
    $contexto_busca['categoria'] = $categoria_atual['slug'];
} elseif ($todas_publicacoes) {
    $contexto_busca['publicacoes'] = 'todas';
}
if ($palavra_busca !== '') {
    $contexto_busca['busca'] = $palavra_busca;
}
if ($data_busca !== '') {
    $contexto_busca['data'] = $data_busca;
}
$url_pagina_base = $url_home . ($contexto_busca === [] ? '?' : '?' . http_build_query($contexto_busca) . '&');

$titulo_pagina = $slug_categoria !== null && $categoria_atual === null
    ? 'Categoria não encontrada'
    : ($categoria_atual['nome'] ?? ($todas_publicacoes ? 'Todas as publicações' : 'Página inicial'));

if (array_key_exists('conteudo', $_GET)) {
    $id_conteudo = filter_input(INPUT_GET, 'conteudo', FILTER_VALIDATE_INT);
    $conteudo_detalhe = is_int($id_conteudo) && $id_conteudo > 0
        ? $model_conteudo->buscarPublicadoPorId($id_conteudo)
        : null;
    if ($conteudo_detalhe === null) {
        http_response_code(404);
    }

    $slug_categoria = null;
    $categoria_atual = null;
    $todas_publicacoes = false;
    $listagem_publica = false;
    $titulo_pagina = $conteudo_detalhe['titulo'] ?? 'Conteúdo não encontrado';
    require_once __DIR__ . '/../app/views/home/detalhe.php';
    exit;
}

require_once __DIR__ . '/../app/views/home/index.php';

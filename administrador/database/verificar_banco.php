<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../includes/conexao.php';

$tabelasObrigatorias = [
    'auditoria',
    'categorias',
    'conteudo_tags',
    'conteudos',
    'eventos',
    'midias',
    'tags',
    'usuarios',
];

$colunasObrigatorias = [
    'usuarios' => ['senha_hash', 'perfil', 'status', 'criado_em', 'atualizado_em'],
    'categorias' => ['slug', 'ordem', 'ativo', 'criado_em', 'atualizado_em'],
    'conteudos' => [
        'slug',
        'resumo',
        'corpo',
        'imagem_capa',
        'autor_id',
        'status',
        'destaque',
        'publicado_em',
        'criado_em',
        'atualizado_em',
    ],
];

$stmtTabelas = $pdo->query(
    "SELECT table_name
     FROM information_schema.tables
     WHERE table_schema = 'public'
     ORDER BY table_name"
);
$tabelasEncontradas = $stmtTabelas->fetchAll(PDO::FETCH_COLUMN);
$problemas = [];

foreach ($tabelasObrigatorias as $tabela) {
    if (!in_array($tabela, $tabelasEncontradas, true)) {
        $problemas[] = "Tabela ausente: {$tabela}";
    }
}

$stmtColunas = $pdo->prepare(
    "SELECT column_name
     FROM information_schema.columns
     WHERE table_schema = 'public' AND table_name = :tabela"
);

foreach ($colunasObrigatorias as $tabela => $colunas) {
    $stmtColunas->execute(['tabela' => $tabela]);
    $encontradas = $stmtColunas->fetchAll(PDO::FETCH_COLUMN);

    foreach ($colunas as $coluna) {
        if (!in_array($coluna, $encontradas, true)) {
            $problemas[] = "Coluna ausente: {$tabela}.{$coluna}";
        }
    }
}

$contagens = [];
foreach (['usuarios', 'categorias', 'conteudos'] as $tabela) {
    if (in_array($tabela, $tabelasEncontradas, true)) {
        $contagens[$tabela] = (int) $pdo->query("SELECT COUNT(*) FROM {$tabela}")->fetchColumn();
    }
}

echo json_encode(
    [
        'banco' => (string) $pdo->query('SELECT current_database()')->fetchColumn(),
        'valido' => $problemas === [],
        'problemas' => $problemas,
        'tabelas' => $tabelasEncontradas,
        'registros_preservados' => $contagens,
    ],
    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
), PHP_EOL;

exit($problemas === [] ? 0 : 1);

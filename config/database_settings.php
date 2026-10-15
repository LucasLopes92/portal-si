<?php

declare(strict_types=1);

/**
 * PORTAL_DATABASE_URL ou um arquivo privado permitem usar a mesma conexao no
 * Apache e no PHP CLI. O URI nunca deve ser versionado nem exibido ao visitante.
 */
$uri = getenv('PORTAL_DATABASE_URL');
$arquivoUri = __DIR__ . '/aiven-uri.local.txt';
if (($uri === false || trim($uri) === '') && is_file($arquivoUri)) {
    $uri = trim((string) file_get_contents($arquivoUri));
    if ($uri === '') {
        throw new RuntimeException('O arquivo privado de conexao esta vazio.');
    }
}

$partes = [];
$parametros = [];
if (is_string($uri) && trim($uri) !== '') {
    $partes = parse_url(trim($uri));
    if (
        $partes === false
        || !in_array($partes['scheme'] ?? '', ['postgres', 'postgresql'], true)
        || empty($partes['host'])
        || empty($partes['user'])
        || empty($partes['path'])
        || $partes['path'] === '/'
    ) {
        throw new RuntimeException('O Service URI PostgreSQL esta incompleto ou invalido.');
    }
    parse_str($partes['query'] ?? '', $parametros);
}

$valorAmbiente = static function (string $nome, string $padrao): string {
    $valor = getenv($nome);

    return $valor === false ? $padrao : $valor;
};

$configuracao = [
    'host' => $valorAmbiente('PORTAL_DB_HOST', (string) ($partes['host'] ?? '127.0.0.1')),
    'port' => $valorAmbiente('PORTAL_DB_PORT', (string) ($partes['port'] ?? '5432')),
    'dbname' => $valorAmbiente('PORTAL_DB_NAME', rawurldecode(ltrim($partes['path'] ?? '/portal_si', '/'))),
    'user' => $valorAmbiente('PORTAL_DB_USER', rawurldecode($partes['user'] ?? 'postgres')),
    'password' => $valorAmbiente('PORTAL_DB_PASSWORD', rawurldecode($partes['pass'] ?? 'postgres')),
    'sslmode' => $valorAmbiente('PORTAL_DB_SSLMODE', (string) ($parametros['sslmode'] ?? ($partes === [] ? 'prefer' : 'require'))),
    'sslrootcert' => $valorAmbiente('PORTAL_DB_SSLROOTCERT',
        $partes !== [] && is_file(__DIR__ . '/aiven-ca.local.pem')
            ? __DIR__ . '/aiven-ca.local.pem'
            : ''
    ),
];

if (!in_array($configuracao['sslmode'], ['disable', 'allow', 'prefer', 'require', 'verify-ca', 'verify-full'], true)) {
    throw new RuntimeException('PORTAL_DB_SSLMODE invalido.');
}

if (
    str_ends_with(strtolower($configuracao['host']), '.aivencloud.com')
    && !in_array($configuracao['sslmode'], ['require', 'verify-ca', 'verify-full'], true)
) {
    throw new RuntimeException('A conexao com o Aiven exige SSL.');
}

// O libpq interpreta barras invertidas como escapes no DSN do Windows.
if (DIRECTORY_SEPARATOR === '\\') {
    $configuracao['sslrootcert'] = str_replace('\\', '/', $configuracao['sslrootcert']);
}

foreach (['host', 'port', 'dbname', 'sslrootcert'] as $campo) {
    if (preg_match('/[;\r\n\x00]/', $configuracao[$campo])) {
        throw new RuntimeException('Parametro de conexao invalido: ' . $campo);
    }
}

return $configuracao;

<?php

declare(strict_types=1);

/**
 * Conexão centralizada com o PostgreSQL.
 *
 * Os valores podem ser sobrescritos por variáveis de ambiente. Isso permite
 * usar as mesmas classes em desenvolvimento, laboratório e produção sem
 * alterar os Models ou os Controllers.
 */

$dbConfig = require __DIR__ . '/database_settings.php';
$dsn = sprintf(
    'pgsql:host=%s;port=%s;dbname=%s;sslmode=%s;connect_timeout=10',
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['dbname'],
    $dbConfig['sslmode']
);
if ($dbConfig['sslrootcert'] !== '') {
    $dsn .= ';sslrootcert=' . $dbConfig['sslrootcert'];
}

/**
 * PDO é configurado para lançar exceções, retornar registros como arrays
 * associativos e desativar prepared statements emulados. Assim, os Models
 * trabalham com consultas parametrizadas reais do PostgreSQL.
 */
$pdoOptions = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];

try {
    $pdo = new PDO($dsn, $dbConfig['user'], $dbConfig['password'], $pdoOptions);
} catch (PDOException $exception) {
    // O detalhe técnico vai para o log; a resposta pública não expõe
    // credenciais, host ou informações internas do banco.
    error_log('Falha na conexão com o PostgreSQL: ' . $exception->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}

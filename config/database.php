<?php

declare(strict_types=1);

/**
 * Conexão centralizada com o PostgreSQL.
 *
 * Os valores podem ser sobrescritos por variáveis de ambiente. Isso permite
 * usar as mesmas classes em desenvolvimento, laboratório e produção sem
 * alterar os Models ou os Controllers.
 */

$host = getenv('PORTAL_DB_HOST') ?: '127.0.0.1';
$port = getenv('PORTAL_DB_PORT') ?: '5432';
$dbname = getenv('PORTAL_DB_NAME') ?: 'portal_si';
$username = getenv('PORTAL_DB_USER') ?: 'postgres';
$password = getenv('PORTAL_DB_PASSWORD');

// O valor padrão atende à instalação local definida no roteiro da disciplina.
// Em outro ambiente, configure PORTAL_DB_PASSWORD antes de iniciar o PHP.
if ($password === false) {
    $password = 'postgres';
}

$dsn = sprintf('pgsql:host=%s;port=%s;dbname=%s', $host, $port, $dbname);

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
    $pdo = new PDO($dsn, $username, $password, $pdoOptions);
} catch (PDOException $exception) {
    // O detalhe técnico vai para o log; a resposta pública não expõe
    // credenciais, host ou informações internas do banco.
    error_log('Falha na conexão com o PostgreSQL: ' . $exception->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}

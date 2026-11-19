<?php

declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

if (!in_array('--confirmar-banco-vazio', $argv, true)) {
    fwrite(STDERR, "Uso: php database/instalar_avaliacao.php --confirmar-banco-vazio\nConfigure antes um banco novo conforme o README.\n");
    exit(1);
}

require __DIR__ . '/../config/database.php';

try {
    $pdo->beginTransaction();
    // Serializa instalacoes simultaneas no mesmo banco.
    $pdo->query('SELECT pg_advisory_xact_lock(731902601)');
    $quantidade = (int) $pdo->query(
        "SELECT count(*) FROM information_schema.tables
         WHERE table_schema = 'public' AND table_type = 'BASE TABLE'"
    )->fetchColumn();
    if ($quantidade !== 0) {
        throw new RuntimeException('O banco ja possui tabelas. Use um banco novo e vazio. Nenhum dado foi alterado.');
    }

    foreach (['schema.sql', 'seed.sql'] as $arquivo) {
        $sql = file_get_contents(__DIR__ . '/' . $arquivo);
        if ($sql === false) {
            throw new RuntimeException('Nao foi possivel ler ' . $arquivo);
        }
        // Schema e seed passam a fazer parte de uma unica transacao.
        $sql = preg_replace('/^\s*(BEGIN|COMMIT);\s*$/mi', '', $sql);
        $pdo->exec($sql);
    }

    // Substitui o administrador do seed apenas nesta instalacao nova.
    $senha = bin2hex(random_bytes(12));
    $stmt = $pdo->prepare(
        "UPDATE usuarios SET nome = 'Avaliador Portal SI', email = :email,
         senha_hash = :hash WHERE email = 'admin@esucri.com.br'"
    );
    $stmt->execute([
        'email' => 'avaliador@portal-si.test',
        'hash' => password_hash($senha, PASSWORD_DEFAULT),
    ]);
    if ($stmt->rowCount() !== 1) {
        throw new RuntimeException('Nao foi possivel criar o administrador de avaliacao.');
    }

    $pdo->commit();
    echo "Instalacao concluida com categorias e configuracao da Home.\n";
    echo "Login do portal: avaliador@portal-si.test\nSenha: {$senha}\n";
    echo "Guarde a senha acima. Abra http://localhost/portal-si/\n";
} catch (Throwable $erro) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    // Nao imprime excecoes PDO, que podem conter detalhes da conexao.
    fwrite(STDERR, $erro instanceof PDOException
        ? "Falha ao instalar. Confira a conexao e as permissoes de criacao de tabelas e da extensao citext.\n"
        : $erro->getMessage() . "\n");
    exit(1);
}

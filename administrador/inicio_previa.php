<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers/admin.php';
require_once __DIR__ . '/../app/models/ConfiguracaoInicio.php';
admin_exigir_acesso();
header('Cache-Control: no-store');
header('X-Frame-Options: SAMEORIGIN');
$config_inicio = (new ConfiguracaoInicio($pdo))->obter();
$aviso_previa = null;

// "Atualizar prévia" envia o formulário do editor para este iframe. Nada é salvo aqui.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $aviso_previa = 'A imagem excede o limite do servidor. Use uma imagem de até 2 MB.';
    } elseif (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $aviso_previa = 'A sessão do formulário expirou. Atualize a página.';
    } else {
        foreach (['etiqueta' => 100, 'titulo' => 140, 'descricao' => 300, 'botao_texto' => 50, 'imagem_alt' => 180] as $campo => $limite) {
            if (is_string($_POST[$campo] ?? null)) {
                $config_inicio[$campo] = mb_substr(trim($_POST[$campo]), 0, $limite);
            }
        }
        if (in_array($_POST['botao_destino'] ?? null, ['publicacoes', 'noticias', 'eventos'], true)) {
            $config_inicio['botao_destino'] = $_POST['botao_destino'];
        }
        $posicao = filter_var($_POST['imagem_posicao'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0, 'max_range' => 100]]);
        if (is_int($posicao)) {
            $config_inicio['imagem_posicao'] = $posicao;
        }

        // A nova foto não é gravada: vai direto para a prévia como data URI.
        $arquivo = $_FILES['imagem'] ?? null;
        if (is_array($arquivo) && is_string($arquivo['name'] ?? null) && ($arquivo['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_NO_FILE) {
            $tmp = (string) ($arquivo['tmp_name'] ?? '');
            $tipos = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            $mime = $arquivo['error'] === UPLOAD_ERR_OK && is_uploaded_file($tmp) ? (new finfo(FILEINFO_MIME_TYPE))->file($tmp) : '';
            $info = $mime !== '' ? @getimagesize($tmp) : false;
            if (!in_array($mime, $tipos, true) || $info === false || ($info['mime'] ?? '') !== $mime || filesize($tmp) > 2 * 1024 * 1024) {
                $aviso_previa = 'Selecione uma imagem JPG, PNG, WebP ou GIF de até 2 MB.';
            } else {
                $foto_inicio = 'data:' . $mime . ';base64,' . base64_encode((string) file_get_contents($tmp));
            }
        }
    }
}
$versaoInicio = substr(hash_file('sha256', __DIR__ . '/../public/assets/css/home-cover.css'), 0, 12);
?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Prévia da abertura</title>
<link rel="stylesheet" href="<?= e(app_base_url()) ?>/public/assets/css/home-cover.css?v=<?= e($versaoInicio) ?>">
<style>html,body{margin:0;padding:0;background:#19212b}body{overflow-x:hidden}.home-cover{min-height:100vh}.home-cover a{pointer-events:none}.previa-aviso{margin:0;padding:10px 16px;background:#fdecee;color:#8f1424;font:700 13px/1.5 Arial,sans-serif}</style>
</head><body><?php if ($aviso_previa !== null): ?><p class="previa-aviso" role="alert"><?= e($aviso_previa) ?></p><?php endif; ?><?php require __DIR__ . '/../app/views/home/abertura.php'; ?></body></html>

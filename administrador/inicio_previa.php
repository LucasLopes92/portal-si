<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers/admin.php';
require_once __DIR__ . '/../app/models/ConfiguracaoInicio.php';
admin_exigir_acesso();
header('Cache-Control: no-store');
header('X-Frame-Options: SAMEORIGIN');
$config_inicio = (new ConfiguracaoInicio($pdo))->obter();
$versaoInicio = substr(hash_file('sha256', __DIR__ . '/../public/assets/css/home-cover.css'), 0, 12);
?><!DOCTYPE html>
<html lang="pt-BR"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Prévia da abertura</title>
<link rel="stylesheet" href="<?= e(app_base_url()) ?>/public/assets/css/home-cover.css?v=<?= e($versaoInicio) ?>">
<style>html,body{margin:0;padding:0;background:#fff}body{overflow-x:hidden}</style>
</head><body><?php require __DIR__ . '/../app/views/home/abertura.php'; ?></body></html>

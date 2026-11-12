<?php
declare(strict_types=1);
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
require __DIR__ . '/../config/database.php';
require __DIR__ . '/../app/services/ConfiguracaoInicioService.php';
function inicio_garantir(bool $ok, string $mensagem): void { if (!$ok) { throw new RuntimeException($mensagem); } }
$model = new ConfiguracaoInicio($pdo);
$original = $model->obter();
$pdo->beginTransaction();
try {
    $autor = (int) $pdo->query("SELECT id FROM usuarios WHERE perfil='admin' ORDER BY id LIMIT 1")->fetchColumn();
    $service = new ConfiguracaoInicioService($model);
    $entrada = array_merge($original, ['titulo'=>'Título temporário da abertura', 'imagem_posicao'=>'0', 'botao_destino'=>'eventos', 'imagem'=>'public/uploads/arquivo-forjado.jpg']);
    inicio_garantir($service->salvar($entrada, [], $autor)['sucesso'], 'Configuração válida não foi salva.');
    $salvo = $model->obter();
    inicio_garantir($salvo['titulo']===$entrada['titulo'] && (int)$salvo['imagem_posicao']===0 && $salvo['botao_destino']==='eventos', 'Os valores não foram persistidos.');
    inicio_garantir($salvo['imagem']===$original['imagem'], 'O formulário conseguiu forjar a referência da imagem.');
    foreach ([['titulo'=>str_repeat('a',141)], ['titulo'=>''], ['imagem_posicao'=>'101'], ['botao_destino'=>'javascript:alert(1)']] as $invalido) {
        inicio_garantir(!$service->salvar(array_merge($entrada,$invalido), [], $autor)['sucesso'], 'Um campo inválido foi aceito.');
        inicio_garantir($model->obter()===$salvo, 'Uma entrada inválida alterou a configuração.');
    }
    inicio_garantir(!$service->salvar($entrada, ['name'=>['arquivo-inválido']], $autor)['sucesso'], 'Envio malformado não foi rejeitado.');
    inicio_garantir($model->obter()===$salvo, 'Envio malformado alterou a configuração.');
    echo "[OK] Persistência, imagem preservada, limites, campos obrigatórios, destino seguro, enquadramento e envio inválido.\n";
} finally { $pdo->rollBack(); }
inicio_garantir($model->obter()===$original, 'A configuração inicial não foi preservada.');

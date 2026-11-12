<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/helpers/admin.php';
require_once __DIR__ . '/../app/controllers/ConfiguracaoInicioController.php';
admin_exigir_acesso();

$controller = new ConfiguracaoInicioController(new ConfiguracaoInicio($pdo));
$dados = $controller->obter();
$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $erros['geral'] = 'O envio excedeu o limite do servidor. Selecione uma imagem de até 2 MB.';
    } elseif (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erros['geral'] = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
    } else {
        $resultado = $controller->salvar($_POST, $_FILES['imagem'] ?? [], (int) $_SESSION['usuario_id']);
        $dados = $resultado['dados'];
        $erros = $resultado['erros'];
        if ($resultado['sucesso']) {
            admin_renovar_csrf();
            admin_definir_flash('sucesso', 'Página inicial atualizada. As alterações já estão no portal.');
            header('Location: ' . admin_url('inicio.php'));
            exit;
        }
    }
}
$flashInicio = admin_consumir_flash();
$tituloPagina = 'Página inicial';
$paginaAtual = 'inicio';
require __DIR__ . '/../app/views/admin/painel/layout/header.php';
require __DIR__ . '/../app/views/admin/painel/inicio.php';
require __DIR__ . '/../app/views/admin/painel/layout/footer.php';

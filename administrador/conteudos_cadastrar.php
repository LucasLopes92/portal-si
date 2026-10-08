<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/autenticacao.php';
require_once __DIR__ . '/models/ConteudoAdmin.php';
require_once __DIR__ . '/services/ConteudoAdminService.php';
require_once __DIR__ . '/controllers/ConteudoAdminController.php';

admin_exigir_acesso();

$model = new ConteudoAdmin($pdo);
$controller = new ConteudoAdminController($model, new ConteudoAdminService($model));
$categorias = $controller->categorias();
$erros = [];
$dados = [
    'titulo' => '',
    'categoria_id' => 0,
    'resumo' => '',
    'corpo' => '',
    'link_youtube' => '',
    'status' => 'rascunho',
    'destaque' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erros['geral'] = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
        $dados = array_merge($dados, $_POST);
    } else {
        $resultado = $controller->cadastrar($_POST, (int) $_SESSION['usuario_id']);
        $erros = $resultado['erros'];
        $dados = array_merge($dados, $resultado['dados']);

        if ($resultado['sucesso']) {
            admin_renovar_csrf();
            admin_definir_flash('sucesso', 'Conteúdo cadastrado com sucesso.');
            header('Location: ' . admin_url('conteudos_listar.php'));
            exit;
        }
    }
}

$tituloPagina = 'Novo conteúdo';
$paginaAtual = 'conteudos';
$tituloFormulario = 'Publicar novo conteúdo';
$descricaoFormulario = 'Preencha as informações editoriais. A imagem de capa será adicionada em uma etapa posterior.';
$acaoFormulario = admin_url('conteudos_cadastrar.php');
$rotuloBotao = 'Salvar conteúdo';
$statusDisponiveis = ['rascunho' => 'Rascunho', 'publicado' => 'Publicado'];

require __DIR__ . '/includes/header.php';
require __DIR__ . '/views/conteudos/formulario.php';
require __DIR__ . '/includes/footer.php';

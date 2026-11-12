<?php

declare(strict_types=1);

require_once __DIR__ . '/../app/helpers/admin.php';
require_once __DIR__ . '/../app/models/ConteudoAdmin.php';
require_once __DIR__ . '/../app/services/ConteudoAdminService.php';
require_once __DIR__ . '/../app/controllers/ConteudoAdminController.php';
require_once __DIR__ . '/../app/services/ImagensPendentesService.php';

admin_exigir_acesso();

$model = new ConteudoAdmin($pdo);
$controller = new ConteudoAdminController($model, new ConteudoAdminService($model));
$categorias = $controller->categorias();
$erros = [];
$avisoImagens = null;
$imagensPendentes = new ImagensPendentesService();
$loteImagens = $imagensPendentes->lote($_POST['lote_imagens'] ?? null);
$dados = [
    'titulo' => '',
    'categoria_id' => 0,
    'resumo' => '',
    'corpo' => '',
    'imagem_capa' => '',
    'link_youtube' => '',
    'status' => 'rascunho',
    'destaque' => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if ($_POST === [] && (int) ($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $erros['imagens'] = 'O envio excedeu o limite do servidor. Envie até 6 MB de imagens por vez.';
    } elseif (!validar_token_csrf($_POST['csrf_token'] ?? null)) {
        $erros['geral'] = 'A sessão do formulário expirou. Atualize a página e tente novamente.';
        $dados = array_merge($dados, $_POST);
    } else {
        // As imagens vão primeiro para a pasta temporária, para aparecerem na prévia do formulário.
        $erroImagens = $imagensPendentes->atualizar($loteImagens, $_POST['remover_pendentes'] ?? null, $_FILES['imagens'] ?? [], 0);

        if (($_POST['acao'] ?? '') === 'enviar_imagens' || $erroImagens !== null) {
            $dados = array_merge($dados, array_intersect_key($_POST, $dados));
            if ($erroImagens !== null) {
                $erros['imagens'] = $erroImagens;
            } else {
                $avisoImagens = 'Imagens atualizadas. Confira a prévia abaixo e salve o conteúdo para publicá-las.';
            }
        } else {
            $resultado = $controller->cadastrar($_POST, (int) $_SESSION['usuario_id'], [], $imagensPendentes->listar($loteImagens));
            $erros = $resultado['erros'];
            $dados = array_merge($dados, $resultado['dados']);
        }

        if (isset($resultado) && $resultado['sucesso']) {
            $imagensPendentes->limpar($loteImagens);
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
$descricaoFormulario = 'Preencha as informações editoriais da publicação.';
$acaoFormulario = admin_url('conteudos_cadastrar.php');
$rotuloBotao = 'Salvar conteúdo';
$statusDisponiveis = ['rascunho' => 'Rascunho', 'publicado' => 'Publicado'];

require __DIR__ . '/../app/views/admin/painel/layout/header.php';
require __DIR__ . '/../app/views/admin/painel/conteudos/formulario.php';
require __DIR__ . '/../app/views/admin/painel/layout/footer.php';

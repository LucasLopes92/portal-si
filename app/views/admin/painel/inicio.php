<section class="admin-page-heading">
    <div><p class="admin-page-heading__eyebrow">Aparência do portal</p><h1>Uma abertura com a cara do curso</h1><p>Edite os textos e a foto. Acompanhe o resultado antes de publicar.</p></div>
</section>
<?php if ($flashInicio): ?><div class="admin-alert admin-alert--sucesso" role="status"><?= e($flashInicio['mensagem']) ?></div><?php endif; ?>
<?php if ($erros): ?><div class="admin-alert admin-alert--error" role="alert"><strong>Revise as alterações:</strong><ul><?php foreach ($erros as $erro): ?><li><?= e($erro) ?></li><?php endforeach; ?></ul><p>Se havia uma nova imagem selecionada, selecione-a novamente.</p></div><?php endif; ?>
<div class="inicio-editor">
    <form class="inicio-editor__form" id="inicio-form" method="post" enctype="multipart/form-data" action="<?= e(admin_url('inicio.php')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">
        <div class="admin-form-field"><label for="inicio-etiqueta">Chamada acima do título</label><input id="inicio-etiqueta" name="etiqueta" maxlength="100" value="<?= e($dados['etiqueta']) ?>" data-preview-text="etiqueta"></div>
        <div class="admin-form-field"><label for="inicio-titulo">Título principal *</label><textarea id="inicio-titulo" name="titulo" maxlength="140" rows="3" required data-preview-text="titulo"><?= e($dados['titulo']) ?></textarea></div>
        <div class="admin-form-field"><label for="inicio-descricao">Texto de apresentação</label><textarea id="inicio-descricao" name="descricao" maxlength="300" rows="4" data-preview-text="descricao"><?= e($dados['descricao']) ?></textarea></div>
        <div class="inicio-editor__divider"></div>
        <div class="admin-form-field"><label for="inicio-imagem">Imagem de fundo</label><input id="inicio-imagem" name="imagem" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><small>Escolha uma foto do computador, de até 2 MB. Prefira uma imagem horizontal com pelo menos 1400 pixels de largura. Sem novo arquivo, a foto atual é mantida.</small><button type="button" class="inicio-editor__discard" id="inicio-descartar-imagem" hidden>Desfazer seleção da imagem</button></div>
        <div class="admin-form-field"><label for="inicio-alt">Descrição da imagem *</label><input id="inicio-alt" name="imagem_alt" maxlength="180" required value="<?= e($dados['imagem_alt']) ?>"><small>Descreva a foto para quem usa leitor de tela.</small></div>
        <div class="admin-form-field"><label for="inicio-posicao">Enquadramento vertical <output id="inicio-posicao-valor"><?= (int) $dados['imagem_posicao'] ?>%</output></label><input id="inicio-posicao" name="imagem_posicao" type="range" min="0" max="100" value="<?= (int) $dados['imagem_posicao'] ?>"><small>Mova para ajustar a parte da foto que aparece na abertura.</small></div>
        <div class="inicio-editor__divider"></div>
        <div class="admin-form-field"><label for="inicio-botao">Texto do botão *</label><input id="inicio-botao" name="botao_texto" maxlength="50" required value="<?= e($dados['botao_texto']) ?>" data-preview-text="botao_texto"></div>
        <div class="admin-form-field"><label for="inicio-destino">O botão leva para</label><select id="inicio-destino" name="botao_destino"><?php foreach (['publicacoes'=>'Publicações na página inicial', 'noticias'=>'Notícias', 'eventos'=>'Eventos'] as $valor=>$nome): ?><option value="<?= e($valor) ?>"<?= $dados['botao_destino']===$valor?' selected':'' ?>><?= e($nome) ?></option><?php endforeach; ?></select></div>
        <p class="inicio-editor__note" id="inicio-estado" role="status" aria-live="polite">A prévia acompanha suas alterações. O portal muda ao salvar.</p>
        <button class="admin-button admin-button--primary" type="submit">Salvar e publicar</button>
    </form>
    <section class="inicio-preview" aria-labelledby="titulo-previa">
        <div class="inicio-preview__toolbar"><div><span class="inicio-preview__live" aria-hidden="true"></span><h2 id="titulo-previa">Prévia ao vivo</h2></div><div class="inicio-preview__devices" role="group" aria-label="Tamanho da prévia"><button type="button" data-preview-width="1440" aria-pressed="true">Computador</button><button type="button" data-preview-width="390" aria-pressed="false">Celular</button></div></div>
        <div class="inicio-preview__canvas" id="inicio-preview-canvas"><div class="inicio-preview__frame-wrap" id="inicio-frame-wrap"><iframe id="inicio-preview" title="Prévia da abertura da página inicial" src="<?= e(admin_url('inicio_previa.php')) ?>" data-base="<?= e(app_base_url()) ?>" data-original-image="<?= e(app_base_url().'/'.ltrim($dados['imagem'],'/')) ?>"></iframe></div></div>
        <p class="inicio-preview__caption">A mesma abertura do portal, com os textos e a imagem que você está editando.</p>
        <noscript><p>A prévia ao vivo precisa de JavaScript. Você pode salvar o formulário e conferir o resultado no portal.</p></noscript>
    </section>
</div>
<script src="<?= e(app_base_url()) ?>/public/assets/js/inicio-editor.js?v=<?= e(substr(hash_file('sha256', __DIR__.'/../../../../public/assets/js/inicio-editor.js'),0,12)) ?>" defer></script>

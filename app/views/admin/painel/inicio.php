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
        <div class="admin-form-field"><label for="inicio-imagem">Imagem de fundo</label><input id="inicio-imagem" name="imagem" type="file" accept="image/jpeg,image/png,image/webp,image/gif"><small>Escolha uma foto do computador, de até 2 MB. Prefira uma imagem horizontal com pelo menos 1400 pixels de largura. Sem novo arquivo, a foto atual é mantida.</small></div>
        <div class="admin-form-field"><label for="inicio-alt">Descrição da imagem *</label><input id="inicio-alt" name="imagem_alt" maxlength="180" required value="<?= e($dados['imagem_alt']) ?>"><small>Descreva a foto para quem usa leitor de tela.</small></div>
        <div class="admin-form-field"><label for="inicio-posicao">Enquadramento vertical</label><input id="inicio-posicao" name="imagem_posicao" type="range" min="0" max="100" value="<?= (int) $dados['imagem_posicao'] ?>"><small>Mova para ajustar a parte da foto que aparece na abertura: à esquerda mostra o topo, à direita mostra a base.</small></div>
        <div class="inicio-editor__divider"></div>
        <div class="admin-form-field"><label for="inicio-botao">Texto do botão *</label><input id="inicio-botao" name="botao_texto" maxlength="50" required value="<?= e($dados['botao_texto']) ?>" data-preview-text="botao_texto"></div>
        <div class="admin-form-field"><label for="inicio-destino">O botão leva para</label><select id="inicio-destino" name="botao_destino"><?php foreach (['publicacoes'=>'Publicações na página inicial', 'noticias'=>'Notícias', 'eventos'=>'Eventos'] as $valor=>$nome): ?><option value="<?= e($valor) ?>"<?= $dados['botao_destino']===$valor?' selected':'' ?>><?= e($nome) ?></option><?php endforeach; ?></select></div>
        <p class="inicio-editor__note">Use “Atualizar prévia” para ver suas alterações ao lado, inclusive a nova foto. O portal só muda ao salvar.</p>
        <div class="inicio-editor__actions">
            <button class="admin-button admin-button--primary" type="submit">Salvar e publicar</button>
            <button class="admin-button admin-button--secondary" type="submit" formaction="<?= e(admin_url('inicio_previa.php')) ?>" formtarget="inicio-preview" formnovalidate>Atualizar prévia</button>
        </div>
        <button type="reset" class="inicio-editor__discard">Desfazer alterações não salvas</button>
    </form>
    <section class="inicio-preview" aria-labelledby="titulo-previa">
        <div class="inicio-preview__toolbar"><div><span class="inicio-preview__live" aria-hidden="true"></span><h2 id="titulo-previa">Prévia</h2></div><fieldset class="inicio-preview__devices"><legend>Tamanho da prévia</legend><input type="radio" name="previa-tamanho" id="previa-computador" checked><label for="previa-computador">Computador</label><input type="radio" name="previa-tamanho" id="previa-celular"><label for="previa-celular">Celular</label></fieldset></div>
        <div class="inicio-preview__canvas" id="inicio-preview-canvas"><div class="inicio-preview__frame-wrap" id="inicio-frame-wrap"><iframe id="inicio-preview" name="inicio-preview" title="Prévia da abertura da página inicial" src="<?= e(admin_url('inicio_previa.php')) ?>"></iframe></div></div>
        <p class="inicio-preview__caption">A mesma abertura do portal. Ela mostra o que está salvo até você clicar em “Atualizar prévia”.</p>
    </section>
</div>

<section class="admin-page-heading" aria-labelledby="titulo-formulario">
    <div>
        <p class="admin-page-heading__eyebrow">Gestão editorial</p>
        <h1 id="titulo-formulario"><?= e($tituloFormulario) ?></h1>
        <p><?= e($descricaoFormulario) ?></p>
    </div>
    <a class="admin-button admin-button--secondary" href="<?= e(admin_url('conteudos_listar.php')) ?>">Voltar à listagem</a>
</section>

<?php if ($erros !== []): ?>
    <div class="admin-alert admin-alert--error" role="alert">
        <strong>Revise os campos informados:</strong>
        <ul>
            <?php foreach ($erros as $erro): ?><li><?= e((string) $erro) ?></li><?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form class="admin-content-form" method="post" enctype="multipart/form-data" action="<?= e($acaoFormulario) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">
    <input type="hidden" name="lote_imagens" value="<?= e($loteImagens) ?>">

    <div class="admin-form-field admin-form-field--wide">
        <label for="titulo">Título *</label>
        <input id="titulo" name="titulo" type="text" maxlength="200" required value="<?= e((string) ($dados['titulo'] ?? '')) ?>">
    </div>

    <div class="admin-form-field">
        <label for="categoria_id">Categoria *</label>
        <select id="categoria_id" name="categoria_id" required>
            <option value="">Selecione uma categoria</option>
            <?php foreach ($categorias as $categoria): ?>
                <option value="<?= (int) $categoria['id'] ?>"<?= (int) ($dados['categoria_id'] ?? 0) === (int) $categoria['id'] ? ' selected' : '' ?>><?= e((string) $categoria['nome']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="admin-form-field">
        <label for="status">Status *</label>
        <select id="status" name="status" required>
            <?php foreach ($statusDisponiveis as $valor => $rotulo): ?>
                <option value="<?= e($valor) ?>"<?= (string) ($dados['status'] ?? 'rascunho') === $valor ? ' selected' : '' ?>><?= e($rotulo) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="admin-form-field admin-form-field--wide">
        <label for="resumo">Resumo</label>
        <textarea id="resumo" name="resumo" maxlength="500" rows="3"><?= e((string) ($dados['resumo'] ?? '')) ?></textarea>
        <small>Até 500 caracteres. Se ficar vazio, a Home usará o início do texto.</small>
    </div>

    <div class="admin-form-field admin-form-field--wide">
        <label for="corpo">Texto completo *</label>
        <textarea id="corpo" name="corpo" rows="12" required><?= e((string) ($dados['corpo'] ?? '')) ?></textarea>
    </div>

    <div class="admin-form-field admin-form-field--wide">
        <label for="imagens">Imagens da publicação</label>
        <small id="imagens-ajuda">Selecione uma ou mais imagens do computador. Até 10 imagens por publicação, com até 2 MB cada e 6 MB por envio. JPG, PNG, WebP ou GIF. A primeira imagem será a capa.</small>
        <?php $imagensExistentes = isset($conteudo) ? imagens_publicacao($conteudo) : []; ?>
        <?php if ($imagensExistentes !== []): ?>
            <div class="admin-image-grid">
                <?php foreach ($imagensExistentes as $indice => $caminho): ?>
                    <label class="admin-image-item">
                        <img src="<?= e(preg_match('~^https://~i', $caminho) ? $caminho : app_base_url() . '/' . ltrim($caminho, '/')) ?>" alt="Imagem <?= $indice + 1 ?> da publicação">
                        <span><input type="checkbox" name="remover_imagens[]" value="<?= $indice ?>"<?= in_array((string) $indice, (array) ($dados['remover_imagens'] ?? []), true) ? ' checked' : '' ?>> Remover imagem <?= $indice + 1 ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php $pendentes = $imagensPendentes->listar($loteImagens); ?>
        <?php if ($pendentes !== []): ?>
            <div class="admin-image-grid" aria-label="Novas imagens aguardando o salvamento">
                <?php foreach ($pendentes as $indice => $caminho): ?>
                    <label class="admin-image-item">
                        <img src="<?= e(app_base_url() . '/' . $caminho) ?>" alt="Nova imagem <?= $indice + 1 ?>">
                        <span><input type="checkbox" name="remover_pendentes[]" value="<?= $indice ?>"> Remover nova imagem <?= $indice + 1 ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <input id="imagens" name="imagens[]" type="file" accept="image/jpeg,image/png,image/webp,image/gif" multiple aria-describedby="imagens-ajuda imagens-selecao">
        <small id="imagens-selecao" role="status"><?php if ($avisoImagens !== null): ?><?= e($avisoImagens) ?><?php elseif ($pendentes !== []): ?><?= count($pendentes) ?> nova(s) imagem(ns) aguardando o salvamento.<?php else: ?>Nenhuma nova imagem enviada.<?php endif; ?> Use “Enviar e ver imagens” para conferir a seleção antes de salvar. Imagens marcadas para remoção saem no próximo envio.</small>
        <button class="admin-button admin-button--secondary admin-image-send" type="submit" name="acao" value="enviar_imagens" formnovalidate>Enviar e ver imagens</button>
    </div>

    <div class="admin-form-field admin-form-field--wide">
        <label for="link_youtube">Link do YouTube</label>
        <input id="link_youtube" name="link_youtube" type="url" placeholder="https://www.youtube.com/watch?v=..." value="<?= e((string) ($dados['link_youtube'] ?? '')) ?>">
    </div>

    <label class="admin-checkbox admin-form-field--wide">
        <input name="destaque" type="checkbox" value="1"<?= !empty($dados['destaque']) ? ' checked' : '' ?>>
        <span>Marcar como conteúdo de destaque</span>
    </label>
    <small class="admin-form-field--wide">O último conteúdo marcado vira a manchete principal. Os demais aparecem em destaques menores. Editar o texto não altera essa prioridade.</small>

    <div class="admin-form-actions admin-form-field--wide">
        <button class="admin-button admin-button--primary" type="submit"><?= e($rotuloBotao) ?></button>
        <a class="admin-button admin-button--secondary" href="<?= e(admin_url('conteudos_listar.php')) ?>">Cancelar</a>
    </div>
</form>

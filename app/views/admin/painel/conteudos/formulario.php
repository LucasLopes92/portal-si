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

<form class="admin-content-form" method="post" action="<?= e($acaoFormulario) ?>">
    <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">

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
        <label for="link_youtube">Link do YouTube</label>
        <input id="link_youtube" name="link_youtube" type="url" placeholder="https://www.youtube.com/watch?v=..." value="<?= e((string) ($dados['link_youtube'] ?? '')) ?>">
    </div>

    <label class="admin-checkbox admin-form-field--wide">
        <input name="destaque" type="checkbox" value="1"<?= !empty($dados['destaque']) ? ' checked' : '' ?>>
        <span>Marcar como conteúdo de destaque</span>
    </label>

    <div class="admin-form-actions admin-form-field--wide">
        <button class="admin-button admin-button--primary" type="submit"><?= e($rotuloBotao) ?></button>
        <a class="admin-button admin-button--secondary" href="<?= e(admin_url('conteudos_listar.php')) ?>">Cancelar</a>
    </div>
</form>

<section class="admin-page-heading" aria-labelledby="titulo-exclusao">
    <div>
        <p class="admin-page-heading__eyebrow">Gestão editorial</p>
        <h1 id="titulo-exclusao">Excluir conteúdo</h1>
        <p>Confira o registro antes de confirmar a operação.</p>
    </div>
    <a class="admin-button admin-button--secondary" href="<?= e(admin_url('conteudos_listar.php')) ?>">Voltar à listagem</a>
</section>

<?php if ($erro !== null): ?>
    <div class="admin-alert admin-alert--error" role="alert"><?= e($erro) ?></div>
<?php endif; ?>

<section class="admin-confirmation" aria-labelledby="confirmar-exclusao">
    <h2 id="confirmar-exclusao">Deseja realmente excluir este conteúdo?</h2>
    <p>O registro deixará de aparecer no painel e no portal público.</p>

    <dl>
        <dt>ID</dt>
        <dd><?= (int) $conteudo['id'] ?></dd>
        <dt>Título</dt>
        <dd><?= e((string) $conteudo['titulo']) ?></dd>
        <dt>Status</dt>
        <dd><?= e(ucfirst((string) $conteudo['status'])) ?></dd>
    </dl>

    <p class="admin-confirmation__warning">Esta ação não pode ser desfeita pelo painel.</p>

    <form class="admin-form-actions" method="post" action="<?= e(admin_url('conteudos_excluir.php')) ?>">
        <input type="hidden" name="csrf_token" value="<?= e(obter_token_csrf()) ?>">
        <input type="hidden" name="id" value="<?= (int) $conteudo['id'] ?>">
        <button class="admin-button admin-button--danger" type="submit">Confirmar exclusão</button>
        <a class="admin-button admin-button--secondary" href="<?= e(admin_url('conteudos_listar.php')) ?>">Cancelar</a>
    </form>
</section>

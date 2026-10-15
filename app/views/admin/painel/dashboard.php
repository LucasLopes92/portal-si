<section class="admin-page-heading" aria-labelledby="titulo-dashboard">
    <div>
        <p class="admin-page-heading__eyebrow">Painel administrativo</p>
        <h1 id="titulo-dashboard">Visão geral</h1>
        <p>Acompanhe o estado editorial do Portal SI e acesse os módulos de gestão.</p>
    </div>
    <a class="admin-button admin-button--primary" href="<?= e(admin_url('conteudos_listar.php')) ?>">Gerenciar conteúdos</a>
</section>

<section class="admin-cards" aria-label="Indicadores do portal">
    <article class="admin-card">
        <span>Total de conteúdos</span>
        <strong><?= (int) $resumo['total_conteudos'] ?></strong>
        <small>Todos os estados editoriais</small>
    </article>
    <article class="admin-card admin-card--success">
        <span>Publicados</span>
        <strong><?= (int) $resumo['publicados'] ?></strong>
        <small>Visíveis na Home pública</small>
    </article>
    <article class="admin-card admin-card--warning">
        <span>Rascunhos</span>
        <strong><?= (int) $resumo['rascunhos'] ?></strong>
        <small>Aguardando publicação</small>
    </article>
    <article class="admin-card admin-card--neutral">
        <span>Categorias ativas</span>
        <strong><?= (int) $resumo['categorias_ativas'] ?></strong>
        <small>Disponíveis para classificação</small>
    </article>
</section>

<section class="admin-panel" aria-labelledby="titulo-proximos-passos">
    <div class="admin-panel__heading">
        <div>
            <p class="admin-page-heading__eyebrow">Fluxo editorial</p>
            <h2 id="titulo-proximos-passos">Próximos passos</h2>
        </div>
    </div>
    <div class="admin-flow">
        <div><strong>1</strong><span>Criar o conteúdo</span></div>
        <div><strong>2</strong><span>Revisar as informações</span></div>
        <div><strong>3</strong><span>Publicar na Home</span></div>
    </div>
</section>

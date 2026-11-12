<?php
require_once __DIR__ . '/../layout/header.php';
$contexto_sem_filtros = array_diff_key($contexto_busca, ['busca' => true, 'data' => true]);
?>
<?php if (!$listagem_publica && $slug_categoria === null): ?>
<?php require __DIR__ . '/abertura.php'; ?>
<?php endif; ?>

<section class="portal-section portal-container<?= $listagem_publica ? ' portal-section--listing' : '' ?>" id="publicacoes" aria-labelledby="titulo-publicacoes">
    <?php if ($listagem_publica): ?>
        <header class="editoria-header">
            <div>
                <p class="editoria-eyebrow">Portal SI <span aria-hidden="true">/</span> <?= ($categoria_atual['slug'] ?? '') === 'eventos' ? 'Vida no campus' : 'Informação e conhecimento' ?></p>
                <h1 id="titulo-publicacoes"><?= e($categoria_atual['nome'] ?? 'Publicações') ?></h1>
                <p class="editoria-description"><?= ($categoria_atual['slug'] ?? '') === 'eventos' ? 'Encontros, experiências e atividades que movimentam o curso.' : 'O que acontece no curso, as ideias e as pessoas que fazem parte dele.' ?></p>
            </div>
            <span class="editoria-signature">Sistemas de Informação<span>Faculdades ESUCRI</span></span>
        </header>
    <?php endif; ?>
    <details class="portal-search<?= $listagem_publica ? ' portal-search--editorial' : '' ?>"<?= $busca_ativa ? ' open' : '' ?>>
    <summary class="portal-section__heading<?= $listagem_publica ? ' portal-listing-heading' : '' ?>">
        <div class="portal-listing-title">
            <?php if ($listagem_publica): ?>
                <p class="editoria-edition"><?= $busca_ativa ? 'Resultado da busca' : 'Últimas publicações' ?><span><?= $total_publicacoes ?> <?= $busca_ativa ? ($total_publicacoes === 1 ? 'resultado' : 'resultados') : ($total_publicacoes === 1 ? 'publicação' : 'publicações') ?></span></p>
            <?php else: ?>
                <h2 id="titulo-publicacoes">Publicações recentes</h2>
            <?php endif; ?>
        </div>
        <span class="portal-search-toggle">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="10.5" cy="10.5" r="6.5"/><path d="m16 16 4.5 4.5"/></svg>
            Buscar
            <svg class="portal-search-chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path d="m7 10 5 5 5-5"/></svg>
        </span>
    </summary>
    <form class="portal-search-form" method="get" action="<?= e($url_home) ?>#publicacoes" role="search" aria-label="Buscar publicações">
        <?php foreach ($contexto_sem_filtros as $campo => $valor): ?>
            <input type="hidden" name="<?= e($campo) ?>" value="<?= e($valor) ?>">
        <?php endforeach; ?>
        <div class="portal-search-field">
            <label for="busca-palavra">Palavra-chave</label>
            <input id="busca-palavra" type="search" name="busca" value="<?= e($palavra_busca) ?>" maxlength="200" placeholder="Título ou conteúdo">
        </div>
        <div class="portal-search-field">
            <label for="busca-data">Data de publicação</label>
            <input id="busca-data" type="date" name="data" value="<?= e($data_busca) ?>"<?= $erro_busca !== '' ? ' aria-invalid="true" aria-describedby="erro-busca"' : '' ?>>
        </div>
        <button class="portal-search-submit" type="submit">Filtrar</button>
        <?php if ($busca_ativa): ?>
            <a class="portal-search-clear" href="<?= e($url_home . ($contexto_sem_filtros === [] ? '' : '?' . http_build_query($contexto_sem_filtros)) . '#publicacoes') ?>">Limpar</a>
        <?php endif; ?>
        <?php if ($erro_busca !== ''): ?>
            <p class="portal-search-error" id="erro-busca" role="alert"><?= e($erro_busca) ?></p>
        <?php endif; ?>
    </form>
    </details>

    <?php if ($slug_categoria !== null && $categoria_atual === null): ?>
        <div class="portal-empty" role="status">
            <span class="portal-empty__symbol" aria-hidden="true">?</span>
            <div><h3>Categoria não encontrada</h3><p>Confira as categorias disponíveis no menu ou volte para a página inicial.</p><a class="portal-text-link" href="<?= e($url_home) ?>">Voltar ao início <span aria-hidden="true">→</span></a></div>
        </div>
    <?php elseif ($erro_busca !== ''): ?>
        <?php // O formulário apresenta o erro da data, sem executar uma busca inválida. ?>
    <?php elseif (empty($conteudos_recentes) && $busca_ativa): ?>
        <div class="portal-empty" role="status">
            <div><h3>Nenhuma publicação encontrada</h3><p>Tente outra palavra-chave ou data.</p></div>
        </div>
    <?php elseif (empty($conteudos_recentes)): ?>
        <div class="portal-empty" role="status">
            <span class="portal-empty__symbol" aria-hidden="true">✦</span>
            <div><h3>Novidades a caminho</h3><p><?= $categoria_atual === null ? 'Ainda não há publicações disponíveis. Explore as categorias do menu e volte em breve.' : 'Ainda não há publicações nesta categoria. Acompanhe as próximas atualizações do portal.' ?></p></div>
        </div>
    <?php else: ?>
        <?php require __DIR__ . '/editorial.php'; ?>
    <?php endif; ?>

    <?php if ($categoria_atual !== null && $total_paginas > 1): ?>
        <nav class="portal-pagination" aria-label="Paginação das publicações">
            <?php if ($pagina_atual > 1): ?>
                <a href="<?= e($url_pagina_base . 'pagina=' . ($pagina_atual - 1) . '#publicacoes') ?>" rel="prev">Anterior</a>
            <?php endif; ?>
            <span>Página <?= (int) $pagina_atual ?> de <?= (int) $total_paginas ?></span>
            <?php if ($pagina_atual < $total_paginas): ?>
                <a href="<?= e($url_pagina_base . 'pagina=' . ($pagina_atual + 1) . '#publicacoes') ?>" rel="next">Próxima</a>
            <?php endif; ?>
        </nav>
    <?php endif; ?>

    <?php if (!$listagem_publica && $slug_categoria === null && $categorias_menu !== []): ?>
        <section class="portal-category-counts" aria-labelledby="titulo-categorias-publicacoes">
            <h2 id="titulo-categorias-publicacoes">Navegue por categoria</h2>
            <div class="portal-category-counts__list">
                <?php foreach ($categorias_menu as $categoria): ?>
                    <a class="portal-category-chip" href="<?= e($url_home . '?categoria=' . rawurlencode($categoria['slug'])) ?>">
                        <span><?= e($categoria['nome']) ?></span>
                        <span class="portal-category-chip__count"><?= (int) $categoria['total_publicados'] ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

<?php require_once __DIR__ . '/../layout/header.php'; ?>
<?php if (!$listagem_publica && $slug_categoria === null): ?>
<section class="portal-hero" aria-labelledby="titulo-hero">
    <div class="portal-container portal-hero__inner">
        <div class="portal-hero__content">
            <p class="portal-eyebrow">Portal de Comunicação SI <span aria-hidden="true">/</span> ESUCRI</p>
            <h1 id="titulo-hero">Conhecimento que se conecta com o mundo.</h1>
            <p class="portal-hero__lead">Acompanhe as notícias e os eventos do curso de Sistemas de Informação.</p>
            <a class="portal-hero__link" href="#publicacoes">Explorar publicações <span aria-hidden="true">↗</span></a>
        </div>
        <div class="portal-hero__art" aria-hidden="true">
            <span class="portal-hero__orbit portal-hero__orbit--one"></span>
            <span class="portal-hero__orbit portal-hero__orbit--two"></span>
            <span class="portal-hero__monogram">SI<span>.</span></span>
            <span class="portal-hero__caption">Ideias em movimento</span>
        </div>
    </div>
</section>
<?php endif; ?>

<section class="portal-section portal-container<?= $listagem_publica ? ' portal-section--listing' : '' ?>" id="publicacoes" aria-labelledby="titulo-publicacoes">
    <details class="portal-search"<?= $busca_ativa ? ' open' : '' ?>>
    <summary class="portal-section__heading<?= $listagem_publica ? ' portal-listing-heading' : '' ?>">
        <div class="portal-listing-title">
            <?php if ($listagem_publica): ?>
                <span class="portal-listing-icon" aria-hidden="true">
                    <?php if (($categoria_atual['slug'] ?? '') === 'eventos'): ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="16" rx="3"/><path d="M7 3v4m10-4v4M3 11h18m-13 4h2m4 0h2m-8 3h2"/></svg>
                    <?php else: ?>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="4" y="3" width="16" height="18" rx="3"/><path d="M8 7h8M8 11h8m-8 4h3m2 0h3m-8 3h3m2 0h3"/></svg>
                    <?php endif; ?>
                </span>
                <div>
                    <h1 id="titulo-publicacoes"><?= e($categoria_atual['nome'] ?? 'Todas as publicações') ?></h1>
                    <p class="portal-listing-count"><?= $total_publicacoes ?> <?= $busca_ativa ? ($total_publicacoes === 1 ? 'resultado' : 'resultados') : ($total_publicacoes === 1 ? 'publicação' : 'publicações') ?></p>
                </div>
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
        <?php foreach ($contexto_busca as $campo => $valor): ?>
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
            <a class="portal-search-clear" href="<?= e($url_home . ($contexto_busca === [] ? '' : '?' . http_build_query($contexto_busca)) . '#publicacoes') ?>">Limpar</a>
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
        <div class="grade-publicacoes">
            <?php foreach ($conteudos_recentes as $item): ?>
                <?php $data_publicacao = (new DateTimeImmutable($item['publicado_em']))->setTimezone(new DateTimeZone('America/Sao_Paulo')); ?>
                <article class="cartao-conteudo<?= $item['categoria_slug'] === 'eventos' ? ' cartao-conteudo--evento' : '' ?>">
                    <header class="cartao-cabecalho">
                        <div class="cartao-meta">
                            <?php if ($categoria_atual === null): ?>
                                <a class="cartao-categoria" href="<?= e($url_home . '?categoria=' . rawurlencode($item['categoria_slug'])) ?>"><?= e($item['categoria_nome']) ?></a>
                            <?php endif; ?>
                            <time class="cartao-data" title="Data de publicação" datetime="<?= e($data_publicacao->format('Y-m-d')) ?>"><?= e($data_publicacao->format('d/m/Y')) ?></time>
                        </div>
                        <<?= $listagem_publica ? 'h2' : 'h3' ?> class="cartao-titulo"><?= e($item['titulo']) ?></<?= $listagem_publica ? 'h2' : 'h3' ?>>
                    </header>
                    <p class="cartao-resumo"><?= e($item['resumo'] ?: mb_strimwidth(strip_tags($item['corpo']), 0, 160, '…', 'UTF-8')) ?></p>
                    <footer class="cartao-rodape">
                        <span>Por <?= e($item['autor_nome']) ?></span>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../layout/footer.php'; ?>

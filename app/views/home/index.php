<?php require_once __DIR__ . '/../layout/header.php'; ?>
<section class="portal-hero" aria-labelledby="titulo-hero">
    <div class="portal-container portal-hero__inner">
        <div class="portal-hero__content">
            <p class="portal-eyebrow">Portal de Comunicação SI <span aria-hidden="true">/</span> ESUCRI</p>
            <h1 id="titulo-hero">Conhecimento que se conecta com o mundo.</h1>
            <p class="portal-hero__lead">Acompanhe projetos, pesquisas, eventos e oportunidades do curso de Sistemas de Informação.</p>
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

<section class="portal-section portal-container" id="publicacoes" aria-labelledby="titulo-publicacoes">
    <div class="portal-section__heading">
        <div>
            <p class="portal-eyebrow portal-eyebrow--red">Em pauta</p>
            <h2 id="titulo-publicacoes"><?= $categoria_atual === null ? 'Publicações recentes' : e($categoria_atual['nome']) ?></h2>
            <p class="portal-section__intro"><?= $categoria_atual === null ? 'Novidades da nossa comunidade acadêmica, em ordem de publicação.' : 'Publicações desta categoria, da mais recente para a mais antiga.' ?></p>
        </div>
        <?php if ($categoria_atual !== null): ?>
            <a class="portal-text-link" href="<?= e($url_home) ?>">Ver todas as publicações <span aria-hidden="true">→</span></a>
        <?php endif; ?>
    </div>

    <?php if ($slug_categoria !== null && $categoria_atual === null): ?>
        <div class="portal-empty" role="status">
            <span class="portal-empty__symbol" aria-hidden="true">?</span>
            <div><h3>Categoria não encontrada</h3><p>Confira as categorias disponíveis no menu ou volte para a página inicial.</p><a class="portal-text-link" href="<?= e($url_home) ?>">Voltar ao início <span aria-hidden="true">→</span></a></div>
        </div>
    <?php elseif (empty($conteudos_recentes)): ?>
        <div class="portal-empty" role="status">
            <span class="portal-empty__symbol" aria-hidden="true">✦</span>
            <div><h3>Novidades a caminho</h3><p><?= $categoria_atual === null ? 'Ainda não há publicações disponíveis. Explore as categorias do menu e volte em breve.' : 'Ainda não há publicações nesta categoria. Acompanhe as próximas atualizações do portal.' ?></p></div>
        </div>
    <?php else: ?>
        <div class="grade-publicacoes">
            <?php foreach ($conteudos_recentes as $item): ?>
                <article class="cartao-conteudo">
                    <header class="cartao-cabecalho">
                        <a class="cartao-categoria" href="<?= e($url_home . '?categoria=' . rawurlencode($item['categoria_slug'])) ?>"><?= e($item['categoria_nome']) ?></a>
                        <h3 class="cartao-titulo"><?= e($item['titulo']) ?></h3>
                    </header>
                    <p class="cartao-resumo"><?= e($item['resumo'] ?: mb_strimwidth(strip_tags($item['corpo']), 0, 160, '…', 'UTF-8')) ?></p>
                    <footer class="cartao-rodape">
                        <span>Por <?= e($item['autor_nome']) ?></span>
                        <time datetime="<?= e(date('Y-m-d', strtotime($item['publicado_em']))) ?>"><?= e(date('d/m/Y', strtotime($item['publicado_em']))) ?></time>
                    </footer>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section class="portal-about" aria-labelledby="titulo-projeto">
    <div class="portal-container portal-about__inner">
        <div><p class="portal-eyebrow portal-eyebrow--red">Sobre o projeto</p><h2 id="titulo-projeto">Tecnologia com impacto na comunidade.</h2></div>
        <p>O Portal SI reúne iniciativas de ensino, pesquisa e extensão das Faculdades ESUCRI. Um espaço para compartilhar o que estudantes e professores constroem dentro e fora da sala de aula.</p>
    </div>
</section>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

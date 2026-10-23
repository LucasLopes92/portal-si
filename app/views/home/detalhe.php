<?php require_once __DIR__ . '/../layout/header.php'; ?>
<section class="portal-section portal-container portal-article" aria-labelledby="titulo-conteudo">
    <?php if ($conteudo_detalhe === null): ?>
        <div class="portal-empty" role="status">
            <span class="portal-empty__symbol" aria-hidden="true">?</span>
            <div>
                <h1 id="titulo-conteudo">Conteúdo não encontrado</h1>
                <p>Esta publicação não existe ou não está disponível.</p>
                <a class="portal-text-link" href="<?= e($url_home) ?>">Voltar às publicações <span aria-hidden="true">→</span></a>
            </div>
        </div>
    <?php else: ?>
        <?php
        $imagem_detalhe = imagem_publica_src($conteudo_detalhe['imagem_capa'] ?? null);
        $youtube_detalhe = youtube_embed_url($conteudo_detalhe['link_youtube'] ?? null);
        ?>
        <a class="portal-text-link portal-article__back" href="<?= e($url_home . '?categoria=' . rawurlencode($conteudo_detalhe['categoria_slug'])) ?>">← Voltar para <?= e($conteudo_detalhe['categoria_nome']) ?></a>
        <header class="portal-article__header">
            <a class="cartao-categoria" href="<?= e($url_home . '?categoria=' . rawurlencode($conteudo_detalhe['categoria_slug'])) ?>"><?= e($conteudo_detalhe['categoria_nome']) ?></a>
            <h1 id="titulo-conteudo"><?= e($conteudo_detalhe['titulo']) ?></h1>
            <p class="portal-article__meta">Por <?= e($conteudo_detalhe['autor_nome']) ?>
                <span aria-hidden="true">·</span>
                <time datetime="<?= e(date('Y-m-d', strtotime($conteudo_detalhe['publicado_em']))) ?>"><?= e(date('d/m/Y', strtotime($conteudo_detalhe['publicado_em']))) ?></time>
            </p>
        </header>

        <?php if ($imagem_detalhe !== null): ?>
            <img class="portal-article__cover" src="<?= e($imagem_detalhe) ?>" alt="<?= e($conteudo_detalhe['titulo']) ?>">
        <?php endif; ?>

        <?php if ($youtube_detalhe !== null): ?>
            <div class="portal-video-embed">
                <iframe src="<?= e($youtube_detalhe) ?>" title="Vídeo: <?= e($conteudo_detalhe['titulo']) ?>" loading="lazy" referrerpolicy="strict-origin-when-cross-origin" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
            </div>
        <?php endif; ?>

        <?php if (!empty($conteudo_detalhe['resumo'])): ?>
            <p class="portal-article__summary"><?= e($conteudo_detalhe['resumo']) ?></p>
        <?php endif; ?>
        <div class="portal-article__body"><?= nl2br(e($conteudo_detalhe['corpo'])) ?></div>
    <?php endif; ?>
</section>
<?php require_once __DIR__ . '/../layout/footer.php'; ?>

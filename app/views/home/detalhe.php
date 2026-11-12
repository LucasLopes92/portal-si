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
        $imagens_detalhe = imagens_publicacao($conteudo_detalhe);
        $youtube_detalhe = youtube_embed_url($conteudo_detalhe['link_youtube'] ?? null);
        $data_detalhe = (new DateTimeImmutable($conteudo_detalhe['publicado_em']))
            ->setTimezone(new DateTimeZone('America/Sao_Paulo'));
        ?>
        <a class="portal-text-link portal-article__back" href="<?= e($url_home . '?categoria=' . rawurlencode($conteudo_detalhe['categoria_slug'])) ?>">← Voltar para <?= e($conteudo_detalhe['categoria_nome']) ?></a>
        <header class="portal-article__header">
            <a class="cartao-categoria" href="<?= e($url_home . '?categoria=' . rawurlencode($conteudo_detalhe['categoria_slug'])) ?>"><?= e($conteudo_detalhe['categoria_nome']) ?></a>
            <h1 id="titulo-conteudo"><?= e($conteudo_detalhe['titulo']) ?></h1>
            <p class="portal-article__meta">Por <?= e($conteudo_detalhe['autor_nome']) ?>
                <span aria-hidden="true">·</span>
                <time datetime="<?= e($data_detalhe->format('Y-m-d')) ?>"><?= e($data_detalhe->format('d/m/Y')) ?></time>
            </p>
        </header>

        <?php if ($imagens_detalhe !== []): ?>
            <section class="portal-gallery" data-gallery aria-label="Imagens da publicação" tabindex="0">
                <div class="portal-gallery__track" data-gallery-track>
                    <?php foreach ($imagens_detalhe as $indice => $caminho): ?>
                        <?php $src = preg_match('~^https://~i', $caminho) ? $caminho : app_base_url() . '/' . ltrim($caminho, '/'); ?>
                        <figure class="portal-gallery__slide">
                            <img src="<?= e($src) ?>" alt="<?= e($conteudo_detalhe['titulo']) ?> — imagem <?= $indice + 1 ?> de <?= count($imagens_detalhe) ?>"<?= $indice > 0 ? ' loading="lazy"' : '' ?>>
                        </figure>
                    <?php endforeach; ?>
                </div>
                <?php if (count($imagens_detalhe) > 1): ?>
                    <div class="portal-gallery__controls" data-gallery-controls hidden>
                        <button type="button" data-gallery-prev aria-label="Imagem anterior">← Anterior</button>
                        <span data-gallery-count role="status" aria-live="polite">1 de <?= count($imagens_detalhe) ?></span>
                        <button type="button" data-gallery-next aria-label="Próxima imagem">Próxima →</button>
                    </div>
                    <div class="portal-gallery__thumbs" data-gallery-thumbs hidden>
                        <?php foreach ($imagens_detalhe as $indice => $caminho): ?>
                            <?php $src = preg_match('~^https://~i', $caminho) ? $caminho : app_base_url() . '/' . ltrim($caminho, '/'); ?>
                            <button type="button" data-gallery-index="<?= $indice ?>" aria-label="Ver imagem <?= $indice + 1 ?>" aria-current="<?= $indice === 0 ? 'true' : 'false' ?>"><img src="<?= e($src) ?>" alt="" loading="lazy"></button>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </section>
            <script src="<?= e(app_base_url()) ?>/public/assets/js/galeria.js" defer></script>
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

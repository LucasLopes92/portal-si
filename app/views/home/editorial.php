<?php
// A consulta prioriza os destaques pelo instante de marcação antes de paginar.
$itens_editoriais = array_values($conteudos_recentes);
$mostrar_manchete = !$busca_ativa && $pagina_atual === 1;
$evento_editorial = ($categoria_atual['slug'] ?? '') === 'eventos';
$url_publicacao = static fn (array $item): string => $url_home . '?conteudo=' . (int) $item['id'];
$src_editorial = static function (array $item): ?string {
    $src = imagem_publica_src($item['imagem_capa'] ?? null);
    return $src === null ? null : (preg_match('~^https://~i', $src) ? $src : app_base_url() . '/' . ltrim($src, '/'));
};
$data_editorial = static fn (array $item): DateTimeImmutable => (new DateTimeImmutable($item['publicado_em']))->setTimezone(new DateTimeZone('America/Sao_Paulo'));
?>
<div class="editorial-publications">
    <?php if ($mostrar_manchete): ?>
        <?php
        $indice_manchete = 0;
        foreach ($itens_editoriais as $indice => $publicacao) {
            if (!empty($publicacao['destaque'])) { $indice_manchete = $indice; break; }
        }
        $manchete = $itens_editoriais[$indice_manchete];
        unset($itens_editoriais[$indice_manchete]);
        $imagem_manchete = $src_editorial($manchete);
        $data_manchete = $data_editorial($manchete);
        ?>
        <article class="editorial-lead<?= $imagem_manchete === null ? ' editorial-lead--text' : '' ?><?= $manchete['categoria_slug'] === 'eventos' ? ' editorial-lead--event' : '' ?>">
            <?php if ($imagem_manchete !== null): ?>
                <a class="editorial-lead__image" href="<?= e($url_publicacao($manchete)) ?>" tabindex="-1" aria-hidden="true">
                    <img src="<?= e($imagem_manchete) ?>" alt="" fetchpriority="high">
                </a>
            <?php endif; ?>
            <div class="editorial-lead__content">
                <p class="editorial-kicker"><span aria-hidden="true"></span><?= !empty($manchete['destaque']) ? 'Em destaque' : 'Publicação mais recente' ?></p>
                <h2><a href="<?= e($url_publicacao($manchete)) ?>"><?= e($manchete['titulo']) ?></a></h2>
                <p class="editorial-lead__summary"><?= e($manchete['resumo'] ?: mb_strimwidth(strip_tags($manchete['corpo']), 0, 260, '…', 'UTF-8')) ?></p>
                <div class="editorial-byline">
                    <span>Por <?= e($manchete['autor_nome']) ?></span>
                    <time datetime="<?= e($data_manchete->format(DATE_ATOM)) ?>">Publicado em <?= e($data_manchete->format('d/m/Y')) ?></time>
                </div>
                <a class="editorial-read" href="<?= e($url_publicacao($manchete)) ?>">Ler <?= $manchete['categoria_slug'] === 'eventos' ? 'sobre o evento' : 'a notícia' ?> <span aria-hidden="true">↗</span></a>
            </div>
        </article>
    <?php endif; ?>
    <?php
    $destaques_secundarios = [];
    if ($mostrar_manchete) {
        foreach ($itens_editoriais as $indice => $item) {
            if (!empty($item['destaque'])) {
                $destaques_secundarios[] = $item;
                unset($itens_editoriais[$indice]);
            }
        }
    }
    ?>
    <?php if ($destaques_secundarios !== []): ?>
        <section class="editorial-highlights" aria-labelledby="outros-destaques">
            <div class="editorial-section-title"><h2 id="outros-destaques">Também em destaque</h2><span aria-hidden="true"></span></div>
            <div class="editorial-highlights__grid<?= count($destaques_secundarios) % 2 === 1 ? ' editorial-highlights__grid--odd' : '' ?>">
                <?php foreach ($destaques_secundarios as $item): ?>
                    <?php $imagem_item = $src_editorial($item); $data_item = $data_editorial($item); ?>
                    <article class="editorial-highlight<?= $item['categoria_slug'] === 'eventos' ? ' editorial-highlight--event' : '' ?>">
                        <div class="editorial-highlight__content">
                            <p class="editorial-highlight__label">Em destaque <span aria-hidden="true">↗</span></p>
                            <h3><a href="<?= e($url_publicacao($item)) ?>"><?= e($item['titulo']) ?></a></h3>
                            <time datetime="<?= e($data_item->format(DATE_ATOM)) ?>" title="Data de publicação"><?= e($data_item->format('d/m/Y')) ?></time>
                        </div>
                        <?php if ($imagem_item !== null): ?>
                            <a class="editorial-highlight__image" href="<?= e($url_publicacao($item)) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($imagem_item) ?>" alt="" loading="lazy"></a>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>
    <?php if ($itens_editoriais !== []): ?>
        <?php if ($mostrar_manchete): ?>
            <div class="editorial-section-title"><h2><?= $evento_editorial ? 'Mais eventos' : ($categoria_atual === null ? 'Mais publicações' : 'Mais notícias') ?></h2><span aria-hidden="true"></span></div>
        <?php endif; ?>
        <div class="editorial-grid">
            <?php foreach ($itens_editoriais as $item): ?>
                <?php $imagem_item = $src_editorial($item); $data_item = $data_editorial($item); ?>
                <article class="editorial-story<?= !empty($item['destaque']) ? ' editorial-story--highlight' : '' ?><?= $imagem_item === null ? ' editorial-story--text' : '' ?>">
                    <?php if ($imagem_item !== null): ?>
                        <a class="editorial-story__image" href="<?= e($url_publicacao($item)) ?>" tabindex="-1" aria-hidden="true"><img src="<?= e($imagem_item) ?>" alt="" loading="lazy"></a>
                    <?php endif; ?>
                    <div class="editorial-story__content">
                        <p class="editorial-story__meta"><span><?= !empty($item['destaque']) ? 'Em destaque · ' : '' ?><?= e($item['categoria_nome']) ?></span><time datetime="<?= e($data_item->format(DATE_ATOM)) ?>" title="Data de publicação"><?= e($data_item->format('d/m/Y')) ?></time></p>
                        <<?= $mostrar_manchete ? 'h3' : 'h2' ?>><a href="<?= e($url_publicacao($item)) ?>"><?= e($item['titulo']) ?></a></<?= $mostrar_manchete ? 'h3' : 'h2' ?>>
                        <p class="editorial-story__summary"><?= e($item['resumo'] ?: mb_strimwidth(strip_tags($item['corpo']), 0, 150, '…', 'UTF-8')) ?></p>
                        <p class="editorial-story__author">Por <?= e($item['autor_nome']) ?></p>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php
$destinos_inicio = ['publicacoes'=>'#publicacoes', 'noticias'=>app_base_url().'/public/index.php?categoria=noticias', 'eventos'=>app_base_url().'/public/index.php?categoria=eventos'];
$foto_inicio ??= app_base_url() . '/' . ltrim($config_inicio['imagem'], '/');
?>
<section class="home-cover" aria-labelledby="titulo-hero">
    <img class="home-cover__photo" data-home-image src="<?= e($foto_inicio) ?>" alt="<?= e($config_inicio['imagem_alt']) ?>" style="object-position: 50% <?= (int) $config_inicio['imagem_posicao'] ?>%" fetchpriority="high">
    <div class="home-cover__shade" aria-hidden="true"></div>
    <div class="home-cover__inner">
        <div class="home-cover__content">
            <p class="home-cover__label" data-home-field="etiqueta"><?= e($config_inicio['etiqueta']) ?></p>
            <h1 id="titulo-hero" data-home-field="titulo"><?= e($config_inicio['titulo']) ?></h1>
            <p class="home-cover__description" data-home-field="descricao"><?= e($config_inicio['descricao']) ?></p>
            <a class="home-cover__button" data-home-link href="<?= e($destinos_inicio[$config_inicio['botao_destino']] ?? '#publicacoes') ?>"><span data-home-field="botao_texto"><?= e($config_inicio['botao_texto']) ?></span><span aria-hidden="true">↗</span></a>
        </div>
    </div>
</section>

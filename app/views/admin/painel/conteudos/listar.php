<section class="admin-page-heading" aria-labelledby="titulo-conteudos">
    <div>
        <p class="admin-page-heading__eyebrow">Gestão editorial</p>
        <h1 id="titulo-conteudos">Conteúdos</h1>
        <p>Consulte as publicações cadastradas, seus autores e o estado de publicação.</p>
    </div>
    <a class="admin-button admin-button--primary" href="<?= e(admin_url('conteudos_cadastrar.php')) ?>">+ Novo conteúdo</a>
</section>

<?php if ($flash !== null): ?>
    <p class="admin-alert admin-alert--<?= e((string) $flash['tipo']) ?>" role="status"><?= e((string) $flash['mensagem']) ?></p>
<?php endif; ?>

<section class="admin-panel admin-panel--table" aria-label="Lista de conteúdos">
    <div class="admin-table-summary">
        <p><strong><?= (int) $paginacao['total'] ?></strong> conteúdo(s) encontrado(s)</p>
        <p>Página <?= (int) $paginacao['pagina'] ?> de <?= (int) $paginacao['total_paginas'] ?></p>
    </div>

    <div class="admin-table-wrapper">
        <table class="admin-table">
            <thead>
                <tr>
                    <th scope="col">Título</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Autor</th>
                    <th scope="col">Status</th>
                    <th scope="col">Data</th>
                    <th scope="col"><span class="visually-hidden">Ações</span></th>
                </tr>
            </thead>
            <tbody>
                <?php if ($paginacao['itens'] === []): ?>
                    <tr>
                        <td class="admin-table__empty" colspan="6">Nenhum conteúdo cadastrado até o momento.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($paginacao['itens'] as $item): ?>
                        <?php
                        $status = (string) $item['status'];
                        $dataReferencia = $item['publicado_em'] ?: $item['criado_em'];
                        $dataFormatada = $dataReferencia
                            ? date('d/m/Y', strtotime((string) $dataReferencia))
                            : '—';
                        ?>
                        <tr>
                            <td>
                                <strong><?= e((string) $item['titulo']) ?></strong>
                                <?php if ((bool) $item['destaque']): ?><small class="admin-featured">Destaque</small><?php endif; ?>
                            </td>
                            <td><?= e((string) $item['categoria_nome']) ?></td>
                            <td><?= e((string) $item['autor_nome']) ?></td>
                            <td><span class="admin-status admin-status--<?= e($status) ?>"><?= e(ucfirst($status)) ?></span></td>
                            <td><time datetime="<?= e(date('Y-m-d', strtotime((string) $dataReferencia))) ?>"><?= e($dataFormatada) ?></time></td>
                            <td>
                                <div class="admin-table-actions">
                                    <a href="<?= e(admin_url('conteudos_editar.php?id=' . (int) $item['id'])) ?>">Editar</a>
                                    <a class="is-danger" href="<?= e(admin_url('conteudos_excluir.php?id=' . (int) $item['id'])) ?>">Excluir</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($paginacao['total_paginas'] > 1): ?>
        <nav class="admin-pagination" aria-label="Paginação de conteúdos">
            <?php for ($pagina = 1; $pagina <= $paginacao['total_paginas']; $pagina++): ?>
                <a<?= $pagina === $paginacao['pagina'] ? ' class="is-current" aria-current="page"' : '' ?> href="<?= e(admin_url('conteudos_listar.php?pagina=' . $pagina)) ?>"><?= $pagina ?></a>
            <?php endfor; ?>
        </nav>
    <?php endif; ?>
</section>

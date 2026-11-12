'use strict';

const imageInput = document.querySelector('#imagens');
if (imageInput) {
    const preview = document.querySelector('#imagens-preview');
    const status = document.querySelector('#imagens-selecao');
    let selected = [];
    let urls = [];
    const render = () => {
        urls.forEach((url) => URL.revokeObjectURL(url));
        urls = [];
        preview.replaceChildren();
        const transfer = new DataTransfer();
        selected.forEach((file, index) => {
            transfer.items.add(file);
            const item = document.createElement('div');
            item.className = 'admin-image-item';
            const image = document.createElement('img');
            const url = URL.createObjectURL(file);
            urls.push(url);
            image.src = url;
            image.alt = file.name;
            const label = document.createElement('span');
            label.textContent = file.name;
            const remove = document.createElement('button');
            remove.type = 'button';
            remove.textContent = 'Remover';
            remove.setAttribute('aria-label', `Remover ${file.name}`);
            remove.addEventListener('click', () => {
                selected.splice(index, 1);
                render();
            });
            item.append(image, label, remove);
            preview.append(item);
        });
        imageInput.files = transfer.files;
        status.textContent = selected.length ? `${selected.length} nova(s) imagem(ns) selecionada(s).` : 'Nenhuma nova imagem selecionada.';
    };
    imageInput.addEventListener('change', () => {
        selected.push(...[...imageInput.files].filter((file) => !selected.some((existing) => existing.name === file.name && existing.size === file.size && existing.lastModified === file.lastModified)));
        render();
    });
}

'use strict';
(() => {
    const form = document.querySelector('#inicio-form');
    const frame = document.querySelector('#inicio-preview');
    const canvas = document.querySelector('#inicio-preview-canvas');
    const wrap = document.querySelector('#inicio-frame-wrap');
    const file = document.querySelector('#inicio-imagem');
    const discard = document.querySelector('#inicio-descartar-imagem');
    const state = document.querySelector('#inicio-estado');
    let viewport = 1440;
    let photo = null;
    let previewObserver;
    const resize = () => {
        if (!frame.contentDocument?.querySelector('.home-cover')) return;
        const scale = Math.min(1, canvas.clientWidth / viewport);
        frame.style.width = `${viewport}px`;
        frame.style.transform = `scale(${scale})`;
        frame.style.height = `${frame.contentDocument.querySelector('.home-cover').offsetHeight}px`;
        wrap.style.width = `${viewport * scale}px`;
        wrap.style.height = `${frame.contentDocument.querySelector('.home-cover').offsetHeight * scale}px`;
    };
    const sync = () => {
        const doc = frame.contentDocument;
        if (!doc?.querySelector('.home-cover')) return;
        form.querySelectorAll('[data-preview-text]').forEach((input) => {
            doc.querySelector(`[data-home-field="${input.dataset.previewText}"]`).textContent = input.value;
        });
        const image = doc.querySelector('[data-home-image]');
        const src = photo || frame.dataset.originalImage;
        if (image.getAttribute('src') !== src) image.src = src;
        image.alt = document.querySelector('#inicio-alt').value;
        const position = document.querySelector('#inicio-posicao').value;
        image.style.objectPosition = `50% ${position}%`;
        document.querySelector('#inicio-posicao-valor').textContent = `${position}%`;
        const destinations = { publicacoes: '#publicacoes', noticias: `${frame.dataset.base}/public/index.php?categoria=noticias`, eventos: `${frame.dataset.base}/public/index.php?categoria=eventos` };
        doc.querySelector('[data-home-link]').setAttribute('href', destinations[document.querySelector('#inicio-destino').value]);
        requestAnimationFrame(resize);
    };
    const initialize = () => {
        previewObserver?.disconnect();
        if (!frame.contentDocument?.querySelector('.home-cover')) return;
        frame.contentDocument.addEventListener('click', (event) => { if (event.target.closest('a')) event.preventDefault(); });
        previewObserver = new ResizeObserver(resize);
        previewObserver.observe(frame.contentDocument.querySelector('.home-cover'));
        sync();
    };
    frame.addEventListener('load', initialize);
    if (frame.contentDocument?.readyState === 'complete') initialize();
    new ResizeObserver(resize).observe(canvas);
    form.addEventListener('input', () => { sync(); state.textContent = 'Alterações na prévia. Salve para publicar no portal.'; });
    form.addEventListener('change', sync);
    file.addEventListener('change', () => {
        file.setCustomValidity('');
        if (photo) URL.revokeObjectURL(photo);
        photo = null;
        const selected = file.files[0];
        if (selected && (!['image/jpeg', 'image/png', 'image/webp', 'image/gif'].includes(selected.type) || selected.size > 2 * 1024 * 1024)) {
            file.setCustomValidity('Selecione uma imagem JPG, PNG, WebP ou GIF de até 2 MB.');
            file.reportValidity();
        } else if (selected) {
            photo = URL.createObjectURL(selected);
        }
        discard.hidden = !selected;
        sync();
    });
    discard.addEventListener('click', () => {
        if (photo) URL.revokeObjectURL(photo);
        photo = null; file.value = ''; file.setCustomValidity(''); discard.hidden = true; sync();
    });
    document.querySelectorAll('[data-preview-width]').forEach((button) => button.addEventListener('click', () => {
        viewport = Number(button.dataset.previewWidth);
        document.querySelectorAll('[data-preview-width]').forEach((item) => item.setAttribute('aria-pressed', String(item === button)));
        sync();
    }));
    let dirty = false;
    form.addEventListener('input', () => { dirty = true; });
    form.addEventListener('change', () => { dirty = true; });
    form.addEventListener('submit', () => { dirty = false; });
    window.addEventListener('beforeunload', (event) => { if (dirty) { event.preventDefault(); event.returnValue = ''; } });
})();

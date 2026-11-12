'use strict';

document.querySelectorAll('[data-gallery]').forEach((gallery) => {
    const track = gallery.querySelector('[data-gallery-track]');
    const total = track.children.length;
    if (total < 2) return;
    const counter = gallery.querySelector('[data-gallery-count]');
    const thumbs = [...gallery.querySelectorAll('[data-gallery-index]')];
    let current = 0;
    gallery.querySelector('[data-gallery-controls]').hidden = false;
    gallery.querySelector('[data-gallery-thumbs]').hidden = false;
    const go = (index) => {
        index = (index + total) % total;
        track.scrollTo({ left: index * track.clientWidth, behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'instant' : 'smooth' });
    };
    gallery.querySelector('[data-gallery-prev]').addEventListener('click', () => go(current - 1));
    gallery.querySelector('[data-gallery-next]').addEventListener('click', () => go(current + 1));
    thumbs.forEach((button, index) => button.addEventListener('click', () => go(index)));
    gallery.addEventListener('keydown', (event) => {
        if (event.key !== 'ArrowLeft' && event.key !== 'ArrowRight') return;
        event.preventDefault();
        go(current + (event.key === 'ArrowRight' ? 1 : -1));
    });
    track.addEventListener('scroll', () => {
        current = Math.max(0, Math.min(total - 1, Math.round(track.scrollLeft / track.clientWidth)));
        counter.textContent = `${current + 1} de ${total}`;
        thumbs.forEach((button, index) => button.setAttribute('aria-current', String(index === current)));
    }, { passive: true });
    new ResizeObserver(() => track.scrollTo({ left: current * track.clientWidth, behavior: 'instant' })).observe(track);
});

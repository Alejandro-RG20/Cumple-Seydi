/**
 * Visor de fotografías a pantalla completa: teclado (← → Esc), gestos de
 * deslizamiento en móvil, precarga de vecinas, foco atrapado y restaurado.
 */
import { $, $$, lockScroll, trapFocus, reducedMotion } from './utils.js';

export function initGallery() {
    const cards = $$('[data-gallery] .photo-card');
    const box = $('[data-lightbox]');
    if (!cards.length || !box) return;

    const img = $('[data-lightbox-img]', box);
    const caption = $('[data-lightbox-caption]', box);
    const counter = $('[data-lightbox-counter]', box);
    const stage = $('[data-lightbox-stage]', box);
    const items = cards.map((c) => ({
        src: c.dataset.full,
        w: +c.dataset.w || 1200,
        h: +c.dataset.h || 1500,
        caption: c.dataset.caption || '',
        alt: c.querySelector('img')?.alt || '',
    }));
    let index = 0;
    let opener = null;
    let token = 0;

    const preload = (i) => {
        const it = items[(i + items.length) % items.length];
        if (it) { const p = new Image(); p.src = it.src; }
    };

    const show = (i, dir = 0) => {
        index = (i + items.length) % items.length;
        const it = items[index];
        const my = ++token;
        box.classList.add('is-busy');
        const outClass = dir > 0 ? 'is-out-left' : dir < 0 ? 'is-out-right' : 'is-loading';
        img.classList.add(outClass);

        const swap = () => {
            if (my !== token) return;
            const next = new Image();
            next.onload = next.onerror = () => {
                if (my !== token) return;
                img.width = it.w; img.height = it.h;
                img.src = it.src;
                img.alt = it.alt;
                img.classList.remove('is-out-left', 'is-out-right');
                img.classList.add('is-loading');
                requestAnimationFrame(() => requestAnimationFrame(() => img.classList.remove('is-loading')));
                box.classList.remove('is-busy');
            };
            next.src = it.src;
        };
        setTimeout(swap, reducedMotion() || !dir ? 0 : 220);

        caption.textContent = it.caption;
        counter.textContent = `${String(index + 1).padStart(2, '0')} / ${String(items.length).padStart(2, '0')}`;
        preload(index + 1);
        preload(index - 1);
    };

    const open = (i, trigger) => {
        opener = trigger;
        box.hidden = false;
        lockScroll(true);
        show(i);
        $('[data-lightbox-close].lightbox__btn', box)?.focus({ preventScroll: true });
    };
    const close = () => {
        if (box.hidden) return;
        box.hidden = true;
        lockScroll(false);
        token++;
        opener?.focus({ preventScroll: true });
    };

    cards.forEach((card, i) => card.addEventListener('click', () => open(i, card)));
    $$('[data-lightbox-close]', box).forEach((b) => b.addEventListener('click', close));
    $('[data-lightbox-prev]', box)?.addEventListener('click', () => show(index - 1, -1));
    $('[data-lightbox-next]', box)?.addEventListener('click', () => show(index + 1, 1));

    box.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') close();
        else if (e.key === 'ArrowRight') show(index + 1, 1);
        else if (e.key === 'ArrowLeft') show(index - 1, -1);
        else trapFocus(box, e);
    });

    // Gestos: deslizar horizontalmente para cambiar, hacia abajo para cerrar.
    let sx = 0, sy = 0, tracking = false;
    stage.addEventListener('pointerdown', (e) => {
        if (e.pointerType === 'mouse') return;
        tracking = true; sx = e.clientX; sy = e.clientY;
    });
    stage.addEventListener('pointerup', (e) => {
        if (!tracking) return;
        tracking = false;
        const dx = e.clientX - sx, dy = e.clientY - sy;
        if (Math.abs(dx) > 50 && Math.abs(dx) > Math.abs(dy)) show(index + (dx < 0 ? 1 : -1), dx < 0 ? 1 : -1);
        else if (dy > 90 && Math.abs(dy) > Math.abs(dx)) close();
    });
    stage.addEventListener('pointercancel', () => { tracking = false; });
    // Clic fuera de la foto (en el escenario vacío) cierra
    stage.addEventListener('click', (e) => { if (e.target === stage) close(); });
}

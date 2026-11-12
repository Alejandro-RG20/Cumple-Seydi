/**
 * Cursor acompañante (solo escritorio con ratón). No reemplaza el cursor del
 * sistema: lo acompaña, así que no afecta la accesibilidad ni la precisión.
 * Sobre elementos con [data-cursor] crece y muestra una etiqueta.
 */
import { $, finePointer, reducedMotion, lerp } from './utils.js';

export function initCursor() {
    const el = $('.cursor');
    if (!el || !finePointer() || reducedMotion()) return;
    const label = $('.cursor__label', el);

    let x = -100, y = -100, cx = -100, cy = -100;
    let raf = 0;
    let running = false;

    const loop = () => {
        cx = lerp(cx, x, 0.2);
        cy = lerp(cy, y, 0.2);
        el.style.transform = `translate3d(${cx.toFixed(1)}px, ${cy.toFixed(1)}px, 0)`;
        if (Math.abs(cx - x) > 0.1 || Math.abs(cy - y) > 0.1) {
            raf = requestAnimationFrame(loop);
        } else {
            running = false;
        }
    };
    const kick = () => { if (!running) { running = true; raf = requestAnimationFrame(loop); } };

    window.addEventListener('pointermove', (e) => {
        if (e.pointerType !== 'mouse') return;
        x = e.clientX; y = e.clientY;
        el.classList.add('is-active');
        kick();
    }, { passive: true });

    document.addEventListener('pointerover', (e) => {
        const t = e.target.closest?.('[data-cursor], a, button');
        el.classList.toggle('is-hover', !!t);
        label.textContent = t?.dataset.cursor || '';
    });
    document.addEventListener('pointerdown', () => el.classList.add('is-down'));
    document.addEventListener('pointerup', () => el.classList.remove('is-down'));
    document.documentElement.addEventListener('pointerleave', () => el.classList.remove('is-active'));
    window.addEventListener('blur', () => { el.classList.remove('is-active'); cancelAnimationFrame(raf); running = false; });
}

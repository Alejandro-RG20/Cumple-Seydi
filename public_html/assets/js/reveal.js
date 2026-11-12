/**
 * Apariciones al desplazarse, línea de tiempo animada y parallax sutil.
 * Usa IntersectionObserver (sin escuchar scroll para las apariciones).
 */
import { $, $$, onScroll, reducedMotion, clamp, isSmallScreen } from './utils.js';

export function initReveal() {
    const targets = $$('[data-reveal], .section-num, .final');

    if (!('IntersectionObserver' in window) || reducedMotion()) {
        targets.forEach((el) => el.classList.add('is-visible'));
    } else {
        // Los elementos con clip-path: inset(100%) no "intersectan" para el navegador,
        // así que en esos casos se observa un "centinela" (su contenedor).
        const map = new Map();
        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                (map.get(entry.target) || []).forEach((el) => el.classList.add('is-visible'));
                io.unobserve(entry.target);
            });
        }, { rootMargin: '0px 0px -12% 0px', threshold: 0 });
        targets.forEach((el) => {
            const sentinel = el.dataset.reveal === 'mask' ? (el.parentElement || el) : el;
            if (!map.has(sentinel)) map.set(sentinel, []);
            map.get(sentinel).push(el);
            io.observe(sentinel);
        });
    }

    /* ---------- Línea de tiempo: se "dibuja" con el scroll ---------- */
    const timeline = $('[data-timeline]');
    const fill = $('[data-timeline-fill]');
    const chapters = $$('[data-chapter]');
    if (timeline && fill) {
        onScroll(() => {
            const r = timeline.getBoundingClientRect();
            const mid = window.innerHeight * 0.55;
            const p = clamp((mid - r.top) / r.height, 0, 1);
            fill.style.transform = `scaleY(${p})`;
            chapters.forEach((ch) => {
                const cr = ch.getBoundingClientRect();
                ch.classList.toggle('is-active', cr.top < mid && cr.bottom > window.innerHeight * 0.2);
            });
        });
    }

    /* ---------- Parallax sutil (solo transform, sin reflow) ---------- */
    if (reducedMotion()) return;
    const layers = $$('[data-parallax]');
    if (!layers.length) return;
    onScroll(() => {
        const vh = window.innerHeight;
        const factor = isSmallScreen() ? 0.5 : 1;
        layers.forEach((el) => {
            // Se mide el contenedor (no el elemento movido) para evitar retroalimentación
            const r = (el.parentElement || el).getBoundingClientRect();
            if (r.bottom < -100 || r.top > vh + 100) return;
            const speed = parseFloat(el.dataset.parallax) || 0;
            const offset = (r.top + r.height / 2 - vh / 2) * speed * factor;
            el.style.translate = `0 ${offset.toFixed(1)}px`;
        });
    });
}

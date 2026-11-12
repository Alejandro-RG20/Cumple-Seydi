/**
 * Navegación: barra superior dinámica, menú a pantalla completa,
 * indicador de capítulo actual y barra de progreso de lectura.
 */
import { $, $$, onScroll, trapFocus, lockScroll, reducedMotion } from './utils.js';

export function initNav() {
    const topbar = $('[data-topbar]');
    const bar = $('.progress__bar');
    const menu = $('[data-menu]');
    const toggle = $('[data-menu-toggle]');
    const chapterNum = $('[data-chapter-num]');
    const chapterName = $('[data-chapter-name]');
    const sections = $$('[data-section]');
    const links = $$('[data-menu-link]');

    /* ---------- Scroll: progreso, ocultar/mostrar barra, tema bajo la barra ---------- */
    let lastY = 0;
    let current = null;
    onScroll((y) => {
        const max = document.documentElement.scrollHeight - window.innerHeight;
        if (bar) bar.style.transform = `scaleX(${max > 0 ? Math.min(1, y / max) : 0})`;

        if (topbar) {
            topbar.classList.toggle('is-scrolled', y > 40);
            const goingDown = y > lastY + 4;
            const goingUp = y < lastY - 4;
            if (goingDown && y > window.innerHeight * 0.6 && !menu?.classList.contains('is-open')) topbar.classList.add('is-hidden');
            else if (goingUp || y < 80) topbar.classList.remove('is-hidden');
        }
        lastY = y;

        // Sección activa = la que cruza la línea a 35 % de la altura de la ventana
        const probe = window.innerHeight * 0.35;
        let active = sections[0];
        for (const s of sections) {
            if (s.getBoundingClientRect().top <= probe) active = s;
        }
        // ¿Hay una sección clara bajo la barra superior?
        let underBar = null;
        for (const s of sections) {
            const r = s.getBoundingClientRect();
            if (r.top <= 30 && r.bottom > 30) underBar = s;
        }
        topbar?.classList.toggle('is-on-light', !!underBar?.classList.contains('theme-light'));

        if (active && active !== current) {
            current = active;
            if (chapterNum) chapterNum.textContent = active.dataset.section || '';
            if (chapterName) chapterName.textContent = active.dataset.title || '';
            links.forEach((a) => a.setAttribute('aria-current', String(a.getAttribute('href') === '#' + active.id)));
        }
    });

    /* ---------- Menú ---------- */
    if (!menu || !toggle) return;
    let closeTimer;
    const open = () => {
        clearTimeout(closeTimer);
        menu.hidden = false;
        requestAnimationFrame(() => menu.classList.add('is-open'));
        toggle.setAttribute('aria-expanded', 'true');
        toggle.querySelector('.menu-btn__text').textContent = 'Cerrar';
        topbar?.classList.remove('is-hidden', 'is-on-light');
        lockScroll(true);
        setTimeout(() => links[0]?.focus({ preventScroll: true }), 300);
    };
    const close = (returnFocus = true) => {
        menu.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        toggle.querySelector('.menu-btn__text').textContent = 'Menú';
        lockScroll(false);
        closeTimer = setTimeout(() => { menu.hidden = true; }, reducedMotion() ? 0 : 900);
        if (returnFocus) toggle.focus({ preventScroll: true });
    };
    toggle.addEventListener('click', () => (menu.classList.contains('is-open') ? close() : open()));
    links.forEach((a) => a.addEventListener('click', () => close(false)));
    document.addEventListener('keydown', (e) => {
        if (!menu.classList.contains('is-open')) return;
        if (e.key === 'Escape') close();
        else {
            // El botón de cerrar está fuera del <nav>: lo incluimos en el ciclo de foco
            const wrap = { querySelectorAll: (s) => [toggle, ...menu.querySelectorAll(s)] };
            trapFocus(wrap, e);
        }
    });
}

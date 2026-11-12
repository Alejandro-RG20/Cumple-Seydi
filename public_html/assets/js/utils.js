/**
 * Utilidades compartidas.
 */

export const $ = (sel, ctx = document) => ctx.querySelector(sel);
export const $$ = (sel, ctx = document) => Array.from(ctx.querySelectorAll(sel));

const mqReduce = window.matchMedia('(prefers-reduced-motion: reduce)');
export const reducedMotion = () => mqReduce.matches;
export const finePointer = () => window.matchMedia('(hover: hover) and (pointer: fine)').matches;
export const isSmallScreen = () => window.innerWidth < 760;

export const clamp = (v, min, max) => Math.min(max, Math.max(min, v));
export const lerp = (a, b, t) => a + (b - a) * t;
export const wait = (ms) => new Promise((r) => setTimeout(r, ms));

/** Datos inyectados por PHP en <script type="application/json" id="app-data">. */
export function appData() {
    try {
        return JSON.parse(document.getElementById('app-data')?.textContent || '{}');
    } catch (err) {
        console.error('[app] Datos de configuración inválidos', err);
        return {};
    }
}

/** Bus de eventos mínimo entre módulos. */
const bus = new EventTarget();
export const emit = (name, detail) => bus.dispatchEvent(new CustomEvent(name, { detail }));
export const on = (name, fn) => bus.addEventListener(name, (e) => fn(e.detail));

let toastTimer;
/** Aviso breve en la parte superior. */
export function toast(message, ms = 2600) {
    const el = $('[data-toast]');
    if (!el) return;
    el.textContent = message;
    el.classList.add('is-visible');
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove('is-visible'), ms);
}

/** Bucle rAF compartido: un solo requestAnimationFrame para todo lo que depende del scroll. */
const scrollFns = new Set();
let ticking = false;
function runScroll() {
    ticking = false;
    const y = window.scrollY;
    scrollFns.forEach((fn) => fn(y));
}
export function onScroll(fn) {
    scrollFns.add(fn);
    if (scrollFns.size === 1) {
        const schedule = () => {
            if (!ticking) { ticking = true; requestAnimationFrame(runScroll); }
        };
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule, { passive: true });
    }
    requestAnimationFrame(() => fn(window.scrollY));
}

/** Mantiene el foco dentro de un diálogo (accesibilidad). */
export function trapFocus(container, event) {
    if (event.key !== 'Tab') return;
    const focusables = $$('a[href], button:not([disabled]), input:not([type="hidden"]), textarea, [tabindex]:not([tabindex="-1"])', container)
        .filter((el) => el.getClientRects().length > 0);
    if (!focusables.length) return;
    const first = focusables[0];
    const last = focusables[focusables.length - 1];
    if (event.shiftKey && document.activeElement === first) { last.focus(); event.preventDefault(); }
    else if (!event.shiftKey && document.activeElement === last) { first.focus(); event.preventDefault(); }
}

/** Bloqueo de scroll del documento para modales (con contador para anidar). */
let lockCount = 0;
export function lockScroll(lock) {
    lockCount = Math.max(0, lockCount + (lock ? 1 : -1));
    document.documentElement.style.overflow = lockCount ? 'hidden' : '';
}

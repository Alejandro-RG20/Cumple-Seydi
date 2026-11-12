/**
 * Detalles desbloqueables:
 *  - Estrellas escondidas: al encontrarlas todas se abre un mensaje secreto.
 *    El progreso se recuerda en este navegador (localStorage, si está disponible).
 *  - Tarjetas que se giran (momentos tipo "flip").
 *  - Botón "Volver a empezar" (repite la introducción).
 */
import { $, $$, emit, toast, lockScroll, trapFocus } from './utils.js';
import { confettiBurst } from './particles.js';

const KEY = 'seydi-stars';

const store = {
    get() {
        try { return new Set(JSON.parse(localStorage.getItem(KEY) || '[]')); } catch { return new Set(); }
    },
    set(set) {
        try { localStorage.setItem(KEY, JSON.stringify([...set])); } catch { /* modo privado */ }
    },
};

export function initSecrets() {
    const stars = $$('[data-star]');
    const counter = $('[data-star-count]');
    const modal = $('[data-secret]');
    const total = stars.length;
    const found = store.get();
    let lastFocus = null;

    const update = () => { if (counter) counter.textContent = String(found.size); };
    stars.forEach((s) => { if (found.has(s.dataset.star)) s.classList.add('is-found'); });
    update();

    const openSecret = () => {
        if (!modal) return;
        lastFocus = document.activeElement;
        modal.hidden = false;
        lockScroll(true);
        $('.btn', modal)?.focus({ preventScroll: true });
        // El confeti va entre el fondo y la tarjeta para no tapar el texto
        const c = document.createElement('canvas');
        c.className = 'secret__confetti';
        c.setAttribute('aria-hidden', 'true');
        modal.insertBefore(c, modal.querySelector('.secret__card'));
        confettiBurst(c);
        setTimeout(() => c.remove(), 4600);
    };
    const closeSecret = () => {
        if (!modal || modal.hidden) return;
        modal.hidden = true;
        lockScroll(false);
        lastFocus?.focus?.({ preventScroll: true });
    };
    $$('[data-secret-close]', modal || document).forEach((b) => b.addEventListener('click', closeSecret));
    modal?.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeSecret(); else trapFocus(modal, e); });

    stars.forEach((s) => s.addEventListener('click', () => {
        if (found.has(s.dataset.star)) return;
        found.add(s.dataset.star);
        store.set(found);
        s.classList.add('is-found');
        update();
        if (found.size >= total) setTimeout(openSecret, 500);
        else toast(`✦ ${found.size} de ${total} estrellas encontradas`);
    }));

    // Pie de página: permite volver a leer el secreto una vez desbloqueado
    $('[data-star-counter]')?.addEventListener('click', () => { if (found.size >= total) openSecret(); });

    /* ---------- Tarjetas que se giran ---------- */
    $$('[data-flip]').forEach((card) => card.addEventListener('click', () => {
        card.setAttribute('aria-pressed', String(card.getAttribute('aria-pressed') !== 'true'));
    }));

    /* ---------- Repetir la introducción ---------- */
    $$('[data-replay]').forEach((b) => b.addEventListener('click', () => emit('intro:replay')));
}

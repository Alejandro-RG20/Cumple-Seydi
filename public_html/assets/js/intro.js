/**
 * Introducción cinematográfica: secuencia de entrada, inicio de la música
 * y transición "iris" hacia la portada. También permite repetirla.
 */
import { $, emit, on, reducedMotion, trapFocus, wait } from './utils.js';

export function initIntro() {
    const intro = $('[data-intro]');
    const root = document.documentElement;
    if (!intro) {
        root.classList.remove('is-intro');
        root.classList.add('is-ready');
        return;
    }
    const startBtn = $('[data-intro-start]', intro);
    // El iris necesita @property (para animar el radio) y máscaras CSS; si no, se usa un fundido.
    const supportsIris = !!(window.CSS && CSS.registerProperty)
        && (CSS.supports('mask-image', 'none') || CSS.supports('-webkit-mask-image', 'none'));

    const play = () => {
        intro.classList.remove('is-leaving', 'is-gone', 'no-iris');
        // Reinicia las animaciones CSS
        intro.classList.remove('is-playing');
        void intro.offsetWidth;
        intro.classList.add('is-playing');
        emit('intro:show');
    };

    // Espera a las fuentes (máx. 1.2 s) para que la tipografía aparezca ya definitiva.
    const fontsReady = document.fonts ? Promise.race([document.fonts.ready, wait(1200)]) : wait(200);
    fontsReady.then(play);

    setTimeout(() => startBtn?.focus({ preventScroll: true }), reducedMotion() ? 100 : 4200);

    intro.addEventListener('keydown', (e) => trapFocus(intro, e));

    let leaving = false;
    startBtn?.addEventListener('click', async () => {
        if (leaving) return;
        leaving = true;
        emit('experience:start');                 // la música arranca con este gesto del usuario
        window.scrollTo({ top: 0, behavior: 'instant' in window ? 'instant' : 'auto' });
        if (!supportsIris) intro.classList.add('no-iris');
        intro.classList.add('is-leaving');
        await wait(reducedMotion() ? 50 : 650);
        root.classList.add('is-ready');          // la portada empieza a aparecer bajo el iris
        root.classList.remove('is-intro');
        await wait(reducedMotion() ? 50 : 1400);
        intro.classList.add('is-gone');
        intro.classList.remove('is-playing');
        leaving = false;
        emit('intro:done');
        document.getElementById('hero-title')?.setAttribute('tabindex', '-1');
        document.getElementById('hero-title')?.focus({ preventScroll: true });
    });

    on('intro:replay', () => {
        root.classList.add('is-intro');
        root.classList.remove('is-ready');
        window.scrollTo(0, 0);
        play();
        setTimeout(() => startBtn?.focus({ preventScroll: true }), reducedMotion() ? 100 : 4200);
    });
}

/**
 * Carta: el sello se rompe, la solapa se abre, el papel sale del sobre y la
 * carta se despliega. El saludo se escribe a máquina y el cuerpo aparece
 * palabra a palabra; la firma se "dibuja". Se puede mostrar todo de golpe.
 */
import { $, $$, wait, reducedMotion } from './utils.js';

/** Envuelve cada palabra de los nodos de texto en <span class="w">. */
function splitWords(root) {
    const walker = document.createTreeWalker(root, NodeFilter.SHOW_TEXT);
    const nodes = [];
    while (walker.nextNode()) nodes.push(walker.currentNode);
    const words = [];
    nodes.forEach((node) => {
        const frag = document.createDocumentFragment();
        node.textContent.split(/(\s+)/).forEach((part) => {
            if (!part) return;
            if (/^\s+$/.test(part)) { frag.appendChild(document.createTextNode(part)); return; }
            const span = document.createElement('span');
            span.className = 'w';
            span.textContent = part;
            frag.appendChild(span);
            words.push(span);
        });
        node.parentNode.replaceChild(frag, node);
    });
    return words;
}

export function initLetter() {
    const stage = $('[data-letter-stage]');
    const envelope = $('[data-envelope]');
    const openBtn = $('[data-letter-open]');
    const letter = $('[data-letter]');
    if (!stage || !envelope || !openBtn || !letter) return;

    const greeting = $('[data-letter-greeting]', letter);
    const body = $('[data-letter-body]', letter);
    const tails = $$('[data-letter-tail]', letter);
    const skipBtn = $('[data-letter-skip]', letter);
    const closeBtn = $('[data-letter-close]', letter);
    const greetingText = greeting.textContent;
    const words = splitWords(body);

    let run = 0; // identifica la animación en curso (para cancelarla)

    const revealAll = () => {
        run++;
        greeting.textContent = greetingText;
        greeting.classList.remove('typing-caret');
        words.forEach((w) => w.classList.add('on'));
        tails.forEach((t) => t.classList.add('on'));
        skipBtn.hidden = true;
    };

    const animate = async () => {
        const my = ++run;
        const fast = reducedMotion();
        if (fast) return revealAll();
        skipBtn.hidden = false;
        // Saludo a máquina
        greeting.textContent = '';
        greeting.classList.add('typing-caret');
        await wait(900);
        for (const ch of greetingText) {
            if (my !== run) return;
            greeting.textContent += ch;
            await wait(ch === ',' ? 260 : 70 + Math.random() * 60);
        }
        greeting.classList.remove('typing-caret');
        await wait(350);
        // Cuerpo palabra a palabra (ritmo natural: pausas en signos)
        for (const w of words) {
            if (my !== run) return;
            w.classList.add('on');
            const end = w.textContent.slice(-1);
            await wait(/[.!?…]/.test(end) ? 380 : /[,;:]/.test(end) ? 180 : 55);
        }
        await wait(300);
        if (my !== run) return;
        tails.forEach((t) => t.classList.add('on'));
        skipBtn.hidden = true;
    };

    const open = async () => {
        if (envelope.classList.contains('is-open')) return;
        openBtn.setAttribute('aria-expanded', 'true');
        envelope.classList.add('is-open');
        await wait(reducedMotion() ? 0 : 1900);
        stage.classList.add('is-done');
        await wait(reducedMotion() ? 0 : 800);
        stage.hidden = true;
        letter.hidden = false;
        letter.classList.add('is-unfolding');
        letter.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'start' });
        letter.setAttribute('tabindex', '-1');
        letter.focus({ preventScroll: true });
        await wait(reducedMotion() ? 0 : 900);
        animate();
    };

    const close = () => {
        run++;
        letter.hidden = true;
        letter.classList.remove('is-unfolding');
        words.forEach((w) => w.classList.remove('on'));
        tails.forEach((t) => t.classList.remove('on'));
        stage.hidden = false;
        stage.classList.remove('is-done');
        envelope.classList.remove('is-open');
        openBtn.setAttribute('aria-expanded', 'false');
        stage.scrollIntoView({ behavior: reducedMotion() ? 'auto' : 'smooth', block: 'center' });
        openBtn.focus({ preventScroll: true });
    };

    openBtn.addEventListener('click', open);
    skipBtn.addEventListener('click', revealAll);
    closeBtn.addEventListener('click', close);
}

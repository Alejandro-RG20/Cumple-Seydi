/**
 * Reloj especial / cuenta regresiva.
 * - Antes de la fecha: días, horas, minutos y segundos (nunca negativos).
 * - Al llegar: se transforma solo en "¡Hoy es tu día!" con confeti.
 * - Pasado el periodo de celebración: mensaje permanente.
 * Se recalcula con Date.now() en cada tick, así no acumula desfase aunque
 * la pestaña esté en segundo plano.
 */
import { $, $$, reducedMotion } from './utils.js';
import { confettiBurst } from './particles.js';

const pad = (n) => String(n).padStart(2, '0');

export function initCountdown(data) {
    const root = $('[data-countdown]');
    if (!root) return;

    const target = Date.parse(data.birthday);
    if (Number.isNaN(target)) {
        console.warn('[reloj] Fecha de cumpleaños inválida:', data.birthday);
        return;
    }
    const celebrationMs = (data.celebrationHours || 24) * 3600 * 1000;
    const texts = data.countdown || {};

    const ticksG = $('[data-ticks]', root);
    const arc = $('[data-arc]', root);
    const big = $('[data-dial-big]', root);
    const small = $('[data-dial-small]', root);
    const units = $('[data-units]', root);
    const title = $('[data-clock-title]', root);
    const text = $('[data-clock-text]', root);
    const els = Object.fromEntries($$('[data-unit]', root).map((el) => [el.dataset.unit, el]));
    const smallDate = small?.textContent || '';

    // 60 marcas del dial (las de cada 5 son mayores)
    const NS = 'http://www.w3.org/2000/svg';
    const ticks = [];
    for (let i = 0; i < 60; i++) {
        const a = (i / 60) * Math.PI * 2 - Math.PI / 2;
        const major = i % 5 === 0;
        const r1 = major ? 138 : 141, r2 = 147;
        const line = document.createElementNS(NS, 'line');
        line.setAttribute('x1', (160 + Math.cos(a) * r1).toFixed(2));
        line.setAttribute('y1', (160 + Math.sin(a) * r1).toFixed(2));
        line.setAttribute('x2', (160 + Math.cos(a) * r2).toFixed(2));
        line.setAttribute('y2', (160 + Math.sin(a) * r2).toFixed(2));
        if (major) line.classList.add('major');
        ticksG?.appendChild(line);
        ticks.push(line);
    }

    let state = root.dataset.state;
    let celebrated = false;
    const prev = {};

    const setState = (next) => {
        if (next === state && root.dataset.ready) return;
        root.dataset.ready = '1';
        const changed = next !== state;
        state = next;
        root.dataset.state = next;
        if (next === 'before') {
            title.textContent = texts.title_before || '';
            text.textContent = texts.text_before || '';
            units.hidden = false;
        } else {
            title.textContent = next === 'today' ? (texts.title_after || '') : (texts.title_past || '');
            text.textContent = next === 'today' ? (texts.text_after || '') : (texts.text_past || '');
            units.hidden = true;
            big.textContent = '✦';
            small.textContent = smallDate;
            arc.style.strokeDasharray = '60 0';
            ticks.forEach((t) => t.classList.add('on'));
            if (changed && next === 'today') celebrate();
        }
    };

    const celebrate = () => {
        if (celebrated || reducedMotion()) return;
        celebrated = true;
        confettiBurst($('[data-confetti]', root));
    };

    const tick = () => {
        const now = Date.now();
        const diff = target - now;
        if (diff <= 0) {
            setState(-diff < celebrationMs ? 'today' : 'past');
            return;
        }
        setState('before');
        const s = Math.floor(diff / 1000);
        const parts = {
            days: Math.floor(s / 86400),
            hours: Math.floor((s % 86400) / 3600),
            minutes: Math.floor((s % 3600) / 60),
            seconds: s % 60,
        };
        for (const k in parts) {
            const v = k === 'days' ? String(parts[k]) : pad(parts[k]);
            if (els[k] && prev[k] !== v) {
                els[k].textContent = k === 'days' ? pad(parts[k]) : v;
                if (prev[k] !== undefined && !reducedMotion()) {
                    els[k].classList.remove('tick');
                    void els[k].offsetWidth;
                    els[k].classList.add('tick');
                }
                prev[k] = v;
            }
        }
        big.textContent = String(parts.days);
        small.textContent = parts.days === 1 ? 'día' : 'días';
        // El arco y las marcas representan los segundos del minuto en curso
        const sec = 60 - parts.seconds;
        arc.style.strokeDasharray = `${sec % 60} 60`;
        ticks.forEach((t, i) => t.classList.toggle('on', i < sec % 60));
    };

    tick();
    // Al ver la sección en estado "hoy", celebramos una vez.
    if (state === 'today' && 'IntersectionObserver' in window) {
        const io = new IntersectionObserver(([e]) => {
            if (e.isIntersecting) { celebrate(); io.disconnect(); }
        }, { threshold: 0.4 });
        io.observe(root);
    }
    // Sincronizado con el cambio de segundo
    setTimeout(() => { tick(); setInterval(tick, 1000); }, 1000 - (Date.now() % 1000));
}

/**
 * Partículas ligeras en <canvas>:
 *  - "dust":   polvo dorado flotando (introducción)
 *  - "embers": brasas doradas que ascienden (final)
 *  - confettiBurst(): confeti elegante para la celebración / secretos
 * Solo se animan mientras el lienzo es visible y la pestaña está activa.
 * Con "reducir movimiento" no se ejecutan.
 */
import { $$, reducedMotion, isSmallScreen } from './utils.js';

const COLORS = ['236,214,168', '217,190,143', '200,164,106', '201,160,160'];
const rand = (a, b) => a + Math.random() * (b - a);

function fitCanvas(canvas) {
    const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    const w = canvas.clientWidth, h = canvas.clientHeight;
    canvas.width = Math.max(1, Math.round(w * dpr));
    canvas.height = Math.max(1, Math.round(h * dpr));
    const ctx = canvas.getContext('2d');
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    return { ctx, w, h };
}

function ambient(canvas, kind) {
    let { ctx, w, h } = fitCanvas(canvas);
    const count = Math.round((kind === 'dust' ? 70 : 46) * (isSmallScreen() ? 0.5 : 1));
    const make = (initial) => ({
        x: rand(0, w),
        y: initial ? rand(0, h) : (kind === 'embers' ? h + 10 : rand(0, h)),
        r: rand(0.4, kind === 'dust' ? 1.8 : 2.2),
        vx: rand(-0.12, 0.12),
        vy: kind === 'embers' ? rand(-0.55, -0.15) : rand(-0.12, 0.08),
        a: rand(0.15, 0.75),
        tw: rand(0, Math.PI * 2),
        c: COLORS[(Math.random() * COLORS.length) | 0],
    });
    const ps = Array.from({ length: count }, () => make(true));
    const glow = !isSmallScreen(); // el resplandor (shadowBlur) es costoso en móviles
    let raf = 0, visible = false;

    const frame = () => {
        ctx.clearRect(0, 0, w, h);
        for (const p of ps) {
            p.x += p.vx; p.y += p.vy; p.tw += 0.02;
            if (p.y < -10 || p.x < -10 || p.x > w + 10 || (kind === 'dust' && p.y > h + 10)) Object.assign(p, make(false));
            const alpha = p.a * (0.6 + 0.4 * Math.sin(p.tw));
            ctx.beginPath();
            ctx.fillStyle = `rgba(${p.c},${alpha.toFixed(3)})`;
            if (glow) { ctx.shadowColor = ctx.fillStyle; ctx.shadowBlur = p.r * 4; }
            ctx.arc(p.x, p.y, p.r, 0, Math.PI * 2);
            ctx.fill();
        }
        raf = requestAnimationFrame(frame);
    };
    const start = () => { if (!raf && visible && !document.hidden) raf = requestAnimationFrame(frame); };
    const stop = () => { cancelAnimationFrame(raf); raf = 0; };

    new IntersectionObserver(([e]) => { visible = e.isIntersecting; visible ? start() : stop(); }).observe(canvas);
    document.addEventListener('visibilitychange', () => (document.hidden ? stop() : start()));
    let rt;
    window.addEventListener('resize', () => {
        clearTimeout(rt);
        rt = setTimeout(() => { ({ ctx, w, h } = fitCanvas(canvas)); }, 200);
    }, { passive: true });
}

export function initAmbientParticles() {
    if (reducedMotion() || !('IntersectionObserver' in window)) return;
    $$('canvas[data-particles]').forEach((c) => ambient(c, c.dataset.particles));
}

/** Ráfaga única de confeti dorado (cintas finas y puntos), ~4 s. */
export function confettiBurst(canvas) {
    if (!canvas || reducedMotion()) return;
    const { ctx, w, h } = fitCanvas(canvas);
    const n = isSmallScreen() ? 90 : 160;
    const ps = Array.from({ length: n }, () => ({
        x: w / 2 + rand(-w * 0.15, w * 0.15),
        y: h * 0.55,
        vx: rand(-7, 7),
        vy: rand(-14, -5),
        g: rand(0.18, 0.3),
        rot: rand(0, Math.PI * 2),
        vr: rand(-0.2, 0.2),
        sw: rand(3, 7), sh: rand(6, 14),
        c: COLORS[(Math.random() * COLORS.length) | 0],
        shape: Math.random() < 0.3 ? 'dot' : 'ribbon',
        life: 1,
    }));
    const t0 = performance.now();
    const frame = (t) => {
        const el = (t - t0) / 1000;
        ctx.clearRect(0, 0, w, h);
        for (const p of ps) {
            p.vx *= 0.985; p.vy += p.g; p.vy *= 0.985;
            p.x += p.vx; p.y += p.vy; p.rot += p.vr;
            p.life = Math.max(0, 1 - el / 4.2);
            ctx.save();
            ctx.translate(p.x, p.y);
            ctx.rotate(p.rot);
            ctx.globalAlpha = p.life;
            ctx.fillStyle = `rgb(${p.c})`;
            if (p.shape === 'dot') { ctx.beginPath(); ctx.arc(0, 0, p.sw / 2, 0, Math.PI * 2); ctx.fill(); }
            else ctx.fillRect(-p.sw / 2, -p.sh / 2, p.sw, p.sh * Math.abs(Math.cos(p.rot * 2)) + 1);
            ctx.restore();
        }
        if (el < 4.3) requestAnimationFrame(frame);
        else ctx.clearRect(0, 0, w, h);
    };
    requestAnimationFrame(frame);
}

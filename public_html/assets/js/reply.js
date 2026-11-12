/**
 * Formulario de respuesta privada. Envía con fetch (JSON) y, si JavaScript
 * falla, el formulario funciona igual con un envío normal (PHP redirige).
 */
import { $ } from './utils.js';

export function initReply(cfg) {
    const form = $('[data-reply]');
    if (!form) return;
    const status = $('[data-reply-status]', form);
    const textarea = $('textarea', form);
    const button = $('button[type="submit"]', form);
    const thanks = $('[data-reply-thanks]', form)?.content?.textContent.trim() || 'Recibido.';

    const setStatus = (msg, kind = '') => {
        status.textContent = msg;
        status.className = 'reply__status' + (kind ? ` is-${kind}` : '');
    };

    // Resultado de un envío sin JavaScript (?respuesta=ok|error)
    const qs = new URLSearchParams(location.search).get('respuesta');
    if (qs === 'ok') setStatus(thanks, 'ok');
    else if (qs === 'error') setStatus('No se pudo enviar. Inténtalo de nuevo.', 'error');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
        const text = textarea.value.trim();
        if (text.length < 2) {
            setStatus('Escribe al menos unas palabras.', 'error');
            textarea.focus();
            return;
        }
        button.disabled = true;
        setStatus('Enviando…');
        try {
            const res = await fetch(cfg.endpoint || form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { Accept: 'application/json' },
                credentials: 'same-origin',
            });
            const json = await res.json().catch(() => ({}));
            if (res.ok && json.ok) {
                setStatus(json.message || 'Recibido.', 'ok');
                form.classList.add('is-sent');
                textarea.value = '';
            } else {
                setStatus(json.message || 'No se pudo enviar. Inténtalo de nuevo.', 'error');
            }
        } catch (err) {
            console.warn('[respuesta]', err);
            setStatus('Sin conexión. Inténtalo de nuevo en un momento.', 'error');
        } finally {
            button.disabled = false;
        }
    });
}

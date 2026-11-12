/**
 * Punto de entrada. Cada módulo es independiente: un error en uno
 * no detiene a los demás.
 */
import { appData } from './utils.js';
import { initIntro } from './intro.js';
import { initNav } from './nav.js';
import { initReveal } from './reveal.js';
import { initCursor } from './cursor.js';
import { initGallery } from './gallery.js';
import { initLetter } from './letter.js';
import { initCountdown } from './countdown.js';
import { initMusic } from './music.js';
import { initSecrets } from './secrets.js';
import { initReply } from './reply.js';
import { initAmbientParticles } from './particles.js';

const data = appData();

const modules = [
    ['música', () => initMusic(data.music || {})],
    ['intro', () => initIntro()],
    ['navegación', () => initNav()],
    ['apariciones', () => initReveal()],
    ['cursor', () => initCursor()],
    ['galería', () => initGallery()],
    ['carta', () => initLetter()],
    ['reloj', () => initCountdown(data)],
    ['sorpresas', () => initSecrets()],
    ['respuesta', () => initReply(data.reply || {})],
    ['partículas', () => initAmbientParticles()],
];

for (const [name, init] of modules) {
    try {
        init();
    } catch (err) {
        console.error(`[app] Error al iniciar el módulo "${name}":`, err);
    }
}

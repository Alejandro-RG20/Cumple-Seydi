/**
 * Reproductor musical "tocadiscos".
 *
 * Modo local  (si existe /assets/audio/<archivo>): <audio> con reproducir/pausar,
 *             barra de progreso, volumen y repetición.
 * Modo Spotify (por defecto): reproductor oficial integrado mediante la
 *             Spotify iFrame API, controlado desde el tocadiscos de la página.
 *
 * Nunca suena sin interacción: la música empieza cuando ella pulsa
 * "Comenzar" en la introducción o toca el disco.
 */
import { $, $$, on, toast } from './utils.js';

const SPOTIFY_API = 'https://open.spotify.com/embed/iframe-api/v1';
const fmt = (s) => {
    if (!Number.isFinite(s) || s < 0) s = 0;
    const m = Math.floor(s / 60);
    return `${m}:${String(Math.floor(s % 60)).padStart(2, '0')}`;
};

export function initMusic(cfg) {
    const player = $('[data-player]');
    if (!player) return;

    const panel = $('[data-player-panel]', player);
    const expandBtn = $('[data-player-expand]', player);
    const toggles = $$('[data-player-toggle]', player);
    const status = $('[data-player-status]', player);
    const note = $('[data-player-note]', player);
    const seek = $('[data-player-seek]', player);
    const time = $('[data-player-time]', player);
    const duration = $('[data-player-duration]', player);
    const volume = $('[data-player-volume]', player);
    const volumeWrap = $('[data-player-volume-wrap]', player);
    const slot = $('[data-spotify-slot]', player);
    const audio = $('[data-player-audio]', player);

    const mode = audio ? 'local' : (cfg.spotify ? 'spotify' : 'none');
    player.dataset.mode = mode;
    if (mode === 'none') { player.hidden = true; return; }
    panel.inert = true;

    /* ---------- Estado visual común ---------- */
    let playing = false;
    const render = (isPlaying, label) => {
        playing = isPlaying;
        player.classList.toggle('is-playing', isPlaying);
        toggles.forEach((b) => {
            b.setAttribute('aria-pressed', String(isPlaying));
            b.setAttribute('aria-label', isPlaying ? 'Pausar música' : 'Reproducir música');
        });
        status.textContent = label || (isPlaying ? 'Sonando' : 'Música en pausa');
    };
    const setFill = (input) => {
        const p = ((+input.value - +input.min) / (+input.max - +input.min)) * 100;
        input.style.setProperty('--fill', p + '%');
    };

    /* ---------- Panel ---------- */
    // Se pliega con una clase (no con "hidden") para que el iframe de Spotify
    // siga cargado y reproduciendo aunque el panel no se vea.
    panel.hidden = false;
    panel.classList.add('is-collapsed');
    const isOpen = () => !panel.classList.contains('is-collapsed');
    const setPanel = (open) => {
        panel.classList.toggle('is-collapsed', !open);
        panel.setAttribute('aria-hidden', String(!open));
        panel.inert = !open;
        expandBtn.setAttribute('aria-expanded', String(open));
        expandBtn.setAttribute('aria-label', open ? 'Ocultar controles de música' : 'Mostrar controles de música');
        if (open && mode === 'spotify') ensureSpotify();
    };
    expandBtn.addEventListener('click', () => setPanel(!isOpen()));
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && isOpen()) setPanel(false); });
    document.addEventListener('click', (e) => { if (isOpen() && !player.contains(e.target)) setPanel(false); });

    /* =====================================================================
       MODO LOCAL
       ===================================================================== */
    if (mode === 'local') {
        audio.loop = true;
        const startVol = Math.max(0, Math.min(1, cfg.volume ?? 0.6));
        audio.volume = startVol;
        volume.value = String(Math.round(startVol * 100));
        setFill(volume);
        // iOS ignora el volumen por software: ocultamos el control si no se aplica
        if (Math.abs(audio.volume - startVol) > 0.01) volumeWrap.hidden = true;

        const play = async () => {
            try {
                if (audio.preload === 'none') audio.preload = 'auto';
                await audio.play();
            } catch (err) {
                render(false, 'Toca para escuchar');
                if (err && err.name !== 'AbortError') console.warn('[música]', err);
            }
        };
        toggles.forEach((b) => b.addEventListener('click', () => (audio.paused ? play() : audio.pause())));
        audio.addEventListener('play', () => render(true));
        audio.addEventListener('pause', () => render(false));
        audio.addEventListener('waiting', () => { status.textContent = 'Cargando…'; });
        audio.addEventListener('playing', () => render(true));
        audio.addEventListener('error', () => {
            render(false, 'Audio no disponible');
            note.textContent = 'No se pudo cargar el archivo de audio. Revisa el nombre en data/content.php.';
        });
        audio.addEventListener('loadedmetadata', () => { duration.textContent = fmt(audio.duration); });
        audio.addEventListener('timeupdate', () => {
            if (!audio.duration) return;
            if (!seeking) { seek.value = String(Math.round((audio.currentTime / audio.duration) * 1000)); setFill(seek); }
            time.textContent = fmt(audio.currentTime);
        });
        let seeking = false;
        seek.addEventListener('input', () => { seeking = true; setFill(seek); time.textContent = fmt((+seek.value / 1000) * (audio.duration || 0)); });
        seek.addEventListener('change', () => { if (audio.duration) audio.currentTime = (+seek.value / 1000) * audio.duration; seeking = false; });
        volume.addEventListener('input', () => { audio.volume = +volume.value / 100; setFill(volume); });

        on('experience:start', play);
        return;
    }

    /* =====================================================================
       MODO SPOTIFY (iFrame API oficial)
       ===================================================================== */
    let controller = null;
    let apiPromise = null;
    let pendingPlay = false;
    let everPlayed = false;

    function ensureSpotify() {
        if (apiPromise) return apiPromise;
        status.textContent = 'Preparando música…';
        apiPromise = new Promise((resolve, reject) => {
            const timeout = setTimeout(() => reject(new Error('timeout')), 15000);
            window.onSpotifyIframeApiReady = (IFrameAPI) => {
                const host = document.createElement('div');
                slot.appendChild(host);
                IFrameAPI.createController(host, {
                    uri: `spotify:track:${cfg.spotify}`,
                    width: '100%',
                    height: 80,
                }, (ctrl) => {
                    clearTimeout(timeout);
                    controller = ctrl;
                    ctrl.addListener('ready', () => {
                        if (!everPlayed) render(false, 'Toca para escuchar');
                        if (pendingPlay) { pendingPlay = false; startSpotify(); }
                    });
                    ctrl.addListener('playback_update', (e) => {
                        const d = e?.data || {};
                        const isPlaying = !d.isPaused && !d.isBuffering;
                        if (isPlaying) everPlayed = true;
                        render(!d.isPaused, d.isBuffering ? 'Cargando…' : undefined);
                    });
                    resolve(ctrl);
                });
            };
            const s = document.createElement('script');
            s.src = SPOTIFY_API;
            s.async = true;
            s.onerror = () => { clearTimeout(timeout); reject(new Error('script')); };
            document.head.appendChild(s);
        }).catch((err) => {
            console.warn('[música] Spotify no disponible:', err.message);
            render(false, 'Música no disponible');
            note.textContent = 'No se pudo conectar con Spotify. Revisa tu conexión o añade un archivo de audio local.';
            apiPromise = null;
            throw err;
        });
        return apiPromise;
    }

    function startSpotify() {
        if (!controller) { pendingPlay = true; return; }
        if (everPlayed) controller.togglePlay();
        else controller.play();
        // Algunos navegadores móviles exigen tocar el propio reproductor de Spotify
        setTimeout(() => {
            if (!playing) {
                setPanel(true);
                note.textContent = 'Si no empieza sola, toca ▶ en el reproductor de Spotify.';
            }
        }, 2500);
    }

    toggles.forEach((b) => b.addEventListener('click', () => {
        if (playing && controller) { controller.togglePlay(); return; }
        ensureSpotify().then(startSpotify, () => {});
    }));

    // Precarga la API mientras se ve la introducción, para que esté lista al pulsar "Comenzar".
    on('intro:show', () => { ensureSpotify().catch(() => {}); });
    on('experience:start', () => {
        ensureSpotify().then(startSpotify, () => toast('La música no está disponible ahora mismo'));
    });
}

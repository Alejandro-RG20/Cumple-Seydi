<?php
/**
 * Pie, reproductor de música, visor de fotos, modal secreto y avisos.
 */

defined('SEYDI_APP') || exit;

$music = local_music();
?>
<footer class="site-footer">
    <p>Hecho a mano para <?= e(c('her_name')) ?> · <?= e(birthday_datetime()->format('Y')) ?></p>
    <button class="site-footer__stars" type="button" data-star-counter>
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l1.9 7.1L21 12l-7.1 1.9L12 21l-1.9-7.1L3 12l7.1-1.9z"/></svg>
        <span><span data-star-count>0</span>/3 estrellas escondidas encontradas</span>
    </button>
</footer>

<!-- ===================== Reproductor de música ===================== -->
<aside class="player" data-player data-mode="<?= $music ? 'local' : 'spotify' ?>" aria-label="Reproductor de música">
    <div class="player__panel" id="player-panel" data-player-panel hidden>
        <div class="player__head">
            <div class="player__art" aria-hidden="true"><span class="player__disc"></span></div>
            <div class="player__info">
                <p class="player__title"><?= e(c('music.title')) ?></p>
                <p class="player__artist"><?= e(c('music.artist')) ?></p>
            </div>
        </div>
        <div class="player__progress" data-player-progress-wrap>
            <input type="range" min="0" max="1000" value="0" step="1" aria-label="Posición de la canción" data-player-seek>
            <div class="player__times"><span data-player-time>0:00</span><span data-player-duration>0:00</span></div>
        </div>
        <div class="player__controls">
            <button class="player__big" type="button" data-player-toggle aria-label="Reproducir" aria-pressed="false">
                <svg class="i-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                <svg class="i-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>
            </button>
            <label class="player__volume" data-player-volume-wrap>
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9v6h4l5 4V5L8 9H4zM16.5 8.5a5 5 0 0 1 0 7"/></svg>
                <span class="sr-only">Volumen</span>
                <input type="range" min="0" max="100" value="60" data-player-volume>
            </label>
        </div>
        <div class="player__spotify" data-spotify-slot></div>
        <p class="player__note" data-player-note></p>
        <?php if ($music): ?><audio preload="none" data-player-audio src="<?= e($music) ?>"></audio><?php endif; ?>
    </div>

    <div class="player__pill">
        <button class="player__vinyl" type="button" data-player-toggle aria-label="Reproducir música" aria-pressed="false" data-cursor="Música">
            <span class="player__disc" aria-hidden="true"></span>
        </button>
        <span class="player__eq" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
        <span class="player__status" data-player-status>Música en pausa</span>
        <button class="player__expand" type="button" data-player-expand aria-expanded="false" aria-controls="player-panel" aria-label="Mostrar controles de música">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 15l6-6 6 6"/></svg>
        </button>
    </div>
</aside>

<!-- ===================== Visor de fotos ===================== -->
<div class="lightbox" data-lightbox role="dialog" aria-modal="true" aria-label="Visor de fotografías" hidden>
    <div class="lightbox__backdrop" data-lightbox-close></div>
    <figure class="lightbox__figure">
        <div class="lightbox__stage" data-lightbox-stage>
            <img class="lightbox__img" data-lightbox-img alt="" width="1200" height="1500">
            <span class="lightbox__spinner" aria-hidden="true"></span>
        </div>
        <figcaption class="lightbox__caption"><span data-lightbox-counter></span><span data-lightbox-caption></span></figcaption>
    </figure>
    <button class="lightbox__btn lightbox__close" type="button" data-lightbox-close aria-label="Cerrar visor">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
    </button>
    <button class="lightbox__btn lightbox__prev" type="button" data-lightbox-prev aria-label="Foto anterior">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 6l-6 6 6 6"/></svg>
    </button>
    <button class="lightbox__btn lightbox__next" type="button" data-lightbox-next aria-label="Foto siguiente">
        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
    </button>
</div>

<!-- ===================== Mensaje secreto ===================== -->
<div class="secret" data-secret role="dialog" aria-modal="true" aria-labelledby="secret-title" hidden>
    <div class="secret__backdrop" data-secret-close></div>
    <div class="secret__card">
        <svg class="secret__icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l1.9 7.1L21 12l-7.1 1.9L12 21l-1.9-7.1L3 12l7.1-1.9z"/></svg>
        <h2 class="secret__title" id="secret-title"><?= e(c('secret.title')) ?></h2>
        <p class="secret__text"><?= e(c('secret.message')) ?></p>
        <button class="btn" type="button" data-secret-close>Guardar este secreto</button>
    </div>
</div>

<div class="toast" data-toast role="status" aria-live="polite"></div>

</body>
</html>

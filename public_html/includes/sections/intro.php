<?php
/**
 * Introducción cinematográfica (pantalla completa, solo con JavaScript).
 * Textos: data/content.php → 'intro'
 */

defined('SEYDI_APP') || exit;

$ring = (string) c('intro.ring_text', '');
?>
<div class="intro" data-intro role="dialog" aria-modal="true" aria-labelledby="intro-title">
    <canvas class="intro__dust" data-particles="dust" aria-hidden="true"></canvas>
    <div class="intro__light" aria-hidden="true"></div>

    <div class="intro__stage">
        <div class="intro__seal" aria-hidden="true">
            <svg class="intro__ring" viewBox="0 0 200 200">
                <defs><path id="ring-path" d="M100,100 m-82,0 a82,82 0 1,1 164,0 a82,82 0 1,1 -164,0"/></defs>
                <text><textPath href="#ring-path" textLength="510" lengthAdjust="spacing"><?= e(mb_strtoupper($ring)) ?></textPath></text>
            </svg>
            <svg class="intro__circle" viewBox="0 0 200 200"><circle cx="100" cy="100" r="64"/></svg>
            <span class="intro__monogram"><?= e(c('monogram', 'S')) ?></span>
        </div>

        <h1 class="intro__title" id="intro-title">
            <?php foreach ((array) c('intro.lines', []) as $i => $line): ?>
            <span class="intro__line" style="--d: <?= (int) $i ?>"><?= e($line) ?></span>
            <?php endforeach; ?>
        </h1>

        <button class="intro__start" type="button" data-intro-start data-cursor="Entrar">
            <span class="intro__start-label"><?= e(c('intro.button', 'Comenzar')) ?></span>
            <svg class="intro__start-icon" viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
        </button>
        <p class="intro__hint">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
            <?= e(c('intro.hint', '')) ?>
        </p>
    </div>
</div>

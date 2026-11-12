<?php
/**
 * I · Portada.  Textos: data/content.php → 'hero'
 */

defined('SEYDI_APP') || exit;

$birthday = birthday_datetime();
$photo = (array) c('hero.photo', []);
?>
<section class="hero" id="inicio" data-section="I" data-title="Inicio" aria-labelledby="hero-title">
    <div class="hero__glow" aria-hidden="true"></div>

    <div class="hero__media" data-hero-in="media">
        <div class="hero__arch-line" aria-hidden="true"></div>
        <figure class="hero__photo" data-parallax="-0.06" style="aspect-ratio: <?= e(ratio($photo)) ?>">
            <?= img($photo, ['eager' => true, 'sizes' => '(min-width: 900px) 40vw, 80vw']) ?>
        </figure>
        <button class="star" type="button" data-star="1" aria-label="Una estrella escondida (1)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l1.9 7.1L21 12l-7.1 1.9L12 21l-1.9-7.1L3 12l7.1-1.9z"/></svg></button>
    </div>

    <div class="hero__text">
        <p class="eyebrow" data-hero-in="1"><?= e(c('hero.eyebrow')) ?></p>
        <h2 class="hero__title" id="hero-title" data-hero-in="2"><?= e(c('hero.title')) ?></h2>
        <p class="hero__date" data-hero-in="3">
            <span><?= e($birthday->format('d')) ?></span><i aria-hidden="true"></i><span><?= e($birthday->format('m')) ?></span><i aria-hidden="true"></i><span><?= e($birthday->format('Y')) ?></span>
            <span class="sr-only"><?= e(spanish_date($birthday)) ?></span>
        </p>
        <p class="hero__message" data-hero-in="4"><?= e(c('hero.message')) ?></p>
        <a class="btn btn--ghost" href="#historia" data-hero-in="5" data-cursor="Leer">
            Empezar la historia
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 5v14M6 13l6 6 6-6"/></svg>
        </a>
    </div>

    <a class="scroll-cue" href="#historia" aria-label="Bajar a Nuestra historia"><span></span></a>
</section>

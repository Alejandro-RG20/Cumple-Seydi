<?php
/**
 * III · Galería editorial (masonry + visor).  Datos: data/content.php → 'gallery'
 */

defined('SEYDI_APP') || exit;

$photos = (array) c('gallery', []);
?>
<section class="gallery" id="galeria" data-section="III" data-title="Galería" aria-labelledby="gallery-title">
    <header class="section-head section-head--split">
        <div>
            <p class="section-num" data-reveal>III</p>
            <h2 class="section-title" id="gallery-title" data-reveal>Galería <em>de luz</em></h2>
        </div>
        <div class="section-head__aside" data-reveal>
            <p class="section-lead"><?= e(c('gallery_intro')) ?></p>
            <p class="gallery__count"><?= count($photos) ?> fotografías
                <button class="star star--inline" type="button" data-star="2" aria-label="Una estrella escondida (2)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l1.9 7.1L21 12l-7.1 1.9L12 21l-1.9-7.1L3 12l7.1-1.9z"/></svg></button>
            </p>
        </div>
    </header>

    <ul class="masonry" data-gallery>
        <?php foreach ($photos as $i => $p): $p = (array) $p; $info = photo_info((string) ($p['src'] ?? '')); $w = $info['w']; $h = $info['h']; $alt = ($p['alt'] ?? '') !== '' ? $p['alt'] : $info['alt']; ?>
        <li class="masonry__item" data-reveal="rise" style="--d: <?= (int) ($i % 3) ?>">
            <button class="photo-card" type="button" data-index="<?= (int) $i ?>"
                    data-full="<?= e($info['url']) ?>" data-w="<?= (int) $w ?>" data-h="<?= (int) $h ?>"
                    data-caption="<?= e($p['caption'] ?? '') ?>" data-cursor="Ver"
                    aria-label="Ver a pantalla completa: <?= e($alt !== '' ? $alt : 'fotografía') ?>">
                <span class="photo-card__frame" style="aspect-ratio: <?= (int) $w ?> / <?= (int) $h ?>">
                    <?= img($p, ['sizes' => '(min-width: 1100px) 30vw, (min-width: 700px) 45vw, 48vw']) ?>
                </span>
                <span class="photo-card__meta" aria-hidden="true">
                    <span><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
                    <svg viewBox="0 0 24 24"><path d="M15 3h6v6M9 21H3v-6M21 3l-7 7M3 21l7-7"/></svg>
                </span>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>
</section>

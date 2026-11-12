<?php
/**
 * II · Nuestra historia (línea de tiempo).  Datos: data/content.php → 'story'
 */

defined('SEYDI_APP') || exit;

$chapters = (array) c('story', []);
?>
<section class="story theme-light" id="historia" data-section="II" data-title="Nuestra historia" aria-labelledby="story-title">
    <header class="section-head">
        <p class="section-num" data-reveal>II</p>
        <h2 class="section-title" id="story-title" data-reveal>Nuestra <em>historia</em></h2>
        <p class="section-lead" data-reveal><?= e(c('story_intro')) ?></p>
    </header>

    <div class="timeline" data-timeline>
    <div class="timeline__track" aria-hidden="true"><span class="timeline__fill" data-timeline-fill></span></div>
    <ol class="timeline__list">
        <?php foreach ($chapters as $i => $ch): $photo = (array) ($ch['photo'] ?? []); ?>
        <li class="chapter" data-chapter>
            <span class="chapter__node" aria-hidden="true"></span>
            <figure class="chapter__media" data-reveal="mask" style="aspect-ratio: <?= e(ratio($photo)) ?>">
                <?= img($photo, ['sizes' => '(min-width: 900px) 38vw, 86vw']) ?>
            </figure>
            <div class="chapter__body">
                <span class="chapter__num" aria-hidden="true" data-reveal><?= e(roman($i + 1)) ?></span>
                <p class="chapter__date" data-reveal><?= e($ch['date'] ?? '') ?></p>
                <h3 class="chapter__title" data-reveal><?= e($ch['title'] ?? '') ?></h3>
                <div class="chapter__text" data-reveal><?= paragraphs((string) ($ch['text'] ?? '')) ?></div>
            </div>
        </li>
        <?php endforeach; ?>
    </ol>
    </div>
</section>

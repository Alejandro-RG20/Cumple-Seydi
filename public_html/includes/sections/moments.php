<?php
/**
 * V · Momentos que quiero recordar.  Datos: data/content.php → 'moments'
 * Cada momento usa su propia composición según 'layout'.
 */

defined('SEYDI_APP') || exit;

$moments = (array) c('moments', []);
$allowed = ['polaroid', 'split', 'quote', 'flip', 'ticket'];
?>
<section class="moments theme-light" id="momentos" data-section="V" data-title="Momentos" aria-labelledby="moments-title">
    <header class="section-head">
        <p class="section-num" data-reveal>V</p>
        <h2 class="section-title" id="moments-title" data-reveal>Momentos que <em>quiero recordar</em></h2>
        <p class="section-lead" data-reveal><?= e(c('moments_intro')) ?></p>
    </header>

    <div class="moments__grid">
        <?php foreach ($moments as $i => $m):
            $m = (array) $m;
            $layout = in_array($m['layout'] ?? '', $allowed, true) ? $m['layout'] : 'split';
            $photo = (array) ($m['photo'] ?? []);
            $hasPhoto = !empty($photo['src']);
            $date = (string) ($m['date'] ?? '');
            $title = (string) ($m['title'] ?? '');
            $text = (string) ($m['text'] ?? '');
        ?>
        <article class="moment moment--<?= e($layout) ?>" data-reveal="rise">
            <?php if ($layout === 'polaroid'): ?>
                <div class="polaroid">
                    <?php if ($hasPhoto): ?><figure class="polaroid__photo"><?= img($photo, ['sizes' => '(min-width: 900px) 30vw, 80vw']) ?></figure><?php endif; ?>
                    <p class="polaroid__caption"><?= e($title) ?></p>
                </div>
                <div class="moment__text">
                    <?php if ($date !== ''): ?><p class="moment__date"><?= e($date) ?></p><?php endif; ?>
                    <?= paragraphs($text) ?>
                </div>

            <?php elseif ($layout === 'quote'): ?>
                <blockquote class="quote">
                    <svg class="quote__mark" viewBox="0 0 48 36" aria-hidden="true"><path d="M0 36V20C0 8 7 1 19 0v7c-6 1-10 5-10 12h10v17H0zm29 0V20c0-12 7-19 19-20v7c-6 1-10 5-10 12h10v17H29z"/></svg>
                    <p><?= e($text) ?></p>
                    <?php if (!empty($m['author'])): ?><footer>— <?= e($m['author']) ?></footer><?php endif; ?>
                </blockquote>

            <?php elseif ($layout === 'flip'): ?>
                <button class="flip" type="button" aria-pressed="false" data-flip data-cursor="Girar">
                    <span class="flip__inner">
                        <span class="flip__face flip__front">
                            <?php if ($hasPhoto): ?><span class="flip__photo"><?= img($photo, ['sizes' => '(min-width: 900px) 30vw, 80vw']) ?></span><?php endif; ?>
                            <span class="flip__label"><?= e($title) ?>
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3-6.7L21 8M21 3v5h-5"/></svg>
                            </span>
                        </span>
                        <span class="flip__face flip__back">
                            <span class="flip__script"><?= e($text) ?></span>
                        </span>
                    </span>
                </button>

            <?php elseif ($layout === 'ticket'): ?>
                <div class="ticket">
                    <div class="ticket__main">
                        <p class="ticket__kicker">Admite a dos</p>
                        <h3 class="ticket__title"><?= e($title) ?></h3>
                        <p class="ticket__text"><?= e($text) ?></p>
                    </div>
                    <div class="ticket__stub">
                        <p><span>Fecha</span><?= e($date) ?></p>
                        <?php if (!empty($m['place'])): ?><p><span>Lugar</span><?= e($m['place']) ?></p><?php endif; ?>
                        <p class="ticket__no">Nº <?= str_pad((string) ($i + 1), 3, '0', STR_PAD_LEFT) ?></p>
                    </div>
                </div>

            <?php else: /* split */ ?>
                <?php if ($hasPhoto): ?><figure class="split__photo" data-reveal="mask"><?= img($photo, ['sizes' => '(min-width: 900px) 40vw, 86vw']) ?></figure><?php endif; ?>
                <div class="split__text">
                    <?php if ($date !== ''): ?><p class="moment__date"><?= e($date) ?></p><?php endif; ?>
                    <h3 class="moment__title"><?= e($title) ?></h3>
                    <?= paragraphs($text) ?>
                </div>
            <?php endif; ?>

            <?php if ($i === 2): ?>
                <button class="star" type="button" data-star="3" aria-label="Una estrella escondida (3)"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2l1.9 7.1L21 12l-7.1 1.9L12 21l-1.9-7.1L3 12l7.1-1.9z"/></svg></button>
            <?php endif; ?>
        </article>
        <?php endforeach; ?>
    </div>
</section>

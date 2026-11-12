<?php
/**
 * VII · Mensaje final, respuesta privada y cierre.
 * Textos: data/content.php → 'final' y 'reply'
 */

defined('SEYDI_APP') || exit;

$photo = (array) c('final.photo', []);
?>
<section class="final" id="final" data-section="VII" data-title="Final" aria-labelledby="final-title">
    <div class="final__media" aria-hidden="true">
        <?= img($photo, ['class' => 'final__img', 'sizes' => '100vw']) ?>
    </div>
    <canvas class="final__embers" data-particles="embers" aria-hidden="true"></canvas>

    <div class="final__content">
        <p class="eyebrow" data-reveal>VII · <?= e(c('final.eyebrow')) ?></p>
        <h2 class="final__lines" id="final-title">
            <?php foreach ((array) c('final.lines', []) as $i => $line): ?>
            <span class="final__line" data-reveal="line" style="--d: <?= (int) $i ?>"><?= e($line) ?></span>
            <?php endforeach; ?>
        </h2>
        <p class="final__closing" data-reveal><?= e(c('final.closing')) ?></p>
        <p class="final__signature" data-reveal="sign"><span><?= e(c('signature')) ?></span></p>
    </div>
</section>

<section class="epilogue" aria-label="Epílogo">
    <?php if (c('reply.enabled', true)): ?>
    <form class="reply" method="post" action="api/mensaje.php" data-reply data-reveal novalidate>
        <h2 class="reply__title"><?= e(c('reply.title')) ?></h2>
        <label class="sr-only" for="reply-message">Tu mensaje</label>
        <textarea id="reply-message" name="message" rows="4" maxlength="1500" required
                  placeholder="<?= e(c('reply.placeholder')) ?>"></textarea>
        <div class="reply__hp" aria-hidden="true">
            <label for="reply-website">No llenar</label>
            <input id="reply-website" name="website" type="text" tabindex="-1" autocomplete="off">
        </div>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="t" value="<?= time() ?>">
        <div class="reply__row">
            <p class="reply__status" role="status" aria-live="polite" data-reply-status></p>
            <button class="btn" type="submit" data-cursor="Enviar"><?= e(c('reply.button', 'Enviar')) ?></button>
        </div>
        <template data-reply-thanks><?= e(c('reply.thanks')) ?></template>
    </form>
    <?php endif; ?>

    <div class="epilogue__actions" data-reveal>
        <button class="btn btn--ghost" type="button" data-replay data-cursor="Otra vez">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M3 12a9 9 0 1 0 3-6.7L3 8M3 3v5h5"/></svg>
            Volver a empezar
        </button>
        <a class="btn btn--ghost" href="#inicio" data-cursor="Arriba">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 19V5M6 11l6-6 6 6"/></svg>
            Volver arriba
        </a>
    </div>
</section>

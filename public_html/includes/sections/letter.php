<?php
/**
 * IV · Carta especial (sobre → carta).  Textos: data/content.php → 'letter'
 */

defined('SEYDI_APP') || exit;
?>
<section class="letter-section" id="carta" data-section="IV" data-title="La carta" aria-labelledby="letter-title">
    <div class="letter-section__light" aria-hidden="true"></div>
    <header class="section-head section-head--center">
        <p class="section-num" data-reveal>IV</p>
        <h2 class="section-title" id="letter-title" data-reveal>Una carta <em>para ti</em></h2>
    </header>

    <div class="envelope-wrap" data-letter-stage>
        <div class="envelope" data-envelope data-reveal>
            <div class="envelope__back" aria-hidden="true"></div>
            <div class="envelope__paper" aria-hidden="true">
                <span class="envelope__paper-line"></span><span class="envelope__paper-line"></span><span class="envelope__paper-line"></span>
            </div>
            <div class="envelope__pocket" aria-hidden="true">
                <span class="envelope__to"><?= e(c('letter.envelope_label')) ?></span>
            </div>
            <div class="envelope__flap" aria-hidden="true"></div>
            <button class="seal" type="button" data-letter-open data-cursor="Abrir" aria-label="Abrir la carta" aria-controls="letter-paper" aria-expanded="false">
                <span class="seal__wax" aria-hidden="true"><span class="seal__letter"><?= e(c('monogram', 'S')) ?></span></span>
            </button>
        </div>
        <p class="envelope-hint" data-reveal><?= e(c('letter.envelope_hint')) ?></p>
    </div>

    <article class="letter" id="letter-paper" data-letter hidden aria-label="Carta">
        <div class="letter__paper">
            <p class="letter__date"><?= e(spanish_date(birthday_datetime())) ?></p>
            <p class="letter__greeting" data-letter-greeting><?= e(c('letter.greeting')) ?></p>
            <div class="letter__body" data-letter-body><?= paragraphs((string) c('letter.body')) ?></div>
            <p class="letter__closing" data-letter-tail><?= e(c('letter.closing')) ?></p>
            <p class="letter__signature" data-letter-tail><span><?= e(c('signature')) ?></span></p>
        </div>
        <div class="letter__actions">
            <button class="btn btn--small" type="button" data-letter-skip>Mostrar todo</button>
            <button class="btn btn--small btn--ghost" type="button" data-letter-close>Guardar en el sobre</button>
        </div>
    </article>
</section>

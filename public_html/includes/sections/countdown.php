<?php
/**
 * VI · Cuenta regresiva / reloj especial.
 * Fecha: data/content.php → 'birthday' y 'timezone'.  Textos → 'countdown'.
 * El estado inicial se calcula en PHP (funciona sin JavaScript) y JS lo mantiene vivo.
 */

defined('SEYDI_APP') || exit;

$target = birthday_datetime();
$now = new DateTimeImmutable('now');
$hours = max(1, (int) c('celebration_hours', 24));
$diff = $target->getTimestamp() - $now->getTimestamp();
if ($diff > 0) {
    $state = 'before';
} elseif (-$diff < $hours * 3600) {
    $state = 'today';
} else {
    $state = 'past';
}
$left = max(0, $diff);
$parts = [
    'days'    => intdiv($left, 86400),
    'hours'   => intdiv($left % 86400, 3600),
    'minutes' => intdiv($left % 3600, 60),
    'seconds' => $left % 60,
];
$titleKey = ['before' => 'title_before', 'today' => 'title_after', 'past' => 'title_past'][$state];
$textKey  = ['before' => 'text_before', 'today' => 'text_after', 'past' => 'text_past'][$state];
$labels = ['days' => 'Días', 'hours' => 'Horas', 'minutes' => 'Minutos', 'seconds' => 'Segundos'];
?>
<section class="clock-section" id="reloj" data-section="VI" data-title="Tu día" data-state="<?= e($state) ?>" data-countdown aria-labelledby="clock-title">
    <canvas class="clock-section__confetti" data-confetti aria-hidden="true"></canvas>
    <header class="section-head section-head--center">
        <p class="section-num" data-reveal>VI</p>
        <h2 class="section-title" id="clock-title" data-reveal data-clock-title><?= e(c('countdown.' . $titleKey)) ?></h2>
        <p class="section-lead" data-reveal data-clock-text><?= e(c('countdown.' . $textKey)) ?></p>
    </header>

    <div class="dial" data-reveal>
        <svg class="dial__svg" viewBox="0 0 320 320" aria-hidden="true">
            <circle class="dial__halo" cx="160" cy="160" r="150"/>
            <g class="dial__ticks" data-ticks></g>
            <circle class="dial__arc" cx="160" cy="160" r="128" pathLength="60" data-arc/>
        </svg>
        <div class="dial__center">
            <span class="dial__big" data-dial-big><?= $state === 'before' ? (int) $parts['days'] : '✦' ?></span>
            <span class="dial__small" data-dial-small><?= $state === 'before' ? ($parts['days'] === 1 ? 'día' : 'días') : e(spanish_date($target)) ?></span>
        </div>
    </div>

    <div class="units" data-units <?= $state !== 'before' ? 'hidden' : '' ?> role="timer" aria-live="off">
        <?php foreach ($labels as $key => $label): ?>
        <div class="unit">
            <span class="unit__value" data-unit="<?= e($key) ?>"><?= str_pad((string) $parts[$key], 2, '0', STR_PAD_LEFT) ?></span>
            <span class="unit__label"><?= e($label) ?></span>
        </div>
        <?php endforeach; ?>
    </div>
    <p class="clock-section__date"><?= e(spanish_date($target)) ?> · <?= e($target->format('H:i')) ?> h</p>
</section>

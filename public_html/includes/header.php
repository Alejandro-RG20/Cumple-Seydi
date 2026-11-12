<?php
/**
 * <head>, capas globales (progreso, grano, cursor) y navegación.
 */

defined('SEYDI_APP') || exit;

$seoTitle = (string) c('seo.title', 'Feliz cumpleaños');
$seoDesc  = (string) c('seo.description', '');
$siteUrl  = rtrim((string) $CONFIG['site_url'], '/');
$ogImage  = absolute_image((string) c('seo.share_image', ''));
$birthday = birthday_datetime();

$navItems = [
    ['id' => 'inicio',   'label' => 'Inicio'],
    ['id' => 'historia', 'label' => 'Nuestra historia'],
    ['id' => 'galeria',  'label' => 'Galería'],
    ['id' => 'carta',    'label' => 'La carta'],
    ['id' => 'momentos', 'label' => 'Momentos'],
    ['id' => 'reloj',    'label' => 'Tu día'],
    ['id' => 'final',    'label' => 'Final'],
];

/* Datos que necesita JavaScript (sin scripts en línea: compatible con CSP). */
$clientData = [
    'herName'   => (string) c('her_name'),
    'birthday'  => $birthday->format(DATE_ATOM),
    'celebrationHours' => max(1, (int) c('celebration_hours', 24)),
    'countdown' => c('countdown', []),
    'music'     => [
        'local'   => local_music(),
        'spotify' => preg_match('/^[A-Za-z0-9]{22}$/', (string) c('music.spotify_track_id', '')) ? (string) c('music.spotify_track_id') : null,
        'volume'  => max(0, min(1, (float) c('music.volume', 0.6))),
    ],
    'reply' => [
        'endpoint' => 'api/mensaje.php',
        'csrf'     => csrf_token(),
    ],
];
?><!doctype html>
<html lang="es" class="no-js">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title><?= e($seoTitle) ?></title>
<meta name="description" content="<?= e($seoDesc) ?>">
<?php if (!$CONFIG['indexable']): ?>
<meta name="robots" content="noindex, nofollow">
<?php endif; ?>
<?php if ($siteUrl !== ''): ?>
<link rel="canonical" href="<?= e($siteUrl) ?>/">
<meta property="og:url" content="<?= e($siteUrl) ?>/">
<?php endif; ?>
<meta name="theme-color" content="#120a0d">
<meta name="color-scheme" content="dark light">
<meta property="og:type" content="website">
<meta property="og:locale" content="es_ES">
<meta property="og:title" content="<?= e($seoTitle) ?>">
<meta property="og:description" content="<?= e($seoDesc) ?>">
<?php if ($ogImage !== ''): ?>
<meta property="og:image" content="<?= e($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<?php endif; ?>
<link rel="icon" href="<?= e(asset('icons/favicon.svg')) ?>" type="image/svg+xml">
<link rel="preload" href="assets/fonts/cormorant-garamond-italic.woff2" as="font" type="font/woff2" crossorigin>
<link rel="preload" href="assets/fonts/jost.woff2" as="font" type="font/woff2" crossorigin>
<link rel="stylesheet" href="<?= e(asset('css/base.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/components.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/sections.css')) ?>">
<script src="<?= e(asset('js/boot.js')) ?>"></script>
<script type="module" src="<?= e(asset('js/main.js')) ?>"></script>
<script type="application/json" id="app-data"><?= json_for_html($clientData) ?></script>
</head>
<body>
<a class="skip-link" href="#inicio">Saltar al contenido</a>

<div class="progress" aria-hidden="true"><span class="progress__bar"></span></div>
<div class="grain" aria-hidden="true"></div>
<div class="cursor" aria-hidden="true"><span class="cursor__label"></span></div>

<header class="topbar" data-topbar>
    <a class="topbar__mark" href="#inicio" aria-label="Volver al inicio">
        <span class="monogram"><?= e(c('monogram', 'S')) ?></span>
    </a>
    <p class="topbar__chapter" aria-live="off"><span data-chapter-num>I</span><span class="topbar__dash"></span><span data-chapter-name>Inicio</span></p>
    <button class="menu-btn" type="button" aria-expanded="false" aria-controls="menu" data-menu-toggle data-cursor="Menú">
        <span class="menu-btn__text">Menú</span>
        <span class="menu-btn__lines" aria-hidden="true"><span></span><span></span></span>
    </button>
</header>

<nav class="menu" id="menu" aria-label="Secciones" data-menu hidden>
    <div class="menu__inner">
        <ol class="menu__list">
            <?php foreach ($navItems as $i => $item): ?>
            <li style="--i: <?= (int) $i ?>">
                <a href="#<?= e($item['id']) ?>" data-menu-link data-cursor="Ir">
                    <span class="menu__num"><?= e(roman($i + 1)) ?></span>
                    <span class="menu__label"><?= e($item['label']) ?></span>
                </a>
            </li>
            <?php endforeach; ?>
        </ol>
        <p class="menu__foot"><?= e(c('her_name')) ?> · <?= e(spanish_date($birthday)) ?></p>
    </div>
</nav>

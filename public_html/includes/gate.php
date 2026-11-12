<?php
/**
 * Pantalla de código de acceso (solo si config 'access_code' no está vacío).
 */

defined('SEYDI_APP') || exit;
http_response_code(200);
header('Cache-Control: no-store');
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Una sorpresa te espera</title>
<meta name="robots" content="noindex, nofollow">
<meta name="theme-color" content="#120a0d">
<link rel="icon" href="<?= e(asset('icons/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/base.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/components.css')) ?>">
</head>
<body class="gate-body">
<main class="gate">
    <span class="monogram monogram--lg" aria-hidden="true"><?= e(c('monogram', 'S')) ?></span>
    <h1 class="gate__title">Algo especial te espera</h1>
    <p class="gate__hint"><?= e($CONFIG['access_hint']) ?></p>
    <form class="gate__form" method="post" action="./">
        <label class="sr-only" for="access-code">Código de acceso</label>
        <input id="access-code" name="access_code" type="password" inputmode="text" autocomplete="off" maxlength="100" required autofocus placeholder="Código">
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
        <button class="btn" type="submit">Entrar</button>
    </form>
    <?php if (!empty($accessError)): ?><p class="gate__error" role="alert"><?= e($accessError) ?></p><?php endif; ?>
</main>
</body>
</html>

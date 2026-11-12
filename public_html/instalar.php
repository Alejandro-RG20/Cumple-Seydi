<?php
/**
 * Instalador web (se usa UNA vez, justo después de subir los archivos).
 *
 * 1. Pide los datos de MySQL que muestra InfinityFree.
 * 2. Comprueba la conexión y guarda config/config.php.
 * 3. Ejecuta database/seydi.sql (tablas + textos + fotos + usuario del panel).
 * 4. Se bloquea a sí mismo (storage/instalado.lock).
 *
 * Alternativa manual: importar database/seydi.sql desde phpMyAdmin.
 */

define('SEYDI_APP', true);
define('SEYDI_SKIP_CONTENT', true);
require __DIR__ . '/includes/bootstrap.php';
header('Cache-Control: no-store');

$lock = APP_ROOT . '/storage/instalado.lock';
$sqlFile = APP_ROOT . '/database/seydi.sql';
$configFile = APP_ROOT . '/config/config.php';
$installed = is_file($lock) || db_installed(db());
$errors = [];
$done = false;
$form = array_merge(['host' => '', 'port' => 3306, 'name' => '', 'user' => '', 'pass' => ''], (array) ($CONFIG['db'] ?? []));
$siteUrl = (string) $CONFIG['site_url'];

if (!$installed && ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        $errors[] = 'La sesión caducó. Recarga la página.';
    }
    foreach (['host', 'name', 'user'] as $f) {
        $form[$f] = trim((string) ($_POST[$f] ?? ''));
        if ($form[$f] === '' || strlen($form[$f]) > 120 || !preg_match('/^[\w.\-]+$/', $form[$f])) {
            $errors[] = 'Revisa el campo "' . $f . '".';
        }
    }
    $form['port'] = (int) ($_POST['port'] ?? 3306) ?: 3306;
    $newPass = (string) ($_POST['pass'] ?? '');
    if ($newPass !== '') {
        $form['pass'] = $newPass;
    }
    $siteUrl = trim((string) ($_POST['site_url'] ?? ''));
    if ($siteUrl !== '' && !preg_match('#^https?://[\w.\-]+(:\d+)?$#', rtrim($siteUrl, '/'))) {
        $errors[] = 'La URL del sitio debe ser como https://tusitio.infinityfreeapp.com';
    }
    if (!is_file($sqlFile)) {
        $errors[] = 'No se encuentra database/seydi.sql. Súbelo junto con los demás archivos.';
    }

    if (!$errors) {
        try {
            $pdo = new PDO(
                sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $form['host'], $form['port'], $form['name']),
                $form['user'], $form['pass'],
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, PDO::ATTR_TIMEOUT => 10]
            );
        } catch (Throwable $e) {
            $errors[] = 'No se pudo conectar a MySQL. Revisa los datos (' . $e->getMessage() . ').';
        }
    }

    if (!$errors) {
        // 1) Guardar config/config.php
        $cfg = require APP_ROOT . '/config/config.example.php';
        if (is_file($configFile)) {
            $cfg = array_merge($cfg, (array) (require $configFile));
        }
        $cfg['db'] = $form;
        if ($siteUrl !== '') {
            $cfg['site_url'] = rtrim($siteUrl, '/');
        }
        $php = "<?php\n/** Configuración generada por instalar.php el " . date('Y-m-d H:i') . ". Puedes editarla. */\n"
            . "defined('SEYDI_APP') || exit;\n\nreturn " . var_export($cfg, true) . ";\n";
        if (@file_put_contents($configFile, $php, LOCK_EX) === false) {
            $errors[] = 'No se pudo escribir config/config.php. Créalo a mano copiando config.example.php.';
        }
    }

    if (!$errors) {
        // 2) Ejecutar el SQL (una sentencia por línea)
        @set_time_limit(300);
        $fh = fopen($sqlFile, 'rb');
        $n = 0;
        try {
            while (($line = fgets($fh)) !== false) {
                $line = trim($line);
                if ($line === '' || strpos($line, '--') === 0) {
                    continue;
                }
                $pdo->exec($line);
                $n++;
            }
            @file_put_contents($lock, date(DATE_ATOM) . "\n");
            $done = true;
        } catch (Throwable $e) {
            $errors[] = 'Error al ejecutar la sentencia ' . ($n + 1) . ': ' . $e->getMessage();
        }
        fclose($fh);
    }
}
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Instalación</title>
<link rel="icon" href="<?= e(asset('icons/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e(asset('css/base.css')) ?>">
<link rel="stylesheet" href="<?= e(asset('css/admin.css')) ?>">
</head>
<body class="admin-body">
<main class="admin-narrow">
    <span class="monogram monogram--lg" aria-hidden="true">S</span>
    <h1 class="admin-title">Instalación</h1>

    <?php if ($installed): ?>
        <p class="note">La web ya está instalada y conectada a la base de datos.</p>
        <p><a class="btn" href="admin/">Ir al panel</a> <a class="btn btn--ghost" href="./">Ver la web</a></p>

    <?php elseif ($done): ?>
        <div class="flash flash--ok">¡Listo! Base de datos instalada: tablas, textos, fotos y usuario del panel.</div>
        <ol class="steps">
            <li>Entra al panel con el usuario <strong>alejandro</strong> y la contraseña que te di.</li>
            <li>Cambia la contraseña desde <em>Mi cuenta</em>.</li>
            <li>Este instalador ya quedó bloqueado.</li>
        </ol>
        <p><a class="btn" href="admin/">Ir al panel</a> <a class="btn btn--ghost" href="./">Ver la web</a></p>

    <?php else: ?>
        <p class="note">Copia los datos de <strong>InfinityFree → Control Panel → MySQL Databases</strong>. Antes, crea una base de datos (por ejemplo, <em>seydi</em>).</p>
        <?php foreach ($errors as $err): ?><div class="flash flash--error"><?= e($err) ?></div><?php endforeach; ?>
        <form method="post" class="card form-grid" autocomplete="off">
            <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
            <label>MySQL Host Name <input name="host" required value="<?= e($form['host']) ?>" placeholder="sql123.infinityfree.com"></label>
            <label>MySQL DB Name <input name="name" required value="<?= e($form['name']) ?>" placeholder="if0_12345678_seydi"></label>
            <label>MySQL User Name <input name="user" required value="<?= e($form['user']) ?>" placeholder="if0_12345678"></label>
            <label>MySQL Password <input name="pass" type="password" placeholder="<?= $form['pass'] !== '' ? 'Guardada (deja vacío para mantenerla)' : 'Contraseña de tu cuenta (vPanel)' ?>"></label>
            <label>Puerto <input name="port" type="number" value="<?= (int) $form['port'] ?>"></label>
            <label>URL del sitio (opcional) <input name="site_url" value="<?= e($siteUrl) ?>" placeholder="https://tusitio.infinityfreeapp.com"></label>
            <button class="btn" type="submit">Instalar</button>
        </form>
    <?php endif; ?>
</main>
</body>
</html>

<?php
/**
 * Panel de administración: /admin
 * Edita textos, fotos, historia, galería y momentos (todo en MySQL)
 * y permite leer los mensajes que ella deja.
 */

define('SEYDI_APP', true);
define('SEYDI_SKIP_CONTENT', true);
require dirname(__DIR__) . '/includes/bootstrap.php';
require APP_ROOT . '/includes/admin.php';

header('Cache-Control: no-store');
header('X-Robots-Tag: noindex, nofollow');

$pdo = db();
$page = preg_replace('/[^a-z]/', '', (string) ($_GET['p'] ?? 'inicio')) ?: 'inicio';
$self = static fn (string $p = 'inicio', array $q = []) => '?' . http_build_query(['p' => $p] + $q);
$redirect = static function (string $url) {
    header('Location: ' . $url, true, 303);
    exit;
};

/* ---------- Sin base de datos: enviar al instalador ---------- */
if (!$pdo || !db_installed($pdo)) {
    $redirect('../instalar.php');
}

$user = admin_user($pdo);
$loginError = '';

/* ==========================================================================
   Acciones (POST)
   ========================================================================== */
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
    $action = (string) ($_POST['action'] ?? '');
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        flash('La sesión caducó. Vuelve a intentarlo.', 'error');
        $redirect($self($page));
    }

    if ($action === 'login') {
        $loginError = admin_login($pdo, (string) ($_POST['username'] ?? ''), (string) ($_POST['password'] ?? ''));
        if ($loginError === '') {
            $redirect($self());
        }
    } elseif (!$user) {
        $redirect($self());
    } else {
        switch ($action) {
            case 'logout':
                unset($_SESSION['admin_id'], $_SESSION['admin_seen']);
                session_regenerate_id(true);
                $redirect($self());

            case 'settings':
                $errors = 0;
                $up = $pdo->prepare('REPLACE INTO settings (k, v) VALUES (?, ?)');
                foreach (settings_form() as $fields) {
                    foreach ($fields as $key => [$label, $type]) {
                        $field = str_replace('.', '__', $key);
                        if ($type !== 'bool' && !array_key_exists($field, $_POST)) {
                            continue;
                        }
                        [$val, $err] = settings_clean($key, $type, $_POST[$field] ?? '');
                        if ($err) {
                            flash($label . ': ' . $err, 'error');
                            $errors++;
                            continue;
                        }
                        $up->execute([$key, $val]);
                    }
                }
                if (!$errors) {
                    flash('Cambios guardados.');
                }
                $redirect($self('ajustes'));

            case 'list_save':
            case 'list_add':
                $table = (string) ($_POST['table'] ?? '');
                $defs = list_defs();
                if (!isset($defs[$table])) {
                    $redirect($self());
                }
                $cols = ['position' => (int) ($_POST['position'] ?? 0)];
                foreach ($defs[$table]['fields'] as $col => [$label, $type]) {
                    $v = clean_text($_POST[$col] ?? '', $type === 'textarea' ? 5000 : 250);
                    if ($type === 'photo') {
                        $v = preg_match('/^[a-z0-9-]{1,80}$/', $v) ? $v : null;
                    }
                    if ($type === 'layout') {
                        $v = array_key_exists($v, MOMENT_LAYOUTS) ? $v : 'split';
                    }
                    $cols[$col] = $v;
                }
                if ($table === 'gallery' && empty($cols['photo_slug'])) {
                    flash('Elige una foto para la galería.', 'error');
                    $redirect($self($table === 'gallery' ? 'galeria' : $table));
                }
                if ($action === 'list_add') {
                    $sql = 'INSERT INTO `' . $table . '` (`' . implode('`, `', array_keys($cols)) . '`) VALUES (' . rtrim(str_repeat('?, ', count($cols)), ', ') . ')';
                    $pdo->prepare($sql)->execute(array_values($cols));
                    flash('Añadido.');
                } else {
                    $set = implode(', ', array_map(static fn ($c) => "`$c` = ?", array_keys($cols)));
                    $pdo->prepare("UPDATE `$table` SET $set WHERE id = ?")->execute([...array_values($cols), (int) ($_POST['id'] ?? 0)]);
                    flash('Guardado.');
                }
                $redirect($self(['story' => 'historia', 'gallery' => 'galeria', 'moments' => 'momentos'][$table]));

            case 'list_delete':
                $table = (string) ($_POST['table'] ?? '');
                if (isset(list_defs()[$table])) {
                    $pdo->prepare("DELETE FROM `$table` WHERE id = ?")->execute([(int) ($_POST['id'] ?? 0)]);
                    flash('Eliminado.');
                }
                $redirect($self(['story' => 'historia', 'gallery' => 'galeria', 'moments' => 'momentos'][$table] ?? 'inicio'));

            case 'photo_upload':
                $res = process_upload($_FILES['photo'] ?? []);
                if (is_string($res)) {
                    flash($res, 'error');
                    $redirect($self('fotos'));
                }
                $alt = clean_text($_POST['alt'] ?? '', 250);
                $slug = make_slug($pdo, $alt !== '' ? $alt : pathinfo((string) ($_FILES['photo']['name'] ?? 'foto'), PATHINFO_FILENAME));
                $st = $pdo->prepare('INSERT INTO photos (slug, alt, mime, width, height, data, thumb, thumb_width, thumb_height, bytes) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
                $st->bindValue(1, $slug);
                $st->bindValue(2, $alt);
                $st->bindValue(3, $res['mime']);
                $st->bindValue(4, $res['w'], PDO::PARAM_INT);
                $st->bindValue(5, $res['h'], PDO::PARAM_INT);
                $st->bindValue(6, $res['data'], PDO::PARAM_LOB);
                $st->bindValue(7, $res['thumb'], PDO::PARAM_LOB);
                $st->bindValue(8, $res['tw'], PDO::PARAM_INT);
                $st->bindValue(9, $res['th'], PDO::PARAM_INT);
                $st->bindValue(10, strlen($res['data']), PDO::PARAM_INT);
                $st->execute();
                if (!empty($_POST['to_gallery'])) {
                    $pos = (int) $pdo->query('SELECT COALESCE(MAX(position), 0) + 10 FROM gallery')->fetchColumn();
                    $pdo->prepare('INSERT INTO gallery (position, photo_slug, caption) VALUES (?, ?, ?)')->execute([$pos, $slug, '']);
                }
                flash('Foto subida y optimizada (' . round(strlen($res['data']) / 1024) . ' KB).');
                $redirect($self('fotos'));

            case 'photo_alt':
                $pdo->prepare('UPDATE photos SET alt = ? WHERE slug = ?')->execute([clean_text($_POST['alt'] ?? '', 250), (string) ($_POST['slug'] ?? '')]);
                flash('Descripción guardada.');
                $redirect($self('fotos'));

            case 'photo_rotate':
                $ok = rotate_photo($pdo, (string) ($_POST['slug'] ?? ''), ($_POST['dir'] ?? '') !== 'ccw');
                flash($ok ? 'Foto girada.' : 'No se pudo girar la foto.', $ok ? 'ok' : 'error');
                $redirect($self('fotos'));

            case 'photo_delete':
                $slug = (string) ($_POST['slug'] ?? '');
                $uses = photo_usage($pdo, $slug);
                if ($uses) {
                    flash('No se puede borrar: la foto se usa en ' . implode(', ', $uses) . '.', 'error');
                } else {
                    $pdo->prepare('DELETE FROM photos WHERE slug = ?')->execute([$slug]);
                    flash('Foto eliminada.');
                }
                $redirect($self('fotos'));

            case 'message_delete':
                $pdo->prepare('DELETE FROM messages WHERE id = ?')->execute([(int) ($_POST['id'] ?? 0)]);
                flash('Mensaje eliminado.');
                $redirect($self('mensajes'));

            case 'password':
                $current = (string) ($_POST['current'] ?? '');
                $new = (string) ($_POST['new'] ?? '');
                if (!password_verify($current, $user['password_hash'])) {
                    flash('La contraseña actual no es correcta.', 'error');
                } elseif (mb_strlen($new) < 10 || $new !== (string) ($_POST['confirm'] ?? '')) {
                    flash('La nueva contraseña debe tener al menos 10 caracteres y coincidir.', 'error');
                } else {
                    $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($new, PASSWORD_DEFAULT), $user['id']]);
                    session_regenerate_id(true);
                    flash('Contraseña actualizada.');
                }
                $redirect($self('cuenta'));
        }
        $redirect($self());
    }
}

/* ==========================================================================
   Vistas
   ========================================================================== */
$photos = $pdo->query('SELECT slug, alt, width, height, bytes, UNIX_TIMESTAMP(updated_at) AS ts FROM photos ORDER BY id')->fetchAll();
$thumbUrl = static fn ($slug) => '../foto.php?f=' . rawurlencode($slug) . '&s=t&v=' . (photo_meta($slug)['v'] ?? '0');

$photoSelect = static function (string $name, ?string $current, bool $allowEmpty = true) use ($photos) {
    $h = '<select name="' . e($name) . '">';
    if ($allowEmpty) {
        $h .= '<option value="">— Sin foto —</option>';
    }
    foreach ($photos as $p) {
        $h .= '<option value="' . e($p['slug']) . '"' . ($p['slug'] === $current ? ' selected' : '') . '>' . e($p['alt'] !== '' ? $p['alt'] : $p['slug']) . '</option>';
    }
    return $h . '</select>';
};

$field = static function (string $name, string $label, string $type, $value) use ($photoSelect) {
    $id = 'f_' . $name . '_' . substr(md5((string) mt_rand()), 0, 6);
    $h = '<label for="' . e($id) . '">' . e($label);
    switch ($type) {
        case 'textarea':
        case 'lines':
            $rows = max(3, min(14, substr_count((string) $value, "\n") + 3, (int) ceil(strlen((string) $value) / 70) + 1));
            $h .= '<textarea id="' . e($id) . '" name="' . e($name) . '" rows="' . $rows . '">' . e($value) . '</textarea>';
            break;
        case 'photo':
            $h .= str_replace('<select ', '<select id="' . e($id) . '" ', $photoSelect($name, (string) $value));
            break;
        case 'bool':
            return '<label class="check" for="' . e($id) . '"><input id="' . e($id) . '" type="checkbox" name="' . e($name) . '" value="1"' . ($value === '1' || $value === true ? ' checked' : '') . '> ' . e($label) . '</label>';
        case 'layout':
            $h .= '<select id="' . e($id) . '" name="' . e($name) . '">';
            foreach (MOMENT_LAYOUTS as $k => $l) {
                $h .= '<option value="' . e($k) . '"' . ($k === $value ? ' selected' : '') . '>' . e($l) . '</option>';
            }
            $h .= '</select>';
            break;
        case 'number':
            $h .= '<input id="' . e($id) . '" type="number" name="' . e($name) . '" value="' . e($value) . '">';
            break;
        default:
            $h .= '<input id="' . e($id) . '" type="text" name="' . e($name) . '" value="' . e($value) . '">';
    }
    return $h . '</label>';
};

$nav = [
    'inicio' => 'Inicio', 'ajustes' => 'Textos y ajustes', 'historia' => 'Historia', 'galeria' => 'Galería',
    'momentos' => 'Momentos', 'fotos' => 'Fotos', 'mensajes' => 'Mensajes', 'cuenta' => 'Mi cuenta',
];
$unread = (int) $pdo->query('SELECT COUNT(*) FROM messages WHERE is_read = 0')->fetchColumn();
$csrf = '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
?><!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title>Panel · <?= e($nav[$page] ?? 'Panel') ?></title>
<link rel="icon" href="<?= e('../' . asset('icons/favicon.svg')) ?>" type="image/svg+xml">
<link rel="stylesheet" href="<?= e('../' . asset('css/base.css')) ?>">
<link rel="stylesheet" href="<?= e('../' . asset('css/admin.css')) ?>">
</head>
<body class="admin-body">
<?php if (!$user): ?>
<main class="admin-narrow">
    <span class="monogram monogram--lg" aria-hidden="true">S</span>
    <h1 class="admin-title">Panel</h1>
    <?php foreach (take_flashes() as [$t, $m]): ?><div class="flash flash--<?= e($t) ?>"><?= e($m) ?></div><?php endforeach; ?>
    <?php if ($loginError): ?><div class="flash flash--error" role="alert"><?= e($loginError) ?></div><?php endif; ?>
    <form method="post" class="card form-grid" action="<?= e($self()) ?>">
        <?= $csrf ?><input type="hidden" name="action" value="login">
        <label>Usuario <input name="username" autocomplete="username" required autofocus></label>
        <label>Contraseña <input name="password" type="password" autocomplete="current-password" required></label>
        <button class="btn" type="submit">Entrar</button>
    </form>
    <p class="note"><a href="../">← Volver a la web</a></p>
</main>
<?php else: ?>
<header class="admin-top">
    <a class="admin-brand" href="<?= e($self()) ?>"><span class="monogram">S</span> Panel</a>
    <nav class="admin-nav" aria-label="Panel">
        <?php foreach ($nav as $k => $l): ?>
        <a href="<?= e($self($k)) ?>"<?= $k === $page ? ' aria-current="page"' : '' ?>><?= e($l) ?><?= $k === 'mensajes' && $unread ? ' <span class="badge">' . $unread . '</span>' : '' ?></a>
        <?php endforeach; ?>
        <a href="../" target="_blank" rel="noopener">Ver la web ↗</a>
    </nav>
    <form method="post" action="<?= e($self()) ?>"><?= $csrf ?><input type="hidden" name="action" value="logout"><button class="btn btn--small btn--ghost" type="submit">Salir</button></form>
</header>
<main class="admin-main">
    <?php foreach (take_flashes() as [$t, $m]): ?><div class="flash flash--<?= e($t) ?>" role="status"><?= e($m) ?></div><?php endforeach; ?>

<?php if ($page === 'ajustes'):
    $values = $pdo->query('SELECT k, v FROM settings')->fetchAll(PDO::FETCH_KEY_PAIR); ?>
    <h1 class="admin-title">Textos y ajustes</h1>
    <form method="post" class="stack" action="<?= e($self('ajustes')) ?>">
        <?= $csrf ?><input type="hidden" name="action" value="settings">
        <?php foreach (settings_form() as $group => $fields): ?>
        <fieldset class="card form-grid">
            <legend><?= e($group) ?></legend>
            <?php foreach ($fields as $key => [$label, $type]): ?>
                <?= $field(str_replace('.', '__', $key), $label, $type, $values[$key] ?? '') ?>
            <?php endforeach; ?>
        </fieldset>
        <?php endforeach; ?>
        <div class="sticky-save"><button class="btn" type="submit">Guardar todos los cambios</button></div>
    </form>

<?php elseif (in_array($page, ['historia', 'galeria', 'momentos'], true)):
    $table = ['historia' => 'story', 'galeria' => 'gallery', 'momentos' => 'moments'][$page];
    $def = list_defs()[$table];
    $rows = $pdo->query("SELECT * FROM `$table` ORDER BY position, id")->fetchAll();
    $nextPos = (int) (end($rows)['position'] ?? 0) + 10; ?>
    <h1 class="admin-title"><?= e($def['title']) ?></h1>
    <p class="note">El número de <strong>orden</strong> define la posición (menor = primero).</p>
    <?php foreach ($rows as $r): ?>
    <div class="card row-card">
        <?php if (!empty($r['photo_slug'])): ?><img class="row-thumb" src="<?= e($thumbUrl($r['photo_slug'])) ?>" alt="" loading="lazy"><?php endif; ?>
        <form method="post" class="form-grid" action="<?= e($self($page)) ?>">
            <?= $csrf ?><input type="hidden" name="action" value="list_save"><input type="hidden" name="table" value="<?= e($table) ?>"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <?= $field('position', 'Orden', 'number', $r['position']) ?>
            <?php foreach ($def['fields'] as $col => [$label, $type]): ?><?= $field($col, $label, $type, $r[$col]) ?><?php endforeach; ?>
            <button class="btn btn--small" type="submit">Guardar</button>
        </form>
        <form method="post" action="<?= e($self($page)) ?>" class="row-delete">
            <?= $csrf ?><input type="hidden" name="action" value="list_delete"><input type="hidden" name="table" value="<?= e($table) ?>"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <button class="btn btn--small btn--danger" type="submit">Eliminar</button>
        </form>
    </div>
    <?php endforeach; ?>
    <form method="post" class="card form-grid" action="<?= e($self($page)) ?>">
        <h2 class="card-title">Añadir <?= e($def['singular']) ?></h2>
        <?= $csrf ?><input type="hidden" name="action" value="list_add"><input type="hidden" name="table" value="<?= e($table) ?>">
        <?= $field('position', 'Orden', 'number', $nextPos) ?>
        <?php foreach ($def['fields'] as $col => [$label, $type]): ?><?= $field($col, $label, $type, $type === 'layout' ? 'split' : '') ?><?php endforeach; ?>
        <button class="btn btn--small" type="submit">Añadir</button>
    </form>

<?php elseif ($page === 'fotos'): ?>
    <h1 class="admin-title">Fotos</h1>
    <form method="post" enctype="multipart/form-data" class="card form-grid" action="<?= e($self('fotos')) ?>">
        <h2 class="card-title">Subir una foto</h2>
        <?= $csrf ?><input type="hidden" name="action" value="photo_upload">
        <label>Foto (JPG, PNG o WebP) <input type="file" name="photo" accept="image/jpeg,image/png,image/webp" required></label>
        <label>Descripción <input name="alt" maxlength="250" placeholder="Ej.: Seydi en la playa"></label>
        <label class="check"><input type="checkbox" name="to_gallery" value="1" checked> Añadir también a la galería</label>
        <p class="note">Se convierte automáticamente a WebP ligero (máx. 1600 px + miniatura de 800 px), sin metadatos ni ubicación GPS, y se guarda en la base de datos.</p>
        <button class="btn btn--small" type="submit">Subir</button>
    </form>
    <div class="photo-grid">
        <?php foreach ($photos as $p): ?>
        <div class="card photo-item">
            <img src="<?= e($thumbUrl($p['slug'])) ?>" alt="" loading="lazy" width="<?= (int) $p['width'] ?>" height="<?= (int) $p['height'] ?>">
            <p class="note"><?= e($p['slug']) ?> · <?= (int) $p['width'] ?>×<?= (int) $p['height'] ?> · <?= round($p['bytes'] / 1024) ?> KB</p>
            <form method="post" class="form-grid" action="<?= e($self('fotos')) ?>">
                <?= $csrf ?><input type="hidden" name="action" value="photo_alt"><input type="hidden" name="slug" value="<?= e($p['slug']) ?>">
                <label>Descripción <input name="alt" value="<?= e($p['alt']) ?>" maxlength="250"></label>
                <button class="btn btn--small" type="submit">Guardar</button>
            </form>
            <div class="rotate-row">
                <?php foreach (['ccw' => '↺ Girar izquierda', 'cw' => 'Girar derecha ↻'] as $dir => $lbl): ?>
                <form method="post" action="<?= e($self('fotos')) ?>">
                    <?= $csrf ?><input type="hidden" name="action" value="photo_rotate"><input type="hidden" name="slug" value="<?= e($p['slug']) ?>"><input type="hidden" name="dir" value="<?= e($dir) ?>">
                    <button class="btn btn--small btn--ghost" type="submit"><?= e($lbl) ?></button>
                </form>
                <?php endforeach; ?>
            </div>
            <form method="post" action="<?= e($self('fotos')) ?>">
                <?= $csrf ?><input type="hidden" name="action" value="photo_delete"><input type="hidden" name="slug" value="<?= e($p['slug']) ?>">
                <button class="btn btn--small btn--danger" type="submit">Eliminar</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

<?php elseif ($page === 'mensajes'):
    $msgs = $pdo->query('SELECT * FROM messages ORDER BY id DESC LIMIT 200')->fetchAll();
    $pdo->exec('UPDATE messages SET is_read = 1 WHERE is_read = 0'); ?>
    <h1 class="admin-title">Mensajes de ella</h1>
    <?php if (!$msgs): ?><p class="note">Todavía no hay mensajes.</p><?php endif; ?>
    <?php foreach ($msgs as $m): $d = new DateTime($m['created_at'], new DateTimeZone('UTC')); $d->setTimezone(new DateTimeZone(date_default_timezone_get())); ?>
    <article class="card message<?= $m['is_read'] ? '' : ' is-new' ?>">
        <p class="note"><?= e($d->format('d/m/Y H:i')) ?><?= $m['is_read'] ? '' : ' · nuevo' ?></p>
        <p class="message__text"><?= nl2br(e($m['message'])) ?></p>
        <form method="post" action="<?= e($self('mensajes')) ?>"><?= $csrf ?><input type="hidden" name="action" value="message_delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>"><button class="btn btn--small btn--danger" type="submit">Eliminar</button></form>
    </article>
    <?php endforeach; ?>

<?php elseif ($page === 'cuenta'): ?>
    <h1 class="admin-title">Mi cuenta</h1>
    <p class="note">Usuario: <strong><?= e($user['username']) ?></strong></p>
    <form method="post" class="card form-grid" action="<?= e($self('cuenta')) ?>">
        <h2 class="card-title">Cambiar contraseña</h2>
        <?= $csrf ?><input type="hidden" name="action" value="password">
        <label>Contraseña actual <input type="password" name="current" autocomplete="current-password" required></label>
        <label>Nueva contraseña (mín. 10) <input type="password" name="new" autocomplete="new-password" minlength="10" required></label>
        <label>Repite la nueva <input type="password" name="confirm" autocomplete="new-password" minlength="10" required></label>
        <button class="btn btn--small" type="submit">Actualizar</button>
    </form>

<?php else:
    $counts = [];
    foreach (['photos' => 'Fotos', 'story' => 'Capítulos', 'gallery' => 'En galería', 'moments' => 'Momentos', 'messages' => 'Mensajes'] as $t => $l) {
        $counts[$l] = (int) $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
    }
    $bday = $pdo->query("SELECT v FROM settings WHERE k = 'birthday'")->fetchColumn(); ?>
    <h1 class="admin-title">Hola, <?= e($user['display_name'] ?: $user['username']) ?></h1>
    <div class="stats">
        <?php foreach ($counts as $l => $n): ?><div class="card stat"><span><?= $n ?></span><?= e($l) ?></div><?php endforeach; ?>
    </div>
    <p class="note">Cumpleaños configurado: <strong><?= e($bday) ?></strong> (<?= e(date_default_timezone_get()) ?>). Todo el contenido se guarda en MySQL.</p>
    <?php if ($unread): ?><p><a class="btn" href="<?= e($self('mensajes')) ?>">Tienes <?= $unread ?> mensaje<?= $unread > 1 ? 's' : '' ?> nuevo<?= $unread > 1 ? 's' : '' ?></a></p><?php endif; ?>
<?php endif; ?>
</main>
<?php endif; ?>
</body>
</html>

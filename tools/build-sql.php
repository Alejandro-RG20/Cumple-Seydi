<?php
/**
 * Genera public_html/database/seydi.sql (estructura + contenido + fotos + usuario admin)
 * a partir de public_html/data/content.php y de una carpeta con las fotos en WebP.
 *
 * Herramienta de desarrollo (no se sube al hosting). Uso:
 *   php tools/build-sql.php --photos=/ruta/fotos --user=alejandro --hash='$2y$...'
 *
 * La carpeta de fotos debe contener <slug>.webp (grande) y <slug>-t.webp (miniatura).
 */

if (PHP_SAPI !== 'cli') {
    exit;
}
define('SEYDI_APP', true);
$root = dirname(__DIR__) . '/public_html';
require $root . '/includes/db.php';

$opt = getopt('', ['photos:', 'user:', 'hash:', 'name::']);
$photosDir = rtrim($opt['photos'] ?? '', '/');
if (!is_dir($photosDir) || empty($opt['user']) || empty($opt['hash'])) {
    fwrite(STDERR, "Uso: php tools/build-sql.php --photos=DIR --user=USUARIO --hash=HASH_BCRYPT\n");
    exit(1);
}

$content = require $root . '/data/content.php';

/** Texto SQL en una sola línea (los saltos de línea se escapan). */
function q(?string $s): string
{
    if ($s === null) {
        return 'NULL';
    }
    return "'" . strtr($s, ["\\" => "\\\\", "'" => "\\'", "\n" => "\\n", "\r" => "\\r", "\0" => "\\0", "\x1a" => "\\Z"]) . "'";
}
$slug = static fn ($photo) => is_array($photo) && !empty($photo['src']) ? preg_replace('/^db:/', '', $photo['src']) : null;

/* Texto alternativo de cada foto (descripción para accesibilidad) */
$alts = [
    'seydi-beso'          => 'Seydi lanzando un beso a la cámara',
    'juntos-al-anochecer' => 'Seydi y Alejandro sonriendo al anochecer bajo un cielo azul',
    'seydi-mirada'        => 'Retrato de Seydi con la mano en la mejilla',
    'beso-bajo-el-cielo'  => 'Alejandro besa la mejilla de Seydi bajo el cielo nocturno',
    'seydi-espejo'        => 'Seydi frente al espejo',
    'risas-y-brackets'    => 'Seydi riendo junto a Alejandro',
    'seydi-traviesa'      => 'Seydi sacando la lengua',
];

$out = [];
$out[] = '-- =============================================================================';
$out[] = '--  Experiencia de cumpleaños · Base de datos completa (estructura + contenido + fotos)';
$out[] = '--  Generado: ' . gmdate('Y-m-d H:i') . ' UTC';
$out[] = '--';
$out[] = '--  Cómo usarlo (elige UNA opción):';
$out[] = '--   A) Abre https://TU-SITIO/instalar.php y sigue los pasos (recomendado).';
$out[] = '--   B) phpMyAdmin → selecciona tu base de datos → Importar → este archivo.';
$out[] = '--';
$out[] = '--  Volver a importarlo restablece textos y fotos originales; NO borra los';
$out[] = '--  mensajes recibidos ni cambia la contraseña del panel si ya existe.';
$out[] = '-- =============================================================================';
$out[] = 'SET NAMES utf8mb4;';
$out[] = "SET time_zone = '+00:00';";

// Estructura (cada sentencia en una línea)
$schema = file_get_contents($root . '/database/schema.sql');
$schema = preg_replace('/^--.*$/m', '', $schema);
foreach (array_filter(array_map('trim', explode(';', $schema))) as $stmt) {
    $out[] = preg_replace('/\s+/', ' ', $stmt) . ';';
}

// Ajustes
foreach (flatten_settings($content) as $k => $v) {
    $out[] = 'REPLACE INTO `settings` (`k`, `v`) VALUES (' . q($k) . ', ' . q($v) . ');';
}

// Fotos
$total = 0;
foreach ($alts as $s => $alt) {
    $full = "$photosDir/$s.webp";
    $thumb = "$photosDir/$s-t.webp";
    if (!is_file($full)) {
        fwrite(STDERR, "Falta $full\n");
        exit(1);
    }
    [$w, $h] = getimagesize($full);
    $data = file_get_contents($full);
    $tdata = is_file($thumb) ? file_get_contents($thumb) : null;
    [$tw, $th] = $tdata ? getimagesize($thumb) : [0, 0];
    $total += strlen($data) + strlen((string) $tdata);
    $out[] = 'REPLACE INTO `photos` (`slug`, `alt`, `mime`, `width`, `height`, `data`, `thumb`, `thumb_width`, `thumb_height`, `bytes`) VALUES ('
        . q($s) . ', ' . q($alt) . ", 'image/webp', $w, $h, 0x" . bin2hex($data) . ', ' . ($tdata ? '0x' . bin2hex($tdata) : 'NULL')
        . ", $tw, $th, " . strlen($data) . ');';
}

// Listas
$out[] = 'DELETE FROM `story`;';
foreach (($content['story'] ?? []) as $i => $r) {
    $out[] = 'INSERT INTO `story` (`position`, `date_label`, `title`, `body`, `photo_slug`) VALUES ('
        . ($i + 1) * 10 . ', ' . q($r['date'] ?? '') . ', ' . q($r['title'] ?? '') . ', ' . q($r['text'] ?? '') . ', ' . q($slug($r['photo'] ?? null)) . ');';
}
$out[] = 'DELETE FROM `gallery`;';
foreach (($content['gallery'] ?? []) as $i => $r) {
    $out[] = 'INSERT INTO `gallery` (`position`, `photo_slug`, `caption`) VALUES ('
        . ($i + 1) * 10 . ', ' . q($slug($r)) . ', ' . q($r['caption'] ?? '') . ');';
}
$out[] = 'DELETE FROM `moments`;';
foreach (($content['moments'] ?? []) as $i => $r) {
    $out[] = 'INSERT INTO `moments` (`position`, `layout`, `date_label`, `title`, `body`, `author`, `place`, `photo_slug`) VALUES ('
        . ($i + 1) * 10 . ', ' . q($r['layout'] ?? 'split') . ', ' . q($r['date'] ?? '') . ', ' . q($r['title'] ?? '') . ', '
        . q($r['text'] ?? '') . ', ' . q($r['author'] ?? '') . ', ' . q($r['place'] ?? '') . ', ' . q($slug($r['photo'] ?? null)) . ');';
}

// Usuario del panel (no se sobrescribe si ya existe)
$out[] = 'INSERT IGNORE INTO `admins` (`username`, `display_name`, `password_hash`) VALUES ('
    . q($opt['user']) . ', ' . q((string) ($content['from_name'] ?? '')) . ', ' . q($opt['hash']) . ');';

$sql = implode("\n", $out) . "\n";
file_put_contents($root . '/database/seydi.sql', $sql);
file_put_contents($root . '/database/seydi.sql.gz', gzencode($sql, 9));
printf("seydi.sql: %.1f KB (fotos: %.1f KB) · seydi.sql.gz: %.1f KB\n", strlen($sql) / 1024, $total / 1024, filesize($root . '/database/seydi.sql.gz') / 1024);

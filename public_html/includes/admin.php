<?php
/**
 * Utilidades del panel /admin: sesión, inicio de sesión seguro, definición
 * de formularios y procesamiento de fotos subidas (conversión a WebP).
 */

defined('SEYDI_APP') || exit;

const ADMIN_IDLE_SECONDS = 7200;     // cierre de sesión tras 2 h sin actividad
const ADMIN_MAX_ATTEMPTS = 5;        // intentos antes del bloqueo temporal
const ADMIN_LOCK_MINUTES = 10;

/* ==========================================================================
   Sesión
   ========================================================================== */

function admin_user(PDO $pdo): ?array
{
    $id = (int) ($_SESSION['admin_id'] ?? 0);
    if ($id <= 0) {
        return null;
    }
    if (time() - (int) ($_SESSION['admin_seen'] ?? 0) > ADMIN_IDLE_SECONDS) {
        unset($_SESSION['admin_id'], $_SESSION['admin_seen']);
        return null;
    }
    $_SESSION['admin_seen'] = time();
    $st = $pdo->prepare('SELECT id, username, display_name, password_hash FROM admins WHERE id = ?');
    $st->execute([$id]);
    return $st->fetch() ?: null;
}

/** Devuelve '' si todo va bien o un mensaje de error. */
function admin_login(PDO $pdo, string $username, string $password): string
{
    $generic = 'Usuario o contraseña incorrectos.';
    $st = $pdo->prepare('SELECT * FROM admins WHERE username = ?');
    $st->execute([mb_strtolower(trim($username))]);
    $row = $st->fetch();
    if (!$row) {
        password_verify($password, '$2y$10$abcdefghijklmnopqrstuuJ0eY2vS1m8b1o0k7HcI0bR2pX6c1e3K'); // tiempo constante
        return $generic;
    }
    if ($row['locked_until'] && strtotime($row['locked_until'] . ' UTC') > time()) {
        return 'Demasiados intentos. Espera unos minutos.';
    }
    if (!password_verify($password, $row['password_hash'])) {
        $fails = (int) $row['failed_attempts'] + 1;
        $lock = $fails >= ADMIN_MAX_ATTEMPTS ? gmdate('Y-m-d H:i:s', time() + ADMIN_LOCK_MINUTES * 60) : null;
        $pdo->prepare('UPDATE admins SET failed_attempts = ?, locked_until = ? WHERE id = ?')
            ->execute([$lock ? 0 : $fails, $lock, $row['id']]);
        return $generic;
    }
    if (password_needs_rehash($row['password_hash'], PASSWORD_DEFAULT)) {
        $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?')->execute([password_hash($password, PASSWORD_DEFAULT), $row['id']]);
    }
    $pdo->prepare('UPDATE admins SET failed_attempts = 0, locked_until = NULL, last_login = UTC_TIMESTAMP() WHERE id = ?')->execute([$row['id']]);
    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $row['id'];
    $_SESSION['admin_seen'] = time();
    return '';
}

function flash(string $msg, string $type = 'ok'): void
{
    $_SESSION['flash'][] = [$type, $msg];
}

function take_flashes(): array
{
    $f = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $f;
}

/* ==========================================================================
   Formulario de ajustes (textos generales)
   type: text | textarea | lines | photo | number | bool | datetime
   ========================================================================== */

function settings_form(): array
{
    return [
        'Personas y fecha' => [
            'her_name' => ['Nombre de ella', 'text'],
            'from_name' => ['Tu nombre (creador)', 'text'],
            'signature' => ['Firma manuscrita', 'text'],
            'monogram' => ['Inicial del sello', 'text'],
            'age' => ['Años que cumple', 'text'],
            'birthday' => ['Fecha y hora del cumpleaños (AAAA-MM-DD HH:MM)', 'datetime'],
            'timezone' => ['Zona horaria', 'text'],
            'celebration_hours' => ['Horas que dura el modo "¡Hoy es tu día!"', 'number'],
        ],
        'Introducción' => [
            'intro.lines' => ['Frases (una por línea)', 'lines'],
            'intro.button' => ['Texto del botón', 'text'],
            'intro.hint' => ['Texto pequeño bajo el botón', 'text'],
            'intro.ring_text' => ['Texto que gira alrededor del sello', 'text'],
        ],
        'Portada' => [
            'hero.eyebrow' => ['Texto superior', 'text'],
            'hero.title' => ['Título grande', 'text'],
            'hero.message' => ['Mensaje', 'textarea'],
            'hero.photo' => ['Foto principal', 'photo'],
        ],
        'Introducciones de sección' => [
            'story_intro' => ['Nuestra historia', 'textarea'],
            'gallery_intro' => ['Galería', 'textarea'],
            'moments_intro' => ['Momentos', 'textarea'],
        ],
        'Carta' => [
            'letter.envelope_label' => ['Texto del sobre', 'text'],
            'letter.envelope_hint' => ['Indicación bajo el sobre', 'text'],
            'letter.greeting' => ['Saludo', 'text'],
            'letter.body' => ['Carta (párrafos separados por una línea vacía)', 'textarea'],
            'letter.closing' => ['Despedida', 'text'],
        ],
        'Reloj' => [
            'countdown.title_before' => ['Título antes de la fecha', 'text'],
            'countdown.text_before' => ['Texto antes de la fecha', 'text'],
            'countdown.title_after' => ['Título el día del cumpleaños', 'text'],
            'countdown.text_after' => ['Texto el día del cumpleaños', 'textarea'],
            'countdown.title_past' => ['Título después', 'text'],
            'countdown.text_past' => ['Texto después', 'text'],
        ],
        'Final' => [
            'final.eyebrow' => ['Texto superior', 'text'],
            'final.lines' => ['Frases finales (una por línea)', 'lines'],
            'final.closing' => ['Cierre', 'textarea'],
            'final.photo' => ['Foto de fondo', 'photo'],
        ],
        'Sorpresa y respuesta' => [
            'secret.title' => ['Título del secreto (3 estrellas)', 'text'],
            'secret.message' => ['Mensaje secreto', 'textarea'],
            'reply.enabled' => ['Mostrar formulario de respuesta', 'bool'],
            'reply.title' => ['Título del formulario', 'text'],
            'reply.placeholder' => ['Texto de ayuda', 'text'],
            'reply.button' => ['Botón', 'text'],
            'reply.thanks' => ['Mensaje al enviar', 'text'],
        ],
        'Música' => [
            'music.spotify_track_id' => ['ID de la canción en Spotify', 'text'],
            'music.local_file' => ['Archivo local en /assets/audio (opcional, tiene prioridad)', 'text'],
            'music.title' => ['Título en el reproductor', 'text'],
            'music.artist' => ['Subtítulo en el reproductor', 'text'],
            'music.volume' => ['Volumen inicial (0 a 1, audio local)', 'text'],
        ],
        'Compartir (SEO)' => [
            'seo.title' => ['Título de la pestaña', 'text'],
            'seo.description' => ['Descripción', 'textarea'],
            'seo.share_image' => ['Imagen al compartir', 'photo'],
        ],
    ];
}

/** Valida y normaliza un valor de ajuste. Devuelve [valor, error]. */
function settings_clean(string $key, string $type, $raw): array
{
    $v = clean_text(is_string($raw) ? $raw : '', 20000);
    switch ($type) {
        case 'bool':
            return [$raw ? '1' : '0', ''];
        case 'number':
            return [(string) max(1, min(720, (int) $v)), ''];
        case 'datetime':
            return DateTime::createFromFormat('!Y-m-d H:i', $v) ? [$v, ''] : [$v, 'Fecha inválida (usa AAAA-MM-DD HH:MM).'];
        case 'photo':
            return [$v === '' || preg_match('/^[a-z0-9-]{1,80}$/', $v) ? $v : '', ''];
    }
    if ($key === 'timezone' && !in_array($v, timezone_identifiers_list(), true)) {
        return [$v, 'Zona horaria inválida (ej. America/Managua).'];
    }
    if ($key === 'music.spotify_track_id' && $v !== '' && !preg_match('/^[A-Za-z0-9]{22}$/', $v)) {
        if (preg_match('#track/([A-Za-z0-9]{22})#', $v, $m)) {
            return [$m[1], ''];
        }
        return [$v, 'El ID de Spotify debe tener 22 caracteres (o pega el enlace de la canción).'];
    }
    return [$v, ''];
}

/* ==========================================================================
   Listas editables
   ========================================================================== */

function list_defs(): array
{
    return [
        'story' => [
            'title' => 'Nuestra historia', 'singular' => 'capítulo',
            'fields' => [
                'date_label' => ['Etiqueta / fecha', 'text'],
                'title' => ['Título', 'text'],
                'body' => ['Texto', 'textarea'],
                'photo_slug' => ['Foto', 'photo'],
            ],
        ],
        'gallery' => [
            'title' => 'Galería', 'singular' => 'foto',
            'fields' => [
                'photo_slug' => ['Foto', 'photo'],
                'caption' => ['Pie de foto', 'text'],
            ],
        ],
        'moments' => [
            'title' => 'Momentos', 'singular' => 'momento',
            'fields' => [
                'layout' => ['Diseño', 'layout'],
                'date_label' => ['Etiqueta / fecha', 'text'],
                'title' => ['Título', 'text'],
                'body' => ['Texto', 'textarea'],
                'author' => ['Autor (solo cita)', 'text'],
                'place' => ['Lugar (solo ticket)', 'text'],
                'photo_slug' => ['Foto', 'photo'],
            ],
        ],
    ];
}

const MOMENT_LAYOUTS = [
    'polaroid' => 'Polaroid', 'split' => 'Foto + texto', 'quote' => 'Cita',
    'flip' => 'Tarjeta que se gira', 'ticket' => 'Entrada (ticket)',
];

/* ==========================================================================
   Fotos: validación y conversión a WebP (con GD)
   ========================================================================== */

/** Reescala a un máximo de $max px y devuelve [binario, ancho, alto, mime]. */
function encode_image($src, int $max, int $quality): array
{
    $w = imagesx($src);
    $h = imagesy($src);
    $scale = min(1, $max / max($w, $h));
    $nw = max(1, (int) round($w * $scale));
    $nh = max(1, (int) round($h * $scale));
    $dst = imagecreatetruecolor($nw, $nh);
    imagecopyresampled($dst, $src, 0, 0, 0, 0, $nw, $nh, $w, $h);
    ob_start();
    if (function_exists('imagewebp')) {
        imagewebp($dst, null, $quality);
        $mime = 'image/webp';
    } else {
        imagejpeg($dst, null, $quality + 4);
        $mime = 'image/jpeg';
    }
    $bin = (string) ob_get_clean();
    imagedestroy($dst);
    return [$bin, $nw, $nh, $mime];
}

/**
 * Procesa una foto subida: valida tipo y tamaño, corrige la orientación,
 * elimina metadatos (GPS incluido) y crea versión grande + miniatura.
 * Devuelve un array con los datos o un string con el error.
 */
function process_upload(array $file)
{
    if (!function_exists('imagecreatefromstring')) {
        return 'El servidor no tiene la extensión GD para procesar imágenes.';
    }
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK || !is_uploaded_file($file['tmp_name'])) {
        return 'No se recibió la foto (¿pesa más de lo permitido por el servidor?).';
    }
    if ($file['size'] > 12 * 1024 * 1024) {
        return 'La foto pesa más de 12 MB.';
    }
    $info = @getimagesize($file['tmp_name']);
    if (!$info || !in_array($info[2], [IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_WEBP], true)) {
        return 'Formato no válido. Usa JPG, PNG o WebP.';
    }
    if ($info[0] * $info[1] > 40000000) {
        return 'La foto tiene demasiados píxeles (máx. 40 MP).';
    }
    @ini_set('memory_limit', '256M');
    $src = @imagecreatefromstring((string) file_get_contents($file['tmp_name']));
    if (!$src) {
        return 'No se pudo leer la imagen.';
    }
    // Orientación EXIF de fotos de móvil (iPhone guarda la foto "de lado" + una etiqueta).
    // Se lee sin depender de la extensión exif, que InfinityFree puede no tener.
    if ($info[2] === IMAGETYPE_JPEG) {
        $rot = [3 => 180, 6 => -90, 8 => 90][jpeg_orientation($file['tmp_name'])] ?? 0;
        if ($rot) {
            $src = imagerotate($src, $rot, 0);
        }
    }
    [$data, $w, $h, $mime] = encode_image($src, 1600, 80);
    [$thumb, $tw, $th] = encode_image($src, 800, 74);
    imagedestroy($src);
    return compact('data', 'w', 'h', 'mime', 'thumb', 'tw', 'th');
}

/** Lee la etiqueta EXIF "Orientation" (1–8) de un JPEG sin la extensión exif. */
function jpeg_orientation(string $path): int
{
    $fh = @fopen($path, 'rb');
    if (!$fh) {
        return 1;
    }
    $d = (string) fread($fh, 262144);
    fclose($fh);
    $len = strlen($d);
    if ($len < 4 || substr($d, 0, 2) !== "\xFF\xD8") {
        return 1;
    }
    $pos = 2;
    while ($pos + 4 <= $len && $d[$pos] === "\xFF") {
        $marker = ord($d[$pos + 1]);
        $size = unpack('n', substr($d, $pos + 2, 2))[1];
        if ($marker === 0xE1 && substr($d, $pos + 4, 6) === "Exif\0\0") {
            $t = $pos + 10;
            $le = substr($d, $t, 2) === 'II';
            $u16 = static fn ($o) => $o + 2 <= $len ? unpack($le ? 'v' : 'n', substr($d, $o, 2))[1] : 0;
            $u32 = static fn ($o) => $o + 4 <= $len ? unpack($le ? 'V' : 'N', substr($d, $o, 4))[1] : 0;
            $ifd = $t + $u32($t + 4);
            $n = $u16($ifd);
            for ($i = 0; $i < $n && $i < 200; $i++) {
                $e = $ifd + 2 + $i * 12;
                if ($u16($e) === 0x0112) {
                    $v = $u16($e + 8);
                    return $v >= 1 && $v <= 8 ? $v : 1;
                }
            }
            return 1;
        }
        if ($marker === 0xDA || $size < 2) {
            break;
        }
        $pos += 2 + $size;
    }
    return 1;
}

/** Gira 90° una foto guardada (versión grande y miniatura). */
function rotate_photo(PDO $pdo, string $slug, bool $clockwise): bool
{
    $st = $pdo->prepare('SELECT data FROM photos WHERE slug = ?');
    $st->execute([$slug]);
    $data = $st->fetchColumn();
    if ($data === false || !function_exists('imagecreatefromstring')) {
        return false;
    }
    $src = @imagecreatefromstring(is_resource($data) ? stream_get_contents($data) : (string) $data);
    if (!$src) {
        return false;
    }
    $src = imagerotate($src, $clockwise ? -90 : 90, 0);
    [$full, $w, $h, $mime] = encode_image($src, 1600, 86);
    [$thumb, $tw, $th] = encode_image($src, 800, 76);
    imagedestroy($src);
    $up = $pdo->prepare('UPDATE photos SET data = ?, thumb = ?, mime = ?, width = ?, height = ?, thumb_width = ?, thumb_height = ?, bytes = ? WHERE slug = ?');
    $up->bindValue(1, $full, PDO::PARAM_LOB);
    $up->bindValue(2, $thumb, PDO::PARAM_LOB);
    $up->bindValue(3, $mime);
    foreach ([4 => $w, 5 => $h, 6 => $tw, 7 => $th, 8 => strlen($full)] as $i => $v) {
        $up->bindValue($i, $v, PDO::PARAM_INT);
    }
    $up->bindValue(9, $slug);
    return $up->execute();
}

/** Crea un slug único a partir de un texto. */
function make_slug(PDO $pdo, string $text): string
{
    $s = mb_strtolower($text);
    $s = strtr($s, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    $s = trim((string) preg_replace('/[^a-z0-9]+/', '-', $s), '-');
    $s = substr($s !== '' ? $s : 'foto', 0, 60);
    $base = $s;
    $i = 2;
    $st = $pdo->prepare('SELECT 1 FROM photos WHERE slug = ?');
    while (true) {
        $st->execute([$s]);
        if (!$st->fetchColumn()) {
            return $s;
        }
        $s = $base . '-' . $i++;
    }
}

/** ¿Dónde se usa una foto? (para no borrar una en uso) */
function photo_usage(PDO $pdo, string $slug): array
{
    $uses = [];
    $st = $pdo->prepare("SELECT k FROM settings WHERE v = ? AND k IN ('hero.photo','final.photo','seo.share_image')");
    $st->execute([$slug]);
    foreach ($st->fetchAll(PDO::FETCH_COLUMN) as $k) {
        $uses[] = $k;
    }
    foreach (['story' => 'Historia', 'gallery' => 'Galería', 'moments' => 'Momentos'] as $t => $label) {
        $st = $pdo->prepare("SELECT COUNT(*) FROM `$t` WHERE photo_slug = ?");
        $st->execute([$slug]);
        if ((int) $st->fetchColumn() > 0) {
            $uses[] = $label;
        }
    }
    return $uses;
}

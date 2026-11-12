<?php
/**
 * Funciones auxiliares: escape, recursos, imágenes, fechas, CSRF y almacenamiento.
 */

defined('SEYDI_APP') || exit;

/* ==========================================================================
   Escape (anti-XSS). TODO texto dinámico pasa por aquí.
   ========================================================================== */

/** Escapa texto para HTML y atributos. */
function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Devuelve un valor anidado del contenido con valor por defecto. ('letter.body') */
function c(string $path, $default = '')
{
    global $CONTENT;
    $node = $CONTENT;
    foreach (explode('.', $path) as $key) {
        if (!is_array($node) || !array_key_exists($key, $node)) {
            return $default;
        }
        $node = $node[$key];
    }
    return $node;
}

/** Convierte texto plano con líneas vacías en párrafos <p> escapados. */
function paragraphs(string $text, string $class = ''): string
{
    $text = str_replace(["\r\n", "\r"], "\n", trim($text));
    $blocks = preg_split("/\n\s*\n/", $text) ?: [];
    $out = '';
    $cls = $class !== '' ? ' class="' . e($class) . '"' : '';
    foreach ($blocks as $block) {
        $block = trim($block);
        if ($block === '') {
            continue;
        }
        $out .= '<p' . $cls . '>' . nl2br(e($block), false) . '</p>';
    }
    return $out;
}

/** JSON seguro para incrustar dentro de <script type="application/json">. */
function json_for_html($data): string
{
    return (string) json_encode(
        $data,
        JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
}

/* ==========================================================================
   Recursos estáticos
   ========================================================================== */

/** URL relativa de un recurso con versión según fecha de modificación (cache-busting). */
function asset(string $path): string
{
    $path = ltrim($path, '/');
    $file = APP_ROOT . '/assets/' . $path;
    $v = is_file($file) ? (string) filemtime($file) : APP_VERSION;
    return 'assets/' . str_replace('%2F', '/', rawurlencode($path)) . '?v=' . $v;
}

/**
 * Normaliza una ruta de imagen del contenido y evita salir de /assets/images.
 * Devuelve null si la ruta no es válida.
 */
function image_path(string $src): ?string
{
    $src = str_replace('\\', '/', trim($src));
    $src = ltrim($src, '/');
    if (strpos($src, 'assets/images/') === 0) {
        $src = substr($src, strlen('assets/images/'));
    }
    if ($src === '' || strpos($src, '..') !== false || !preg_match('#^[\w\-./ ]+\.(jpe?g|png|webp|avif|gif|svg)$#i', $src)) {
        return null;
    }
    return $src;
}

/** Dimensiones intrínsecas de una imagen local (para evitar saltos de diseño). */
function image_dims(string $rel): array
{
    static $cache = [];
    if (isset($cache[$rel])) {
        return $cache[$rel];
    }
    $file = APP_ROOT . '/assets/images/' . $rel;
    $dims = [1200, 1500];
    if (is_file($file)) {
        if (preg_match('/\.svg$/i', $rel)) {
            $head = (string) file_get_contents($file, false, null, 0, 2048);
            if (preg_match('/viewBox="\s*[-\d.]+\s+[-\d.]+\s+([\d.]+)\s+([\d.]+)\s*"/i', $head, $m)) {
                $dims = [(int) round((float) $m[1]), (int) round((float) $m[2])];
            }
        } else {
            $info = @getimagesize($file);
            if ($info && $info[0] > 0 && $info[1] > 0) {
                $dims = [(int) $info[0], (int) $info[1]];
            }
        }
    }
    return $cache[$rel] = $dims;
}

/**
 * Pinta una etiqueta <img> (o <picture> si existe una versión .webp gemela)
 * con ancho/alto explícitos, lazy loading y decodificación asíncrona.
 *
 * @param array $photo ['src' => ..., 'alt' => ...]
 * @param array $opts  class, sizes, eager (bool), attrs (array extra)
 */
function img(array $photo, array $opts = []): string
{
    $rel = image_path((string) ($photo['src'] ?? ''));
    $alt = (string) ($photo['alt'] ?? '');
    if ($rel === null) {
        $rel = 'provisionales/foto-01.svg';
    }
    [$w, $h] = image_dims($rel);
    $eager = !empty($opts['eager']);
    $attrs = [
        'src'      => asset('images/' . $rel),
        'alt'      => $alt,
        'width'    => $w,
        'height'   => $h,
        'loading'  => $eager ? 'eager' : 'lazy',
        'decoding' => $eager ? 'sync' : 'async',
    ];
    if ($eager) {
        $attrs['fetchpriority'] = 'high';
    }
    if (!empty($opts['class'])) {
        $attrs['class'] = $opts['class'];
    }
    if (!empty($opts['sizes'])) {
        $attrs['sizes'] = $opts['sizes'];
    }
    foreach (($opts['attrs'] ?? []) as $k => $v) {
        $attrs[$k] = $v;
    }
    $html = '<img';
    foreach ($attrs as $k => $v) {
        $html .= ' ' . e($k) . '="' . e($v) . '"';
    }
    $html .= '>';

    // Si existe "foto.webp" junto a "foto.jpg", el navegador usará la WebP.
    if (preg_match('/\.(jpe?g|png)$/i', $rel)) {
        $webp = preg_replace('/\.(jpe?g|png)$/i', '.webp', $rel);
        if (is_file(APP_ROOT . '/assets/images/' . $webp)) {
            $html = '<picture><source type="image/webp" srcset="' . e(asset('images/' . $webp)) . '">' . $html . '</picture>';
        }
    }
    return $html;
}

/** Proporción "w / h" para CSS aspect-ratio. */
function ratio(array $photo): string
{
    $rel = image_path((string) ($photo['src'] ?? '')) ?? 'provisionales/foto-01.svg';
    [$w, $h] = image_dims($rel);
    return $w . ' / ' . $h;
}

/** URL absoluta (si hay site_url) de una imagen para Open Graph. */
function absolute_image(string $src): string
{
    global $CONFIG;
    $rel = image_path($src);
    if ($rel === null) {
        return '';
    }
    $base = rtrim((string) $CONFIG['site_url'], '/');
    return ($base !== '' ? $base . '/' : '') . 'assets/images/' . str_replace('%2F', '/', rawurlencode($rel));
}

/** Busca el archivo de música local; devuelve la URL o null. */
function local_music(): ?string
{
    $name = basename((string) c('music.local_file', ''));
    if ($name === '' || !preg_match('/^[\w\-. ]+\.(mp3|m4a|aac|ogg|oga|opus|wav|webm)$/i', $name)) {
        return null;
    }
    return is_file(APP_ROOT . '/assets/audio/' . $name) ? asset('audio/' . $name) : null;
}

/* ==========================================================================
   Fechas en español (sin depender de la extensión intl)
   ========================================================================== */

function birthday_datetime(): DateTimeImmutable
{
    $raw = (string) c('birthday', '');
    $dt = DateTimeImmutable::createFromFormat('!Y-m-d H:i', $raw)
        ?: DateTimeImmutable::createFromFormat('!Y-m-d', $raw);
    return $dt ?: new DateTimeImmutable('tomorrow');
}

function spanish_date(DateTimeInterface $d): string
{
    $months = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio', 'julio', 'agosto',
        'septiembre', 'octubre', 'noviembre', 'diciembre'];
    return $d->format('j') . ' de ' . $months[(int) $d->format('n') - 1] . ' de ' . $d->format('Y');
}

function roman(int $n): string
{
    $map = ['M' => 1000, 'CM' => 900, 'D' => 500, 'CD' => 400, 'C' => 100, 'XC' => 90,
        'L' => 50, 'XL' => 40, 'X' => 10, 'IX' => 9, 'V' => 5, 'IV' => 4, 'I' => 1];
    $out = '';
    foreach ($map as $r => $v) {
        while ($n >= $v) {
            $out .= $r;
            $n -= $v;
        }
    }
    return $out;
}

/* ==========================================================================
   Seguridad: CSRF, código de acceso
   ========================================================================== */

function csrf_token(): string
{
    if (empty($_SESSION['csrf']) || !is_string($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf'];
}

function csrf_valid($token): bool
{
    return is_string($token) && !empty($_SESSION['csrf']) && hash_equals($_SESSION['csrf'], $token);
}

function access_required(): bool
{
    global $CONFIG;
    return (string) $CONFIG['access_code'] !== '' && empty($_SESSION['access_ok']);
}

/** Procesa el formulario del código de acceso. Devuelve un mensaje de error o ''. */
function handle_access_attempt(): string
{
    global $CONFIG;
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST' || !isset($_POST['access_code'])) {
        return '';
    }
    $_SESSION['access_attempts'] = (int) ($_SESSION['access_attempts'] ?? 0);
    $_SESSION['access_lock'] = (int) ($_SESSION['access_lock'] ?? 0);
    if ($_SESSION['access_lock'] > time()) {
        return 'Demasiados intentos. Espera un minuto.';
    }
    if (!csrf_valid($_POST['csrf'] ?? null)) {
        return 'La sesión caducó. Inténtalo de nuevo.';
    }
    $given = preg_replace('/\s+/', '', (string) $_POST['access_code']);
    $expected = preg_replace('/\s+/', '', (string) $CONFIG['access_code']);
    if (is_string($given) && mb_strlen($given) <= 100 && hash_equals(mb_strtolower($expected), mb_strtolower($given))) {
        session_regenerate_id(true);
        $_SESSION['access_ok'] = true;
        $_SESSION['access_attempts'] = 0;
        header('Location: ./', true, 303);
        exit;
    }
    $_SESSION['access_attempts']++;
    if ($_SESSION['access_attempts'] >= 5) {
        $_SESSION['access_lock'] = time() + 60;
        $_SESSION['access_attempts'] = 0;
    }
    return 'Ese no es el código. Inténtalo otra vez.';
}

/* ==========================================================================
   Almacenamiento privado (respuestas)
   ========================================================================== */

function storage_dir(): string
{
    return APP_ROOT . '/storage';
}

/** Limpia texto de entrada: UTF-8 válido, sin caracteres de control, recortado. */
function clean_text($value, int $max): string
{
    if (!is_string($value)) {
        return '';
    }
    if (!mb_check_encoding($value, 'UTF-8')) {
        $value = mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
    $value = str_replace(["\r\n", "\r"], "\n", $value);
    $value = (string) preg_replace('/[^\P{C}\n\t]/u', '', $value);
    $value = trim($value);
    return mb_substr($value, 0, $max);
}

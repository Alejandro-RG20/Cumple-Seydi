<?php
/**
 * Arranque de la aplicación: errores, configuración, contenido, sesión y
 * cabeceras de seguridad. Lo incluyen index.php y api/mensaje.php.
 *
 * Compatible con PHP 7.4+ (InfinityFree usa PHP 8.x).
 */

defined('SEYDI_APP') || exit;

define('APP_ROOT', dirname(__DIR__));
define('APP_VERSION', '1.0.0');

/* ---------- Configuración técnica ---------------------------------------- */
$configFile = APP_ROOT . '/config/config.php';
if (!is_file($configFile)) {
    $configFile = APP_ROOT . '/config/config.example.php';
}
$CONFIG = require $configFile;
if (!is_array($CONFIG)) {
    $CONFIG = [];
}
$CONFIG += [
    'site_url'         => '',
    'access_code'      => '',
    'access_hint'      => '',
    'indexable'        => false,
    'debug'            => false,
    'reply_rate_limit' => 5,
    'db'               => [],
];

/* ---------- Errores -------------------------------------------------------- */
error_reporting(E_ALL);
ini_set('display_errors', $CONFIG['debug'] ? '1' : '0');
ini_set('log_errors', '1');

require __DIR__ . '/functions.php';
require __DIR__ . '/db.php';

/* ---------- Contenido: MySQL (principal) o data/content.php (respaldo) --- */
$CONTENT = require APP_ROOT . '/data/content.php';
if (!is_array($CONTENT)) {
    $CONTENT = [];
}
$CONTENT_SOURCE = 'archivo';
if (!defined('SEYDI_SKIP_CONTENT')) {
    $pdo = db();
    if ($pdo && db_installed($pdo)) {
        try {
            $CONTENT = content_from_db($pdo, $CONTENT);
            $CONTENT_SOURCE = 'mysql';
        } catch (Throwable $e) {
            error_log('[seydi] Error leyendo contenido de MySQL: ' . $e->getMessage());
        }
    }
    unset($pdo);
}

$tz = isset($CONTENT['timezone']) ? (string) $CONTENT['timezone'] : 'UTC';
if (!in_array($tz, timezone_identifiers_list(), true)) {
    $tz = 'UTC';
}
date_default_timezone_set($tz);

/* ---------- Sesión segura (CSRF, código de acceso, límite de envíos) ----- */
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    session_name('seydi_sid');
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'secure'   => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    // Las peticiones de solo lectura (fotos) no bloquean la sesión
    session_start(defined('SEYDI_READONLY_SESSION') ? ['read_and_close' => true] : []);
}

/* ---------- Cabeceras de seguridad (refuerzo de .htaccess) --------------- */
if (!headers_sent()) {
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('X-Frame-Options: SAMEORIGIN');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=()');
    // Spotify solo se permite para su reproductor oficial integrado (modo música por defecto).
    $spotify = 'https://open.spotify.com https://*.spotify.com https://*.scdn.co';
    header("Content-Security-Policy: default-src 'self'; script-src 'self' {$spotify}; "
        . "frame-src {$spotify}; style-src 'self' 'unsafe-inline'; img-src 'self' data: https://*.scdn.co; "
        . "font-src 'self'; media-src 'self'; connect-src 'self' {$spotify}; base-uri 'self'; form-action 'self'; "
        . "frame-ancestors 'self'; object-src 'none'");
}

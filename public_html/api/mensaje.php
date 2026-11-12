<?php
/**
 * Recibe la respuesta privada de ella y la guarda en /storage/mensajes.json
 *
 * Seguridad:
 *  - Solo POST, token CSRF ligado a la sesión, campo trampa (honeypot)
 *  - Tiempo mínimo de llenado, límite de envíos por sesión y por IP (hash)
 *  - Validación de longitud y limpieza de caracteres de control
 *  - Escritura atómica con bloqueo (flock) en una carpeta inaccesible por web
 *  - No se guarda la IP en claro: solo un hash truncado para el límite
 *
 * Responde JSON si la petición viene de JavaScript; si no, redirige (PRG).
 */

define('SEYDI_APP', true);
require dirname(__DIR__) . '/includes/bootstrap.php';

$wantsJson = stripos((string) ($_SERVER['HTTP_ACCEPT'] ?? ''), 'application/json') !== false;

function reply_out(bool $ok, string $msg, int $status, bool $json): void
{
    http_response_code($status);
    header('Cache-Control: no-store');
    if ($json) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $msg], JSON_UNESCAPED_UNICODE);
    } else {
        header('Location: ../?respuesta=' . ($ok ? 'ok' : 'error') . '#final', true, 303);
    }
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Allow: POST');
    reply_out(false, 'Método no permitido.', 405, true);
}
if (!c('reply.enabled', true)) {
    reply_out(false, 'El formulario está desactivado.', 403, $wantsJson);
}
if (access_required()) {
    reply_out(false, 'Acceso no autorizado.', 403, $wantsJson);
}
if (!csrf_valid($_POST['csrf'] ?? null)) {
    reply_out(false, 'La sesión caducó. Recarga la página e inténtalo de nuevo.', 419, $wantsJson);
}

// Honeypot: los bots suelen llenarlo. Respondemos "ok" sin guardar nada.
if (!empty($_POST['website'])) {
    reply_out(true, 'Gracias.', 200, $wantsJson);
}

// Tiempo mínimo humano (3 s desde que se cargó la página).
$t = (int) ($_POST['t'] ?? 0);
if ($t <= 0 || time() - $t < 3) {
    reply_out(false, 'Tómate un momento y vuelve a intentarlo.', 429, $wantsJson);
}

$message = clean_text($_POST['message'] ?? '', 1500);
if (mb_strlen($message) < 2) {
    reply_out(false, 'Escribe al menos unas palabras.', 422, $wantsJson);
}

// Límite por sesión.
$limit = max(1, (int) $CONFIG['reply_rate_limit']);
$now = time();
$_SESSION['reply_times'] = array_values(array_filter(
    (array) ($_SESSION['reply_times'] ?? []),
    static function ($ts) use ($now) { return is_int($ts) && $ts > $now - 3600; }
));
if (count($_SESSION['reply_times']) >= $limit) {
    reply_out(false, 'Has enviado varios mensajes seguidos. Inténtalo más tarde.', 429, $wantsJson);
}

$dir = storage_dir();
if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
    reply_out(false, 'No se pudo guardar el mensaje (almacenamiento).', 500, $wantsJson);
}

$ipHash = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? '') . '|' . __FILE__), 0, 16);
$file = $dir . '/mensajes.json';
$fh = @fopen($file, 'c+');
if (!$fh) {
    reply_out(false, 'No se pudo guardar el mensaje.', 500, $wantsJson);
}
flock($fh, LOCK_EX);
$raw = stream_get_contents($fh);
$data = json_decode($raw ?: '[]', true);
if (!is_array($data)) {
    $data = [];
}

// Límite por IP (también cubre sesiones nuevas).
$recentFromIp = 0;
foreach ($data as $row) {
    if (($row['ip'] ?? '') === $ipHash && strtotime((string) ($row['fecha'] ?? '')) > $now - 3600) {
        $recentFromIp++;
    }
}
if ($recentFromIp >= $limit) {
    flock($fh, LOCK_UN);
    fclose($fh);
    reply_out(false, 'Has enviado varios mensajes seguidos. Inténtalo más tarde.', 429, $wantsJson);
}

$data[] = [
    'fecha'   => date(DATE_ATOM),
    'mensaje' => $message,
    'ip'      => $ipHash,
];
// Conserva como máximo los 500 mensajes más recientes.
$data = array_slice($data, -500);

ftruncate($fh, 0);
rewind($fh);
$ok = fwrite($fh, (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
fflush($fh);
flock($fh, LOCK_UN);
fclose($fh);

if (!$ok) {
    reply_out(false, 'No se pudo guardar el mensaje.', 500, $wantsJson);
}
$_SESSION['reply_times'][] = $now;
reply_out(true, (string) c('reply.thanks', 'Recibido.'), 200, $wantsJson);

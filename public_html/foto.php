<?php
/**
 * Sirve las fotos guardadas en MySQL (tabla photos).
 *   foto.php?f=slug        → versión grande (WebP)
 *   foto.php?f=slug&s=t    → miniatura ligera
 * Caché larga en el navegador (la URL lleva ?v= que cambia al editar la foto).
 */

define('SEYDI_APP', true);
define('SEYDI_SKIP_CONTENT', true);
define('SEYDI_READONLY_SESSION', true);
require __DIR__ . '/includes/bootstrap.php';

$slug = (string) ($_GET['f'] ?? '');
$thumb = ($_GET['s'] ?? '') === 't';
if (!preg_match('/^[a-z0-9-]{1,80}$/', $slug)) {
    http_response_code(404);
    exit;
}
// Si la página tiene código de acceso, las fotos también lo exigen.
if (access_required()) {
    http_response_code(403);
    exit;
}
$pdo = db();
if (!$pdo) {
    http_response_code(503);
    exit;
}

$col = $thumb ? 'COALESCE(thumb, data)' : 'data';
$st = $pdo->prepare("SELECT mime, {$col} AS blob_data, UNIX_TIMESTAMP(updated_at) AS ts FROM photos WHERE slug = ?");
$st->execute([$slug]);
$meta = $st->fetch();
if (!$meta || $meta['blob_data'] === null) {
    http_response_code(404);
    exit;
}
$blob = is_resource($meta['blob_data']) ? stream_get_contents($meta['blob_data']) : (string) $meta['blob_data'];
$mime = in_array($meta['mime'], ['image/webp', 'image/jpeg', 'image/png'], true) ? $meta['mime'] : 'application/octet-stream';

$etag = '"' . md5($slug . '|' . $meta['ts'] . '|' . ($thumb ? 't' : 'f')) . '"';
header_remove('Pragma');
header('Content-Type: ' . $mime);
header('ETag: ' . $etag);
header('Cache-Control: ' . ((string) $CONFIG['access_code'] !== '' ? 'private' : 'public') . ', max-age=31536000, immutable');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', (int) $meta['ts']) . ' GMT');
if (trim((string) ($_SERVER['HTTP_IF_NONE_MATCH'] ?? '')) === $etag) {
    http_response_code(304);
    exit;
}
header('Content-Length: ' . strlen($blob));
echo $blob;

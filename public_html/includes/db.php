<?php
/**
 * Base de datos MySQL (PDO): conexión, lectura del contenido completo,
 * metadatos de fotos y guardado de mensajes.
 *
 * Tablas (ver database/seydi.sql):
 *   settings  → textos y ajustes (clave/valor)
 *   photos    → fotos en WebP (versión grande + miniatura) como BLOB
 *   story     → capítulos de "Nuestra historia"
 *   gallery   → fotos de la galería y su orden
 *   moments   → "Momentos que quiero recordar"
 *   messages  → mensajes que ella deja en el formulario
 *   admins    → usuarios del panel /admin
 */

defined('SEYDI_APP') || exit;

/** Claves de ajustes que contienen varias líneas (una frase por línea). */
const SETTINGS_LINE_KEYS = ['intro.lines', 'final.lines'];
/** Claves de ajustes que apuntan a una foto (guardan el "slug" de la foto). */
const SETTINGS_PHOTO_KEYS = ['hero.photo', 'final.photo', 'seo.share_image'];

/** Conexión única. Devuelve null si no está configurada o falla (la web usa el respaldo). */
function db(): ?PDO
{
    static $pdo = false;
    if ($pdo !== false) {
        return $pdo;
    }
    global $CONFIG;
    $cfg = $CONFIG['db'] ?? [];
    if (empty($cfg['host']) || empty($cfg['name']) || empty($cfg['user'])) {
        return $pdo = null;
    }
    try {
        $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4', $cfg['host'], (int) ($cfg['port'] ?? 3306), $cfg['name']);
        $pdo = new PDO($dsn, (string) $cfg['user'], (string) ($cfg['pass'] ?? ''), [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::ATTR_TIMEOUT            => 5,
        ]);
        $pdo->exec("SET time_zone = '+00:00'");
    } catch (Throwable $e) {
        error_log('[seydi] No se pudo conectar a MySQL: ' . $e->getMessage());
        $pdo = null;
    }
    return $pdo;
}

/** ¿Las tablas existen y tienen contenido? */
function db_installed(?PDO $pdo): bool
{
    if (!$pdo) {
        return false;
    }
    try {
        return (int) $pdo->query('SELECT COUNT(*) FROM settings')->fetchColumn() > 0;
    } catch (Throwable $e) {
        return false;
    }
}

/** Metadatos de una foto por slug (se cargan todas una vez, sin los BLOB). */
function photo_meta(string $slug): ?array
{
    static $all = null;
    if ($all === null) {
        $all = [];
        $pdo = db();
        if ($pdo) {
            try {
                $rows = $pdo->query('SELECT id, slug, alt, width, height, thumb_width, thumb_height, UNIX_TIMESTAMP(updated_at) AS ts FROM photos')->fetchAll();
                foreach ($rows as $r) {
                    $all[$r['slug']] = [
                        'id' => (int) $r['id'], 'slug' => $r['slug'], 'alt' => (string) $r['alt'],
                        'w' => (int) $r['width'], 'h' => (int) $r['height'],
                        'tw' => (int) $r['thumb_width'], 'th' => (int) $r['thumb_height'],
                        'v' => base_convert((string) $r['ts'], 10, 36),
                    ];
                }
            } catch (Throwable $e) {
                error_log('[seydi] photos: ' . $e->getMessage());
            }
        }
    }
    return $all[$slug] ?? null;
}

/**
 * Convierte el contenido (array anidado de data/content.php) en filas clave/valor.
 * Se usa para sembrar la base de datos y para el formulario del panel.
 */
function flatten_settings(array $content): array
{
    $out = [];
    $walk = static function (array $node, string $prefix) use (&$walk, &$out) {
        foreach ($node as $k => $v) {
            $key = $prefix === '' ? (string) $k : $prefix . '.' . $k;
            if (in_array($key, ['story', 'gallery', 'moments'], true)) {
                continue;
            }
            if (in_array($key, SETTINGS_LINE_KEYS, true)) {
                $out[$key] = implode("\n", array_map('strval', (array) $v));
            } elseif (in_array($key, SETTINGS_PHOTO_KEYS, true)) {
                $out[$key] = preg_replace('/^db:/', '', is_array($v) ? (string) ($v['src'] ?? '') : (string) $v);
            } elseif (is_array($v)) {
                $walk($v, $key);
            } elseif (is_bool($v)) {
                $out[$key] = $v ? '1' : '0';
            } else {
                $out[$key] = (string) $v;
            }
        }
    };
    $walk($content, '');
    return $out;
}

/** Construye el array de contenido (misma forma que data/content.php) desde MySQL. */
function content_from_db(PDO $pdo, array $fallback): array
{
    $content = $fallback;
    foreach ($pdo->query('SELECT k, v FROM settings')->fetchAll() as $row) {
        $key = (string) $row['k'];
        $val = (string) $row['v'];
        if (in_array($key, SETTINGS_LINE_KEYS, true)) {
            $val = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", '', $val))), 'strlen'));
        } elseif (in_array($key, SETTINGS_PHOTO_KEYS, true)) {
            $val = $val === '' ? '' : ['src' => 'db:' . $val, 'alt' => ''];
            if ($key === 'seo.share_image') {
                $val = $val ? $val['src'] : '';
            }
        } elseif ($key === 'reply.enabled') {
            $val = $val === '1';
        }
        // Asigna en la ruta "a.b.c"
        $ref = &$content;
        foreach (explode('.', $key) as $part) {
            if (!isset($ref[$part]) || !is_array($ref[$part])) {
                $ref[$part] = $ref[$part] ?? [];
            }
            $ref = &$ref[$part];
        }
        $ref = $val;
        unset($ref);
    }

    $photo = static fn ($slug) => ['src' => $slug ? 'db:' . $slug : '', 'alt' => ''];

    $content['story'] = array_map(static fn ($r) => [
        'date' => $r['date_label'], 'title' => $r['title'], 'text' => $r['body'], 'photo' => $photo($r['photo_slug']),
    ], $pdo->query('SELECT * FROM story ORDER BY position, id')->fetchAll());

    $content['gallery'] = array_map(static fn ($r) => [
        'src' => 'db:' . $r['photo_slug'], 'alt' => '', 'caption' => $r['caption'],
    ], $pdo->query('SELECT * FROM gallery ORDER BY position, id')->fetchAll());

    $content['moments'] = array_map(static fn ($r) => [
        'layout' => $r['layout'], 'date' => $r['date_label'], 'title' => $r['title'], 'text' => $r['body'],
        'author' => $r['author'], 'place' => $r['place'], 'photo' => $photo($r['photo_slug']),
    ], $pdo->query('SELECT * FROM moments ORDER BY position, id')->fetchAll());

    return $content;
}

/**
 * Guarda un mensaje de respuesta. Devuelve 'ok', 'limit' o 'error'.
 * Con base de datos usa la tabla messages; sin ella, storage/mensajes.json.
 */
function save_message(string $message, string $ipHash, int $limit): string
{
    $pdo = db();
    if ($pdo && db_installed($pdo)) {
        try {
            $st = $pdo->prepare('SELECT COUNT(*) FROM messages WHERE ip_hash = ? AND created_at > (UTC_TIMESTAMP() - INTERVAL 1 HOUR)');
            $st->execute([$ipHash]);
            if ((int) $st->fetchColumn() >= $limit) {
                return 'limit';
            }
            $pdo->prepare('INSERT INTO messages (created_at, message, ip_hash) VALUES (UTC_TIMESTAMP(), ?, ?)')->execute([$message, $ipHash]);
            return 'ok';
        } catch (Throwable $e) {
            error_log('[seydi] messages: ' . $e->getMessage());
            return 'error';
        }
    }

    // Respaldo en archivo
    $dir = storage_dir();
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        return 'error';
    }
    $fh = @fopen($dir . '/mensajes.json', 'c+');
    if (!$fh) {
        return 'error';
    }
    flock($fh, LOCK_EX);
    $data = json_decode(stream_get_contents($fh) ?: '[]', true);
    $data = is_array($data) ? $data : [];
    $now = time();
    $recent = count(array_filter($data, static fn ($r) => ($r['ip'] ?? '') === $ipHash && strtotime((string) ($r['fecha'] ?? '')) > $now - 3600));
    if ($recent >= $limit) {
        flock($fh, LOCK_UN);
        fclose($fh);
        return 'limit';
    }
    $data[] = ['fecha' => date(DATE_ATOM), 'mensaje' => $message, 'ip' => $ipHash];
    $data = array_slice($data, -500);
    ftruncate($fh, 0);
    rewind($fh);
    $ok = fwrite($fh, (string) json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    fflush($fh);
    flock($fh, LOCK_UN);
    fclose($fh);
    return $ok ? 'ok' : 'error';
}

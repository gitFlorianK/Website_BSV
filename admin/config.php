<?php
$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
    || (($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https')
    || (($_SERVER['SERVER_PORT'] ?? '') === '443');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure' => $secureCookie,
]);
session_start();

date_default_timezone_set('Europe/Berlin');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

define('DB_PATH', __DIR__ . '/data/cms.db');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

require_once __DIR__ . '/schema.php';

function getDB(): PDO {
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $isNew = !file_exists(DB_PATH);
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    if ($isNew) {
        initDB($db);
    }

    ensureContentTables($db);

    return $db;
}

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo 'Zugriff verweigert';
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        echo 'Ungültiges CSRF-Token';
        exit;
    }
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

function sanitizeContentHtml(string $html): string {
    if (trim($html) === '') return '';

    $allowedTags = ['h2','h3','h4','p','ul','ol','li','a','strong','em','br','img','table','thead','tbody','tr','th','td','div','span'];
    $allowedAttrs = [
        'a'   => ['href', 'title', 'target', 'rel'],
        'img' => ['src', 'alt', 'title', 'width', 'height'],
    ];

    $doc = new DOMDocument('1.0', 'UTF-8');
    libxml_use_internal_errors(true);
    $doc->loadHTML(
        '<?xml encoding="UTF-8"><div>' . $html . '</div>',
        LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
    );
    libxml_clear_errors();

    $root = $doc->getElementsByTagName('div')->item(0);
    if (!$root) return '';

    $stripWithContent = ['script', 'style', 'iframe', 'object', 'embed', 'noscript'];

    $xpath = new DOMXPath($doc);
    foreach (iterator_to_array($xpath->query('.//*', $root)) as $node) {
        $tag = strtolower($node->nodeName);
        if (in_array($tag, $stripWithContent, true)) {
            $node->parentNode->removeChild($node);
            continue;
        }
        if (!in_array($tag, $allowedTags, true)) {
            while ($node->firstChild) {
                $node->parentNode->insertBefore($node->firstChild, $node);
            }
            $node->parentNode->removeChild($node);
            continue;
        }
        $allowed = $allowedAttrs[$tag] ?? [];
        foreach (iterator_to_array($node->attributes) as $attr) {
            $name = strtolower($attr->name);
            if (!in_array($name, $allowed, true)) {
                $node->removeAttribute($attr->name);
                continue;
            }
            if ($name === 'href' || $name === 'src') {
                $val = trim($attr->value);
                if (preg_match('#^(javascript|vbscript|data):#i', $val)) {
                    $safeImgData = $name === 'src' && $tag === 'img'
                        && preg_match('#^data:image/(png|jpeg|gif|webp);base64,#i', $val);
                    if (!$safeImgData) {
                        $node->removeAttribute($attr->name);
                    }
                }
            }
        }
        if ($tag === 'a' && $node->getAttribute('target') === '_blank') {
            $node->setAttribute('rel', 'noopener noreferrer');
        }
    }

    $result = '';
    foreach ($root->childNodes as $child) {
        $result .= $doc->saveHTML($child);
    }
    return $result;
}

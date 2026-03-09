<?php
session_start();

define('DB_PATH', __DIR__ . '/data/cms.db');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

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

    return $db;
}

function initDB(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            display_name TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'redakteur' CHECK(role IN ('admin','redakteur')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL DEFAULT 'blog' CHECK(type IN ('blog','gallery')),
            title TEXT NOT NULL,
            content TEXT,
            category TEXT,
            author_id INTEGER NOT NULL,
            published INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            alt_text TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            date_text TEXT NOT NULL,
            description TEXT,
            sort_order INTEGER DEFAULT 0,
            published INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS page_titles (
            page_key TEXT PRIMARY KEY,
            title TEXT NOT NULL,
            subtitle TEXT DEFAULT ''
        );
    ");

    // Seed default events
    $db->exec("
        INSERT INTO events (title, date_text, description, sort_order, published) VALUES
        ('Anfängerkurs Erwachsene', '06.05. – 10.06.2026', 'Mittwochs 18:30 – ca. 20:00 Uhr, 6 Trainingseinheiten auf dem Bogensportplatz', 1, 1),
        ('Anfängerkurs Kinder & Jugendliche', '29.05. – 13.06.2026', 'Freitags 16:00 – 17:30 Uhr & Samstags 13:00 – 14:30 Uhr, 6 Trainingseinheiten', 2, 1),
        ('Reguläres Training', 'Laufend', 'Sommertraining ab April auf dem Bogensportplatz – siehe Trainingszeiten', 3, 1)
    ");

    // Seed page titles
    $db->exec("
        INSERT INTO page_titles (page_key, title, subtitle) VALUES
        ('index', 'Herzlich Willkommen', 'Bogensportverein 1960 Plauen e.V.'),
        ('aktuelles', 'Aktuelles', 'Neuigkeiten, Berichte und Impressionen'),
        ('training', 'Training', 'Trainingszeiten & Standorte'),
        ('sponsors', 'Sponsoren', 'Unsere Unterstützer'),
        ('anfaengerkurs', 'Anfängerkurs', 'Sie haben Interesse am Bogenschießen?'),
        ('contact', 'Kontakt', 'Wir freuen uns auf Ihre Nachricht'),
        ('imprint', 'Impressum', ''),
        ('datenschutz', 'Datenschutzerklärung', ''),
        ('information', 'Informationen', 'Infoseiten')
    ");

    // Default admin account
    $hash = password_hash('admin2024', PASSWORD_DEFAULT);
    $db->prepare("INSERT OR IGNORE INTO users (username, password_hash, display_name, role) VALUES (?, ?, ?, ?)")
       ->execute(['admin', $hash, 'Administrator', 'admin']);
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

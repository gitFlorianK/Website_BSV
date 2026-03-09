<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();
$error = '';
$success = '';

// Ensure table exists for existing databases
$db->exec("
    CREATE TABLE IF NOT EXISTS page_titles (
        page_key TEXT PRIMARY KEY,
        title TEXT NOT NULL,
        subtitle TEXT DEFAULT ''
    )
");

// Seed missing entries
$defaults = [
    'index'        => ['Herzlich Willkommen', 'Bogensportverein 1960 Plauen e.V.'],
    'aktuelles'    => ['Aktuelles', 'Neuigkeiten, Berichte und Impressionen'],
    'training'     => ['Training', 'Trainingszeiten & Standorte'],
    'sponsors'     => ['Sponsoren', 'Unsere Unterstützer'],
    'anfaengerkurs'=> ['Anfängerkurs', 'Sie haben Interesse am Bogenschießen?'],
    'contact'      => ['Kontakt', 'Wir freuen uns auf Ihre Nachricht'],
    'imprint'      => ['Impressum', ''],
    'datenschutz'  => ['Datenschutzerklärung', ''],
    'information'  => ['Informationen', 'Infoseiten'],
];

$insertStmt = $db->prepare('INSERT OR IGNORE INTO page_titles (page_key, title, subtitle) VALUES (?, ?, ?)');
foreach ($defaults as $key => [$title, $subtitle]) {
    $insertStmt->execute([$key, $title, $subtitle]);
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $keys = $_POST['page_key'] ?? [];
    $titles = $_POST['title'] ?? [];
    $subtitles = $_POST['subtitle'] ?? [];

    $updateStmt = $db->prepare('UPDATE page_titles SET title = ?, subtitle = ? WHERE page_key = ?');

    foreach ($keys as $i => $key) {
        $title = trim($titles[$i] ?? '');
        $subtitle = trim($subtitles[$i] ?? '');

        if (empty($title)) {
            $error = 'Alle Titel müssen ausgefüllt sein.';
            break;
        }

        $updateStmt->execute([$title, $subtitle, $key]);
    }

    if (!$error) {
        $success = 'Seitentitel wurden gespeichert.';
    }
}

$pages = $db->query('SELECT * FROM page_titles ORDER BY page_key')->fetchAll();

$labels = [
    'index'         => 'Startseite',
    'aktuelles'     => 'Aktuelles',
    'anfaengerkurs' => 'Anfängerkurs',
    'contact'       => 'Kontakt',
    'datenschutz'   => 'Datenschutz',
    'imprint'       => 'Impressum',
    'information'   => 'Informationen',
    'sponsors'      => 'Sponsoren',
    'training'      => 'Training',
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seitentitel | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <h1>Seitentitel verwalten</h1>
            <p class="welcome">Titel und Untertitel der Hero-Bereiche aller Seiten bearbeiten.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <form method="post">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <?php foreach ($pages as $page): ?>
                <div class="card admin-card">
                    <h2><?= sanitize($labels[$page['page_key']] ?? $page['page_key']) ?></h2>
                    <input type="hidden" name="page_key[]" value="<?= sanitize($page['page_key']) ?>">

                    <div class="form-row">
                        <div class="form-group form-group-wide">
                            <label>Titel *</label>
                            <input type="text" name="title[]" value="<?= sanitize($page['title']) ?>" required>
                        </div>
                        <div class="form-group form-group-wide">
                            <label>Untertitel</label>
                            <input type="text" name="subtitle[]" value="<?= sanitize($page['subtitle']) ?>" placeholder="Optional">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Alle speichern</button>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

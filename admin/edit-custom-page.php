<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();
$pageId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$page = null;
$error = '';
$success = '';

if ($pageId) {
    $stmt = $db->prepare('SELECT * FROM custom_pages WHERE id = ?');
    $stmt->execute([$pageId]);
    $page = $stmt->fetch();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete' && $pageId) {
        requireAdmin();
        $db->prepare('DELETE FROM custom_pages WHERE id = ?')->execute([$pageId]);
        header('Location: custom-pages.php?deleted=1');
        exit;
    }

    $title = trim($_POST['title'] ?? '');
    $slug = trim($_POST['slug'] ?? '');
    $subtitle = trim($_POST['subtitle'] ?? '');
    $content = $_POST['content'] ?? '';
    $published = isset($_POST['published']) ? 1 : 0;
    $showInNav = isset($_POST['show_in_nav']) ? 1 : 0;
    $navLabel = trim($_POST['nav_label'] ?? '');
    $sortOrder = (int)($_POST['sort_order'] ?? 0);

    // Slug generieren/bereinigen
    if (empty($slug) && !empty($title)) {
        $slug = strtolower(trim($title));
        $slug = preg_replace('/[äÄ]/', 'ae', $slug);
        $slug = preg_replace('/[öÖ]/', 'oe', $slug);
        $slug = preg_replace('/[üÜ]/', 'ue', $slug);
        $slug = preg_replace('/ß/', 'ss', $slug);
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug);
        $slug = trim($slug, '-');
        if (empty($slug)) {
            $slug = 'seite-' . time();
        }
    }

    // Reservierte Slugs
    $reserved = ['index', 'training', 'aktuelles', 'sponsors', 'anfaengerkurs', 'contact', 'imprint', 'datenschutz', 'information', 'admin', 'page', 'uploads'];

    if (empty($title)) {
        $error = 'Titel ist erforderlich.';
    } elseif (empty($slug)) {
        $error = 'URL-Slug ist erforderlich.';
    } elseif (in_array($slug, $reserved)) {
        $error = 'Dieser URL-Slug ist reserviert. Bitte wählen Sie einen anderen.';
    } elseif (!preg_match('/^[a-z0-9]([a-z0-9-]*[a-z0-9])?$/', $slug)) {
        $error = 'Der URL-Slug darf nur Kleinbuchstaben, Zahlen und Bindestriche enthalten.';
    } else {
        // Check uniqueness
        $checkStmt = $db->prepare('SELECT id FROM custom_pages WHERE slug = ? AND id != ?');
        $checkStmt->execute([$slug, $pageId]);
        if ($checkStmt->fetch()) {
            $error = 'Eine Seite mit diesem URL-Slug existiert bereits.';
        }
    }

    if (!$error) {
        if ($pageId && $page) {
            $db->prepare('UPDATE custom_pages SET title = ?, slug = ?, subtitle = ?, content = ?, published = ?, show_in_nav = ?, nav_label = ?, sort_order = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?')
               ->execute([$title, $slug, $subtitle, $content, $published, $showInNav, $navLabel, $sortOrder, $pageId]);
        } else {
            $db->prepare('INSERT INTO custom_pages (title, slug, subtitle, content, published, show_in_nav, nav_label, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)')
               ->execute([$title, $slug, $subtitle, $content, $published, $showInNav, $navLabel, $sortOrder]);
            $pageId = $db->lastInsertId();
        }

        header('Location: edit-custom-page.php?id=' . $pageId . '&saved=1');
        exit;
    }

    // On error, keep form values
    $page = [
        'id' => $pageId,
        'title' => $title,
        'slug' => $slug,
        'subtitle' => $subtitle,
        'content' => $content,
        'published' => $published,
        'show_in_nav' => $showInNav,
        'nav_label' => $navLabel,
        'sort_order' => $sortOrder,
    ];
}

// Reload after potential save
if ($pageId && !$error) {
    $stmt = $db->prepare('SELECT * FROM custom_pages WHERE id = ?');
    $stmt->execute([$pageId]);
    $page = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page ? 'Seite bearbeiten' : 'Neue Seite' ?> | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <div class="section-header">
                <h1><?= $page && $pageId ? 'Seite bearbeiten' : 'Neue Seite erstellen' ?></h1>
                <a href="custom-pages.php" class="btn btn-outline">Zurück</a>
            </div>

            <?php if (isset($_GET['saved'])): ?>
                <div class="alert alert-success">Seite wurde gespeichert.
                    <?php if ($page): ?>
                        <a href="../page.php?slug=<?= sanitize($page['slug']) ?>" target="_blank" style="color: inherit; text-decoration: underline;">Vorschau</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="post" class="post-form">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                <input type="hidden" name="action" value="save">

                <div class="card admin-card">
                    <div class="form-row">
                        <div class="form-group form-group-wide">
                            <label for="title">Titel *</label>
                            <input type="text" id="title" name="title" value="<?= sanitize($page['title'] ?? '') ?>" required>
                        </div>
                        <div class="form-group form-group-wide">
                            <label for="slug">URL-Slug *</label>
                            <input type="text" id="slug" name="slug" value="<?= sanitize($page['slug'] ?? '') ?>" placeholder="wird-automatisch-generiert">
                            <small class="form-help">Erreichbar unter: page.php?slug=<strong id="slug-preview"><?= sanitize($page['slug'] ?? '...') ?></strong></small>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="subtitle">Untertitel (Hero-Bereich)</label>
                        <input type="text" id="subtitle" name="subtitle" value="<?= sanitize($page['subtitle'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label for="content">Seiteninhalt</label>
                        <textarea id="content" name="content" rows="20"><?= sanitize($page['content'] ?? '') ?></textarea>
                        <small class="form-help">HTML erlaubt: &lt;h2&gt;, &lt;h3&gt;, &lt;p&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;a&gt;, &lt;strong&gt;, &lt;br&gt;, &lt;img&gt;, &lt;table&gt;</small>
                    </div>
                </div>

                <div class="card admin-card">
                    <h2>Einstellungen</h2>
                    <div class="form-row">
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="published" <?= ($page['published'] ?? 0) ? 'checked' : '' ?>>
                                Veröffentlicht
                            </label>
                        </div>
                        <div class="form-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="show_in_nav" id="show_in_nav" <?= ($page['show_in_nav'] ?? 0) ? 'checked' : '' ?>>
                                In Navigation anzeigen
                            </label>
                        </div>
                    </div>

                    <div class="form-row" id="nav-options" style="<?= ($page['show_in_nav'] ?? 0) ? '' : 'display:none' ?>">
                        <div class="form-group form-group-wide">
                            <label for="nav_label">Navigations-Text</label>
                            <input type="text" id="nav_label" name="nav_label" value="<?= sanitize($page['nav_label'] ?? '') ?>" placeholder="Falls leer, wird der Titel verwendet">
                        </div>
                        <div class="form-group">
                            <label for="sort_order">Sortierung</label>
                            <input type="text" id="sort_order" name="sort_order" value="<?= sanitize((string)($page['sort_order'] ?? '0')) ?>">
                            <small class="form-help">Kleinere Zahlen = weiter links</small>
                        </div>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Speichern</button>
                    <a href="custom-pages.php" class="btn btn-outline">Abbrechen</a>
                    <?php if ($pageId): ?>
                        <form method="post" class="inline-form" style="margin-left: auto;" onsubmit="return confirm('Seite wirklich löschen?')">
                            <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="btn btn-danger">Seite löschen</button>
                        </form>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </main>

    <script>
    // Auto-generate slug from title
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    const slugPreview = document.getElementById('slug-preview');
    let slugManuallyEdited = slugInput.value !== '';

    slugInput.addEventListener('input', () => {
        slugManuallyEdited = slugInput.value !== '';
        slugPreview.textContent = slugInput.value || '...';
    });

    titleInput.addEventListener('input', () => {
        if (slugManuallyEdited) return;
        let slug = titleInput.value.toLowerCase().trim();
        slug = slug.replace(/[äÄ]/g, 'ae').replace(/[öÖ]/g, 'oe').replace(/[üÜ]/g, 'ue').replace(/ß/g, 'ss');
        slug = slug.replace(/[^a-z0-9]+/g, '-').replace(/^-|-$/g, '');
        slugInput.value = slug;
        slugPreview.textContent = slug || '...';
    });

    // Toggle nav options
    document.getElementById('show_in_nav').addEventListener('change', function() {
        document.getElementById('nav-options').style.display = this.checked ? '' : 'none';
    });
    </script>
</body>
</html>

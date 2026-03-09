<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();
$pages = $db->query('SELECT * FROM custom_pages ORDER BY sort_order, title')->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eigene Seiten | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <div class="section-header">
                <h1>Eigene Seiten</h1>
                <a href="edit-custom-page.php" class="btn btn-primary">Neue Seite</a>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Seite wurde gelöscht.</div>
            <?php endif; ?>

            <?php if (empty($pages)): ?>
                <p class="empty-state">Noch keine eigenen Seiten vorhanden.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Titel</th>
                            <th>URL</th>
                            <th>Navigation</th>
                            <th>Status</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pages as $p): ?>
                        <tr>
                            <td><?= sanitize($p['title']) ?></td>
                            <td><code>page.php?slug=<?= sanitize($p['slug']) ?></code></td>
                            <td><?= $p['show_in_nav'] ? '<span class="badge badge-published">Ja</span>' : '<span class="badge badge-draft">Nein</span>' ?></td>
                            <td><span class="badge badge-<?= $p['published'] ? 'published' : 'draft' ?>"><?= $p['published'] ? 'Online' : 'Entwurf' ?></span></td>
                            <td class="actions">
                                <a href="edit-custom-page.php?id=<?= $p['id'] ?>" class="btn btn-sm">Bearbeiten</a>
                                <a href="../page.php?slug=<?= sanitize($p['slug']) ?>" target="_blank" class="btn btn-sm">Ansehen</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>

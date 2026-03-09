<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();

$postCount = $db->query("SELECT COUNT(*) FROM posts")->fetchColumn();
$publishedCount = $db->query("SELECT COUNT(*) FROM posts WHERE published = 1")->fetchColumn();
$userCount = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$imageCount = $db->query("SELECT COUNT(*) FROM images")->fetchColumn();
$eventCount = $db->query("SELECT COUNT(*) FROM events WHERE published = 1")->fetchColumn();

$recentPosts = $db->query("
    SELECT p.*, u.display_name as author_name
    FROM posts p
    JOIN users u ON p.author_id = u.id
    ORDER BY p.updated_at DESC
    LIMIT 5
")->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <h1>Dashboard</h1>
            <p class="welcome">Willkommen, <?= sanitize($_SESSION['user_name']) ?>!</p>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number"><?= $postCount ?></div>
                    <div class="stat-label">Beiträge gesamt</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $publishedCount ?></div>
                    <div class="stat-label">Veröffentlicht</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $imageCount ?></div>
                    <div class="stat-label">Bilder</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?= $eventCount ?></div>
                    <div class="stat-label">Termine</div>
                </div>
                <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <div class="stat-card">
                    <div class="stat-number"><?= $userCount ?></div>
                    <div class="stat-label">Benutzer</div>
                </div>
                <?php endif; ?>
            </div>

            <div class="section-header">
                <h2>Letzte Beiträge</h2>
                <a href="edit-post.php" class="btn btn-primary">Neuer Beitrag</a>
            </div>

            <?php if (empty($recentPosts)): ?>
                <p class="empty-state">Noch keine Beiträge vorhanden.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Titel</th>
                            <th>Typ</th>
                            <th>Autor</th>
                            <th>Status</th>
                            <th>Datum</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recentPosts as $post): ?>
                        <tr>
                            <td><?= sanitize($post['title']) ?></td>
                            <td><span class="badge badge-<?= $post['type'] ?>"><?= $post['type'] === 'blog' ? 'Blog' : 'Galerie' ?></span></td>
                            <td><?= sanitize($post['author_name']) ?></td>
                            <td><span class="badge badge-<?= $post['published'] ? 'published' : 'draft' ?>"><?= $post['published'] ? 'Online' : 'Entwurf' ?></span></td>
                            <td><?= date('d.m.Y', strtotime($post['updated_at'])) ?></td>
                            <td>
                                <a href="edit-post.php?id=<?= $post['id'] ?>" class="btn btn-sm">Bearbeiten</a>
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

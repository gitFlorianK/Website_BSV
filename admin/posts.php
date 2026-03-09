<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();

// Handle delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    verifyCsrf();
    $postId = (int)($_POST['post_id'] ?? 0);

    // Delete associated images from disk
    $images = $db->prepare('SELECT filename FROM images WHERE post_id = ?');
    $images->execute([$postId]);
    foreach ($images->fetchAll() as $img) {
        $path = UPLOAD_DIR . $img['filename'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    $db->prepare('DELETE FROM posts WHERE id = ?')->execute([$postId]);
    header('Location: posts.php?deleted=1');
    exit;
}

$typeFilter = $_GET['type'] ?? '';
$where = '';
$params = [];
if ($typeFilter === 'blog' || $typeFilter === 'gallery') {
    $where = 'WHERE p.type = ?';
    $params[] = $typeFilter;
}

$posts = $db->prepare("
    SELECT p.*, u.display_name as author_name,
           (SELECT COUNT(*) FROM images i WHERE i.post_id = p.id) as image_count
    FROM posts p
    JOIN users u ON p.author_id = u.id
    $where
    ORDER BY p.created_at DESC
");
$posts->execute($params);
$posts = $posts->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beiträge | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <div class="section-header">
                <h1>Beiträge</h1>
                <a href="edit-post.php" class="btn btn-primary">Neuer Beitrag</a>
            </div>

            <?php if (isset($_GET['deleted'])): ?>
                <div class="alert alert-success">Beitrag wurde gelöscht.</div>
            <?php endif; ?>

            <div class="filter-bar">
                <a href="posts.php" class="btn btn-sm <?= !$typeFilter ? 'btn-primary' : 'btn-outline' ?>">Alle</a>
                <a href="posts.php?type=blog" class="btn btn-sm <?= $typeFilter === 'blog' ? 'btn-primary' : 'btn-outline' ?>">Blog</a>
                <a href="posts.php?type=gallery" class="btn btn-sm <?= $typeFilter === 'gallery' ? 'btn-primary' : 'btn-outline' ?>">Galerie</a>
            </div>

            <?php if (empty($posts)): ?>
                <p class="empty-state">Keine Beiträge gefunden.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Titel</th>
                            <th>Typ</th>
                            <th>Bilder</th>
                            <th>Autor</th>
                            <th>Status</th>
                            <th>Erstellt</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($posts as $post): ?>
                        <tr>
                            <td><a href="edit-post.php?id=<?= $post['id'] ?>"><?= sanitize($post['title']) ?></a></td>
                            <td><span class="badge badge-<?= $post['type'] ?>"><?= $post['type'] === 'blog' ? 'Blog' : 'Galerie' ?></span></td>
                            <td><?= $post['image_count'] ?></td>
                            <td><?= sanitize($post['author_name']) ?></td>
                            <td><span class="badge badge-<?= $post['published'] ? 'published' : 'draft' ?>"><?= $post['published'] ? 'Online' : 'Entwurf' ?></span></td>
                            <td><?= date('d.m.Y', strtotime($post['created_at'])) ?></td>
                            <td class="actions">
                                <a href="edit-post.php?id=<?= $post['id'] ?>" class="btn btn-sm">Bearbeiten</a>
                                <form method="post" class="inline-form" onsubmit="return confirm('Beitrag wirklich löschen?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                                </form>
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

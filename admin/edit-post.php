<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$postImages = [];
$error = '';
$success = '';

if ($postId) {
    $stmt = $db->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    if ($post) {
        $imgStmt = $db->prepare('SELECT * FROM images WHERE post_id = ? ORDER BY sort_order');
        $imgStmt->execute([$postId]);
        $postImages = $imgStmt->fetchAll();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();

    $title = trim($_POST['title'] ?? '');
    $type = $_POST['type'] ?? 'blog';
    $content = trim($_POST['content'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $published = isset($_POST['published']) ? 1 : 0;

    if (empty($title)) {
        $error = 'Titel ist erforderlich.';
    } elseif (!in_array($type, ['blog', 'gallery'])) {
        $error = 'Ungültiger Typ.';
    } else {
        if ($postId && $post) {
            // Update
            $stmt = $db->prepare('UPDATE posts SET title = ?, type = ?, content = ?, category = ?, published = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?');
            $stmt->execute([$title, $type, $content, $category, $published, $postId]);
        } else {
            // Insert
            $stmt = $db->prepare('INSERT INTO posts (title, type, content, category, author_id, published) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->execute([$title, $type, $content, $category, $_SESSION['user_id'], $published]);
            $postId = $db->lastInsertId();
        }

        // Handle image uploads
        if (!empty($_FILES['images']['name'][0])) {
            if (!is_dir(UPLOAD_DIR)) {
                mkdir(UPLOAD_DIR, 0755, true);
            }

            $maxSort = $db->prepare('SELECT COALESCE(MAX(sort_order), 0) FROM images WHERE post_id = ?');
            $maxSort->execute([$postId]);
            $sortOrder = (int)$maxSort->fetchColumn();

            foreach ($_FILES['images']['tmp_name'] as $i => $tmpName) {
                if ($_FILES['images']['error'][$i] !== UPLOAD_ERR_OK) continue;
                if ($_FILES['images']['size'][$i] > MAX_UPLOAD_SIZE) continue;

                $finfo = new finfo(FILEINFO_MIME_TYPE);
                $mime = $finfo->file($tmpName);
                if (!in_array($mime, ALLOWED_TYPES)) continue;

                $ext = pathinfo($_FILES['images']['name'][$i], PATHINFO_EXTENSION);
                $filename = uniqid('img_') . '.' . strtolower($ext);

                if (move_uploaded_file($tmpName, UPLOAD_DIR . $filename)) {
                    $sortOrder++;
                    $db->prepare('INSERT INTO images (post_id, filename, alt_text, sort_order) VALUES (?, ?, ?, ?)')
                       ->execute([$postId, $filename, $title, $sortOrder]);
                }
            }
        }

        // Handle image deletion
        if (!empty($_POST['delete_images'])) {
            foreach ($_POST['delete_images'] as $imgId) {
                $imgStmt = $db->prepare('SELECT filename FROM images WHERE id = ? AND post_id = ?');
                $imgStmt->execute([(int)$imgId, $postId]);
                $img = $imgStmt->fetch();
                if ($img) {
                    $path = UPLOAD_DIR . $img['filename'];
                    if (file_exists($path)) unlink($path);
                    $db->prepare('DELETE FROM images WHERE id = ?')->execute([(int)$imgId]);
                }
            }
        }

        header('Location: edit-post.php?id=' . $postId . '&saved=1');
        exit;
    }
}

// Reload post and images after potential changes
if ($postId) {
    $stmt = $db->prepare('SELECT * FROM posts WHERE id = ?');
    $stmt->execute([$postId]);
    $post = $stmt->fetch();

    $imgStmt = $db->prepare('SELECT * FROM images WHERE post_id = ? ORDER BY sort_order');
    $imgStmt->execute([$postId]);
    $postImages = $imgStmt->fetchAll();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $post ? 'Bearbeiten' : 'Neuer Beitrag' ?> | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <h1><?= $post ? 'Beitrag bearbeiten' : 'Neuer Beitrag' ?></h1>

            <?php if (isset($_GET['saved'])): ?>
                <div class="alert alert-success">Beitrag wurde gespeichert.</div>
            <?php endif; ?>
            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="post-form">
                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                <div class="form-row">
                    <div class="form-group form-group-wide">
                        <label for="title">Titel *</label>
                        <input type="text" id="title" name="title" value="<?= sanitize($post['title'] ?? '') ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="type">Typ</label>
                        <select id="type" name="type">
                            <option value="blog" <?= ($post['type'] ?? 'blog') === 'blog' ? 'selected' : '' ?>>Blog-Beitrag</option>
                            <option value="gallery" <?= ($post['type'] ?? '') === 'gallery' ? 'selected' : '' ?>>Galerie</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="category">Kategorie / Saison</label>
                    <input type="text" id="category" name="category" value="<?= sanitize($post['category'] ?? '') ?>" placeholder="z.B. Hallensaison 2024/2025">
                </div>

                <div class="form-group">
                    <label for="content">Inhalt</label>
                    <textarea id="content" name="content" rows="12"><?= sanitize($post['content'] ?? '') ?></textarea>
                    <small class="form-help">HTML ist erlaubt: &lt;strong&gt;, &lt;ul&gt;, &lt;li&gt;, &lt;p&gt;, &lt;br&gt;</small>
                </div>

                <div class="form-group">
                    <label for="images">Bilder hochladen</label>
                    <input type="file" id="images" name="images[]" multiple accept="image/jpeg,image/png,image/webp,image/gif">
                    <small class="form-help">Max. 5 MB pro Bild. JPG, PNG, WebP, GIF erlaubt.</small>
                </div>

                <?php if (!empty($postImages)): ?>
                <div class="form-group">
                    <label>Vorhandene Bilder</label>
                    <div class="image-grid">
                        <?php foreach ($postImages as $img): ?>
                        <div class="image-preview">
                            <img src="../<?= UPLOAD_URL . sanitize($img['filename']) ?>" alt="<?= sanitize($img['alt_text']) ?>">
                            <label class="image-delete">
                                <input type="checkbox" name="delete_images[]" value="<?= $img['id'] ?>">
                                Löschen
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="published" <?= ($post['published'] ?? 0) ? 'checked' : '' ?>>
                        Veröffentlicht
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">Speichern</button>
                    <a href="posts.php" class="btn btn-outline">Abbrechen</a>
                </div>
            </form>
        </div>
    </main>
</body>
</html>

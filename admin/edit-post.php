<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();
$postId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$post = null;
$postImages = [];
$error = '';

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
                    <input type="file" id="imageInput" multiple accept="image/jpeg,image/png,image/webp,image/gif">
                    <small class="form-help">Max. 5 MB pro Bild. JPG, PNG, WebP, GIF erlaubt. Große Bilder werden automatisch verkleinert.</small>
                    <div id="imageStatus" style="display:none; margin-top: 0.5rem; padding: 0.5rem; background: var(--surface); border-radius: 4px; font-size: 0.85rem;"></div>
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
    <script>
    (function() {
        const MAX_SIZE = 5 * 1024 * 1024; // 5 MB
        const MAX_DIMENSION = 3840;
        const imageInput = document.getElementById('imageInput');
        const statusEl = document.getElementById('imageStatus');
        const form = document.querySelector('.post-form');

        // Create the actual hidden file input used for form submission
        const hiddenContainer = document.createElement('div');
        hiddenContainer.style.display = 'none';
        hiddenContainer.innerHTML = '<input type="file" name="images[]" multiple>';
        form.appendChild(hiddenContainer);
        const realInput = hiddenContainer.querySelector('input');

        let processedFiles = [];

        imageInput.addEventListener('change', async function() {
            const files = Array.from(this.files);
            if (!files.length) return;

            processedFiles = [];
            statusEl.style.display = 'block';
            statusEl.textContent = 'Bilder werden verarbeitet...';
            imageInput.disabled = true;

            for (let i = 0; i < files.length; i++) {
                const file = files[i];
                statusEl.textContent = `Bild ${i + 1} von ${files.length} wird verarbeitet...`;

                if (file.type === 'image/gif' || file.size <= MAX_SIZE) {
                    // GIFs not resizable via canvas; small files need no processing
                    processedFiles.push(file);
                    continue;
                }

                try {
                    const processed = await compressImage(file);
                    processedFiles.push(processed);
                } catch (e) {
                    console.warn('Bildverarbeitung fehlgeschlagen, verwende Original:', e);
                    processedFiles.push(file);
                }
            }

            // Transfer processed files to the real input
            const dt = new DataTransfer();
            processedFiles.forEach(f => dt.items.add(f));
            realInput.files = dt.files;

            const summary = processedFiles.map((f, i) => {
                const orig = files[i];
                const sizeMB = (f.size / 1024 / 1024).toFixed(1);
                if (f !== orig) {
                    const origMB = (orig.size / 1024 / 1024).toFixed(1);
                    return `${orig.name}: ${origMB} MB → ${sizeMB} MB`;
                }
                return `${f.name}: ${sizeMB} MB`;
            });
            statusEl.innerHTML = summary.join('<br>');
            imageInput.disabled = false;
        });

        function compressImage(file) {
            return new Promise((resolve, reject) => {
                const img = new Image();
                const url = URL.createObjectURL(file);

                img.onload = function() {
                    URL.revokeObjectURL(url);

                    let { width, height } = img;

                    // Scale down if dimensions exceed maximum
                    if (width > MAX_DIMENSION || height > MAX_DIMENSION) {
                        const ratio = Math.min(MAX_DIMENSION / width, MAX_DIMENSION / height);
                        width = Math.round(width * ratio);
                        height = Math.round(height * ratio);
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    // Try decreasing quality until under 5 MB
                    const outputType = file.type === 'image/png' ? 'image/jpeg' : file.type;
                    const ext = outputType === 'image/webp' ? '.webp' : '.jpg';
                    let quality = 0.85;

                    function tryCompress() {
                        canvas.toBlob(function(blob) {
                            if (!blob) return reject(new Error('Canvas toBlob failed'));

                            if (blob.size <= MAX_SIZE || quality <= 0.3) {
                                const name = file.name.replace(/\.[^.]+$/, ext);
                                resolve(new File([blob], name, { type: outputType, lastModified: Date.now() }));
                            } else {
                                quality -= 0.1;
                                tryCompress();
                            }
                        }, outputType, quality);
                    }

                    tryCompress();
                };

                img.onerror = () => {
                    URL.revokeObjectURL(url);
                    reject(new Error('Bild konnte nicht geladen werden'));
                };

                img.src = url;
            });
        }

        // Prevent submission while processing
        form.addEventListener('submit', function(e) {
            if (imageInput.disabled) {
                e.preventDefault();
                alert('Bitte warten, Bilder werden noch verarbeitet...');
            }
        });
    })();
    </script>
</body>
</html>

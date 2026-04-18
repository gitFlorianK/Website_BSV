<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();
$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create' || $action === 'update') {
        $title = trim($_POST['title'] ?? '');
        $dateText = trim($_POST['date_text'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);
        $published = isset($_POST['published']) ? 1 : 0;

        if (empty($title) || empty($dateText)) {
            $error = 'Titel und Datum sind erforderlich.';
        } elseif ($action === 'create') {
            $db->prepare('INSERT INTO events (title, date_text, description, sort_order, published) VALUES (?, ?, ?, ?, ?)')
               ->execute([$title, $dateText, $description, $sortOrder, $published]);
            $success = 'Termin wurde erstellt.';
        } else {
            $id = (int)($_POST['event_id'] ?? 0);
            $db->prepare('UPDATE events SET title = ?, date_text = ?, description = ?, sort_order = ?, published = ? WHERE id = ?')
               ->execute([$title, $dateText, $description, $sortOrder, $published, $id]);
            $success = 'Termin wurde aktualisiert.';
        }
    } elseif ($action === 'delete') {
        requireAdmin();
        $id = (int)($_POST['event_id'] ?? 0);
        $db->prepare('DELETE FROM events WHERE id = ?')->execute([$id]);
        $success = 'Termin wurde gelöscht.';
    }
}

$events = $db->query('SELECT * FROM events ORDER BY sort_order ASC, created_at DESC')->fetchAll();
$editEvent = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare('SELECT * FROM events WHERE id = ?');
    $stmt->execute([(int)$_GET['edit']]);
    $editEvent = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Termine | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <h1>Terminverwaltung</h1>
            <p class="welcome">Termine werden auf der Startseite im Bereich "Termine" angezeigt.</p>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <!-- Create / Edit form -->
            <div class="card admin-card">
                <h2><?= $editEvent ? 'Termin bearbeiten' : 'Neuen Termin anlegen' ?></h2>
                <form method="post">
                    <input type="hidden" name="action" value="<?= $editEvent ? 'update' : 'create' ?>">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <?php if ($editEvent): ?>
                        <input type="hidden" name="event_id" value="<?= $editEvent['id'] ?>">
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group form-group-wide">
                            <label for="title">Titel *</label>
                            <input type="text" id="title" name="title" value="<?= sanitize($editEvent['title'] ?? '') ?>" required placeholder="z.B. Anfängerkurs Erwachsene">
                        </div>
                        <div class="form-group">
                            <label for="date_text">Datum / Zeitraum *</label>
                            <input type="text" id="date_text" name="date_text" value="<?= sanitize($editEvent['date_text'] ?? '') ?>" required placeholder="z.B. 06.05. – 10.06.2026">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="description">Beschreibung</label>
                        <textarea id="description" name="description" rows="3" placeholder="Details zum Termin"><?= sanitize($editEvent['description'] ?? '') ?></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="sort_order">Sortierung</label>
                            <input type="text" id="sort_order" name="sort_order" value="<?= sanitize((string)($editEvent['sort_order'] ?? '0')) ?>" placeholder="0 = oben">
                            <small class="form-help">Kleinere Zahlen werden zuerst angezeigt.</small>
                        </div>
                        <div class="form-group" style="display:flex;align-items:end;padding-bottom:0.35rem;">
                            <label class="checkbox-label">
                                <input type="checkbox" name="published" <?= ($editEvent['published'] ?? 1) ? 'checked' : '' ?>>
                                Veröffentlicht
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><?= $editEvent ? 'Aktualisieren' : 'Termin anlegen' ?></button>
                        <?php if ($editEvent): ?>
                            <a href="events.php" class="btn btn-outline">Abbrechen</a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Events list -->
            <h2>Vorhandene Termine</h2>
            <?php if (empty($events)): ?>
                <p class="empty-state">Noch keine Termine vorhanden.</p>
            <?php else: ?>
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>Sortierung</th>
                            <th>Titel</th>
                            <th>Datum</th>
                            <th>Status</th>
                            <th>Aktionen</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($events as $event): ?>
                        <tr>
                            <td><?= $event['sort_order'] ?></td>
                            <td><?= sanitize($event['title']) ?></td>
                            <td><?= sanitize($event['date_text']) ?></td>
                            <td><span class="badge badge-<?= $event['published'] ? 'published' : 'draft' ?>"><?= $event['published'] ? 'Online' : 'Entwurf' ?></span></td>
                            <td class="actions">
                                <a href="events.php?edit=<?= $event['id'] ?>" class="btn btn-sm">Bearbeiten</a>
                                <form method="post" class="inline-form" onsubmit="return confirm('Termin wirklich löschen?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
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

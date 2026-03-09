<?php
require_once __DIR__ . '/config.php';
requireAdmin();

$db = getDB();
$error = '';
$success = '';

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $username = trim($_POST['username'] ?? '');
        $displayName = trim($_POST['display_name'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'redakteur';

        if (empty($username) || empty($password) || empty($displayName)) {
            $error = 'Alle Felder sind erforderlich.';
        } elseif (strlen($password) < 6) {
            $error = 'Passwort muss mindestens 6 Zeichen lang sein.';
        } elseif (!in_array($role, ['admin', 'redakteur'])) {
            $error = 'Ungültige Rolle.';
        } else {
            $existing = $db->prepare('SELECT id FROM users WHERE username = ?');
            $existing->execute([$username]);
            if ($existing->fetch()) {
                $error = 'Benutzername bereits vergeben.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $db->prepare('INSERT INTO users (username, password_hash, display_name, role) VALUES (?, ?, ?, ?)')
                   ->execute([$username, $hash, $displayName, $role]);
                $success = 'Benutzer wurde erstellt.';
            }
        }
    } elseif ($action === 'delete') {
        $userId = (int)($_POST['user_id'] ?? 0);
        if ($userId === (int)$_SESSION['user_id']) {
            $error = 'Sie können sich nicht selbst löschen.';
        } else {
            $db->prepare('DELETE FROM users WHERE id = ?')->execute([$userId]);
            $success = 'Benutzer wurde gelöscht.';
        }
    } elseif ($action === 'update_role') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $role = $_POST['role'] ?? 'redakteur';
        if ($userId === (int)$_SESSION['user_id']) {
            $error = 'Sie können Ihre eigene Rolle nicht ändern.';
        } elseif (in_array($role, ['admin', 'redakteur'])) {
            $db->prepare('UPDATE users SET role = ? WHERE id = ?')->execute([$role, $userId]);
            $success = 'Rolle wurde aktualisiert.';
        }
    } elseif ($action === 'reset_password') {
        $userId = (int)($_POST['user_id'] ?? 0);
        $newPassword = $_POST['new_password'] ?? '';
        if (strlen($newPassword) < 6) {
            $error = 'Passwort muss mindestens 6 Zeichen lang sein.';
        } else {
            $hash = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([$hash, $userId]);
            $success = 'Passwort wurde zurückgesetzt.';
        }
    }
}

$users = $db->query('SELECT * FROM users ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Benutzerverwaltung | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <h1>Benutzerverwaltung</h1>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <!-- Create user form -->
            <div class="card admin-card">
                <h2>Neuen Benutzer anlegen</h2>
                <form method="post" class="user-form">
                    <input type="hidden" name="action" value="create">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="username">Benutzername</label>
                            <input type="text" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="display_name">Anzeigename</label>
                            <input type="text" id="display_name" name="display_name" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Passwort</label>
                            <input type="password" id="password" name="password" required minlength="6">
                        </div>
                        <div class="form-group">
                            <label for="role">Rolle</label>
                            <select id="role" name="role">
                                <option value="redakteur">Redakteur</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Benutzer anlegen</button>
                </form>
            </div>

            <!-- User list -->
            <h2>Vorhandene Benutzer</h2>
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Benutzername</th>
                        <th>Anzeigename</th>
                        <th>Rolle</th>
                        <th>Erstellt</th>
                        <th>Aktionen</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?= sanitize($user['username']) ?></td>
                        <td><?= sanitize($user['display_name']) ?></td>
                        <td>
                            <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="update_role">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <select name="role" onchange="this.form.submit()">
                                    <option value="redakteur" <?= $user['role'] === 'redakteur' ? 'selected' : '' ?>>Redakteur</option>
                                    <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
                                </select>
                            </form>
                            <?php else: ?>
                                <span class="badge badge-published"><?= $user['role'] === 'admin' ? 'Admin' : 'Redakteur' ?></span>
                            <?php endif; ?>
                        </td>
                        <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                        <td class="actions">
                            <?php if ($user['id'] !== (int)$_SESSION['user_id']): ?>
                            <!-- Reset password -->
                            <form method="post" class="inline-form">
                                <input type="hidden" name="action" value="reset_password">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="password" name="new_password" placeholder="Neues Passwort" minlength="6" class="input-sm">
                                <button type="submit" class="btn btn-sm">PW setzen</button>
                            </form>
                            <!-- Delete -->
                            <form method="post" class="inline-form" onsubmit="return confirm('Benutzer wirklich löschen?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                            <?php else: ?>
                                <span class="text-dim">(Sie selbst)</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>

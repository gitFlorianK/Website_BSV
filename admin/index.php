<?php
require_once __DIR__ . '/config.php';

$error = '';

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'login') {
    $db = getDB();
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['display_name'];
        $_SESSION['user_role'] = $user['role'];
        header('Location: dashboard.php');
        exit;
    }
    $error = 'Benutzername oder Passwort falsch.';
}

// Redirect if already logged in
if (!empty($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Login | BSV 1960 Plauen</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body class="login-page">
    <div class="login-box">
        <h1>BSV 1960 Plauen</h1>
        <p class="login-subtitle">Content-Management-System</p>

        <?php if ($error): ?>
            <div class="alert alert-error"><?= sanitize($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="action" value="login">
            <div class="form-group">
                <label for="username">Benutzername</label>
                <input type="text" id="username" name="username" required autofocus>
            </div>
            <div class="form-group">
                <label for="password">Passwort</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-full">Anmelden</button>
        </form>

        <a href="../index.html" class="back-link">Zurück zur Website</a>
    </div>
</body>
</html>

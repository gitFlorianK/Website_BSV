<header class="admin-header">
    <div class="admin-header-inner">
        <a href="dashboard.php" class="admin-logo">BSV CMS</a>
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="posts.php">Beiträge</a>
            <a href="edit-post.php">Neuer Beitrag</a>
            <a href="events.php">Termine</a>
            <a href="pages.php">Inhalte</a>
            <a href="custom-pages.php">Seiten</a>
            <?php if ($_SESSION['user_role'] === 'admin'): ?>
                <a href="users.php">Benutzer</a>
            <?php endif; ?>
        </nav>
        <div class="admin-user">
            <span><?= sanitize($_SESSION['user_name']) ?> (<?= $_SESSION['user_role'] === 'admin' ? 'Admin' : 'Redakteur' ?>)</span>
            <a href="logout.php" class="btn btn-sm btn-outline">Abmelden</a>
        </div>
    </div>
</header>

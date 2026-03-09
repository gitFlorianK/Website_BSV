<?php
require_once __DIR__ . '/config.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seiteninhalte | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <h1>Seiteninhalte bearbeiten</h1>
            <p class="welcome">Wählen Sie eine Seite aus, um deren Inhalte zu bearbeiten.</p>

            <div class="pages-grid">
                <a href="edit-content.php?page=index" class="card page-link-card">
                    <h3>Startseite</h3>
                    <p>Willkommenstext, Vorstand, CTA-Bereich</p>
                </a>
                <a href="edit-content.php?page=training" class="card page-link-card">
                    <h3>Training</h3>
                    <p>Trainingszeiten, Standorte, Saisonzeiträume</p>
                </a>
                <a href="edit-content.php?page=anfaengerkurs" class="card page-link-card">
                    <h3>Anfängerkurs</h3>
                    <p>Kurstermine, Preise, Anmeldung</p>
                </a>
                <a href="edit-content.php?page=sponsors" class="card page-link-card">
                    <h3>Sponsoren</h3>
                    <p>Sponsoren verwalten</p>
                </a>
                <a href="edit-content.php?page=contact" class="card page-link-card">
                    <h3>Kontakt</h3>
                    <p>Kontaktdaten, Anschrift</p>
                </a>
                <a href="edit-content.php?page=information" class="card page-link-card">
                    <h3>Informationen</h3>
                    <p>Externe Links, Verbände, Vereine</p>
                </a>
                <a href="edit-content.php?page=imprint" class="card page-link-card">
                    <h3>Impressum</h3>
                    <p>Rechtliche Angaben</p>
                </a>
                <a href="edit-content.php?page=datenschutz" class="card page-link-card">
                    <h3>Datenschutz</h3>
                    <p>Datenschutzerklärung</p>
                </a>
            </div>
        </div>
    </main>
</body>
</html>

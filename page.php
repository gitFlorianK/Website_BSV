<?php
require_once __DIR__ . '/admin/config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    http_response_code(404);
    header('Location: index.html');
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT * FROM custom_pages WHERE slug = ? AND published = 1');
$stmt->execute([$slug]);
$page = $stmt->fetch();

if (!$page) {
    http_response_code(404);
    header('Location: index.html');
    exit;
}

$title = htmlspecialchars($page['title'], ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars($page['subtitle'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= $title ?> | Bogensportverein 1960 Plauen e.V.</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <header class="header">
    <div class="container">
      <a href="index.html" class="logo">
        <img src="Logo_Verein_2_FK.JPG" alt="BSV 1960 Plauen Logo">
        <span class="logo-text">BSV 1960 Plauen</span>
      </a>
      <button class="hamburger" aria-label="Menü öffnen">
        <span></span><span></span><span></span>
      </button>
      <nav>
        <ul class="nav-list">
          <li><a href="index.html">Startseite</a></li>
          <li><a href="training.html">Training</a></li>
          <li><a href="aktuelles.html">Aktuelles</a></li>
          <li><a href="sponsors.html">Sponsoren</a></li>
          <li><a href="information.html">Information</a></li>
          <li><a href="anfaengerkurs.html">Anfängerkurs</a></li>
          <li><a href="contact.html">Kontakt</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <section class="hero">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1><?= $title ?></h1>
      <?php if ($subtitle): ?>
        <p><?= $subtitle ?></p>
      <?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="page-content fade-in">
        <?= $page['content'] ?>
      </div>
    </div>
  </section>

  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-contact">
          <h4>Kontakt</h4>
          <p>
            Bogensportverein 1960 Plauen e.V.<br>
            z.H. Herrn Florian Künzel<br>
            Erich-Knauf-Str. 20<br>
            08525 Plauen<br><br>
            Tel: 0152-541 566 36<br>
            E-Mail: info@bogensport-plauen.de
          </p>
        </div>
        <div>
          <h4>Quick-Links</h4>
          <ul>
            <li><a href="training.html">Trainingszeiten</a></li>
            <li><a href="anfaengerkurs.html">Anfängerkurs</a></li>
            <li><a href="aktuelles.html">Aktuelles</a></li>
            <li><a href="sponsors.html">Sponsoren</a></li>
          </ul>
        </div>
        <div>
          <h4>Verein</h4>
          <ul>
            <li><a href="contact.html">Kontakt</a></li>
            <li><a href="information.html">Information</a></li>
            <li><a href="imprint.html">Impressum</a></li>
            <li><a href="datenschutz.html">Datenschutz</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        &copy; <?= date('Y') ?> Bogensportverein 1960 Plauen e.V.
      </div>
    </div>
  </footer>

  <script src="js/main.js"></script>
</body>
</html>

<?php
date_default_timezone_set('Europe/Berlin');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$siteTitle = 'Bogensportverein 1960 Plauen e.V.';
$fullTitle = !empty($pageTitle) ? "$pageTitle | $siteTitle" : $siteTitle;
$activePage = $activePage ?? '';
?>
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= htmlspecialchars($fullTitle, ENT_QUOTES, 'UTF-8') ?></title>
<?php if (!empty($metaDescription)): ?>
  <meta name="description" content="<?= htmlspecialchars($metaDescription, ENT_QUOTES, 'UTF-8') ?>">
<?php endif; ?>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/style.css">
</head>
<body>

  <header class="header">
    <div class="container">
      <a href="index.php" class="logo">
        <img src="Logo_Verein_2_FK.JPG" alt="BSV 1960 Plauen Logo">
        <span class="logo-text">BSV 1960 Plauen</span>
      </a>
      <button class="hamburger" aria-label="Menü öffnen">
        <span></span><span></span><span></span>
      </button>
      <nav>
        <ul class="nav-list">
<?php
$navItems = [
    'index' => 'Startseite',
    'training' => 'Training',
    'aktuelles' => 'Aktuelles',
    'sponsors' => 'Sponsoren',
    'information' => 'Information',
    'anfaengerkurs' => 'Anfängerkurs',
    'contact' => 'Kontakt',
];
foreach ($navItems as $key => $label):
    $activeClass = ($activePage === $key) ? ' class="active"' : '';
?>
          <li><a href="<?= $key ?>.php"<?= $activeClass ?>><?= $label ?></a></li>
<?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </header>

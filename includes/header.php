<?php
require_once __DIR__ . '/bootstrap.php';
date_default_timezone_set('Europe/Berlin');

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: strict-origin-when-cross-origin');

$siteTitle = 'Bogensportverein 1960 Plauen e.V.';
$fullTitle = !empty($pageTitle) ? "$pageTitle | $siteTitle" : $siteTitle;
$activePage = $activePage ?? '';

$navItems = [
    ['key' => 'index',         'label' => 'Startseite',    'url' => 'index.php',         'sort' => 10],
    ['key' => 'training',      'label' => 'Training',      'url' => 'training.php',      'sort' => 20],
    ['key' => 'aktuelles',     'label' => 'Aktuelles',     'url' => 'aktuelles.php',     'sort' => 30],
    ['key' => 'sponsors',      'label' => 'Sponsoren',     'url' => 'sponsors.php',      'sort' => 40],
    ['key' => 'information',   'label' => 'Information',   'url' => 'information.php',   'sort' => 50],
    ['key' => 'anfaengerkurs', 'label' => 'Anfängerkurs',  'url' => 'anfaengerkurs.php', 'sort' => 60],
    ['key' => 'contact',       'label' => 'Kontakt',       'url' => 'contact.php',       'sort' => 70],
];

try {
    $customStmt = getDB()->query('SELECT slug, nav_label, title, sort_order FROM custom_pages WHERE published = 1 AND show_in_nav = 1');
    foreach ($customStmt as $row) {
        $navItems[] = [
            'key'   => '',
            'label' => $row['nav_label'] !== '' ? $row['nav_label'] : $row['title'],
            'url'   => 'page.php?slug=' . rawurlencode($row['slug']),
            'sort'  => (int)$row['sort_order'],
        ];
    }
} catch (Throwable $e) {
    // Fall back silently — fixed nav still renders.
}

usort($navItems, fn($a, $b) => $a['sort'] <=> $b['sort']);
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
<?php foreach ($navItems as $item):
    $activeClass = ($item['key'] !== '' && $activePage === $item['key']) ? ' class="active"' : '';
?>
          <li><a href="<?= htmlspecialchars($item['url'], ENT_QUOTES, 'UTF-8') ?>"<?= $activeClass ?>><?= htmlspecialchars($item['label'], ENT_QUOTES, 'UTF-8') ?></a></li>
<?php endforeach; ?>
        </ul>
      </nav>
    </div>
  </header>

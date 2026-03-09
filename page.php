<?php
require_once __DIR__ . '/admin/config.php';

$slug = $_GET['slug'] ?? '';
if (!$slug) {
    http_response_code(404);
    header('Location: index.php');
    exit;
}

$db = getDB();
$stmt = $db->prepare('SELECT * FROM custom_pages WHERE slug = ? AND published = 1');
$stmt->execute([$slug]);
$pageData = $stmt->fetch();

if (!$pageData) {
    http_response_code(404);
    header('Location: index.php');
    exit;
}

$pageTitle = htmlspecialchars($pageData['title'], ENT_QUOTES, 'UTF-8');
$subtitle = htmlspecialchars($pageData['subtitle'], ENT_QUOTES, 'UTF-8');
$activePage = '';
$scripts = ['js/main.js'];
include __DIR__ . '/includes/header.php';
?>

  <section class="hero">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1><?= $pageTitle ?></h1>
<?php if ($subtitle): ?>
      <p><?= $subtitle ?></p>
<?php endif; ?>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="page-content fade-in">
        <?= $pageData['content'] ?>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

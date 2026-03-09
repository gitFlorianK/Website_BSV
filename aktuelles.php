<?php
$pageTitle = 'Aktuelles';
$activePage = 'aktuelles';
$scripts = ['js/main.js', 'js/aktuelles.js'];
include __DIR__ . '/includes/header.php';
?>

  <section class="hero" data-page="aktuelles">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1>Aktuelles</h1>
      <p>Neuigkeiten, Berichte und Impressionen</p>
    </div>
  </section>

  <section class="section">
    <div class="container">

      <!-- Tab Navigation -->
      <div class="tab-bar fade-in">
        <button class="tab-btn active" data-tab="all">Alle</button>
        <button class="tab-btn" data-tab="blog">Berichte</button>
        <button class="tab-btn" data-tab="gallery">Galerie</button>
      </div>

      <!-- Content loaded from CMS -->
      <div id="aktuelles-content" class="aktuelles-grid">
        <p class="loading-state">Inhalte werden geladen...</p>
      </div>

      <!-- Fallback: static content if API is not available -->
      <noscript>
        <p>Bitte aktivieren Sie JavaScript, um die Inhalte anzuzeigen.</p>
      </noscript>

    </div>
  </section>

  <!-- Lightbox -->
  <div class="lightbox" id="lightbox">
    <button class="lightbox-close" aria-label="Schließen">&times;</button>
    <button class="lightbox-nav lightbox-prev" aria-label="Vorheriges Bild">&#8249;</button>
    <img src="" alt="Galerie-Bild">
    <button class="lightbox-nav lightbox-next" aria-label="Nächstes Bild">&#8250;</button>
  </div>

<?php include __DIR__ . '/includes/footer.php'; ?>

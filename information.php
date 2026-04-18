<?php
$pageTitle = 'Informationen';
$activePage = 'information';
$scripts = ['js/main.js', 'js/content.js'];
include __DIR__ . '/includes/header.php';
?>

  <section class="hero" data-page="information">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1>Informationen</h1>
      <p>Infoseiten</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <h2 class="section-title fade-in">Verbände &amp; Informationen</h2>
      <div class="grid-2 stagger" data-cms="links-verbaende">

        <a href="https://www.dbsv1959.de/" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#127993;</div>
          <div>
            <h4>Deutscher Bogenschützenverband (DBSV)</h4>
            <p>dbsv1959.de</p>
          </div>
        </a>

        <a href="https://www.sachsenbogen.de/sbv/index.php" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#127993;</div>
          <div>
            <h4>Sächsischer Bogensportverband</h4>
            <p>sachsenbogen.de</p>
          </div>
        </a>

        <a href="http://www.bogensportinfo.de/" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#9432;</div>
          <div>
            <h4>Bogensportinfo</h4>
            <p>bogensportinfo.de</p>
          </div>
        </a>

        <a href="https://www.bogensport-extra.de/index.php" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#9432;</div>
          <div>
            <h4>Bogensportmagazin</h4>
            <p>bogensport-extra.de</p>
          </div>
        </a>

      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

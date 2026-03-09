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

  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title fade-in">Sächsische Bogensportvereine</h2>
      <div class="grid-2 stagger" data-cms="links-vereine">

        <a href="https://www.the-bowmen.de/site/index.php" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#127919;</div>
          <div>
            <h4>Bogensportclub Glauchau e.V.</h4>
            <p>Glauchau</p>
          </div>
        </a>

        <a href="http://www.sv-koweg.de/index.php?id=22" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#127919;</div>
          <div>
            <h4>SV Koweg Görlitz</h4>
            <p>Görlitz</p>
          </div>
        </a>

        <a href="https://www.mogono-bogen.de/" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#127919;</div>
          <div>
            <h4>SG Motor Gohlis-Nord Leipzig e.V.</h4>
            <p>Leipzig</p>
          </div>
        </a>

        <a href="https://www.radebergersv-bogenschiessen.de/" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#127919;</div>
          <div>
            <h4>Radeberger Sportverein e.V.</h4>
            <p>Radeberg</p>
          </div>
        </a>

        <a href="http://www.osvzittau.de/bogensport.html" target="_blank" rel="noopener" class="card info-link-card fade-in">
          <div class="link-icon">&#127919;</div>
          <div>
            <h4>OSV Zittau e.V.</h4>
            <p>Zittau</p>
          </div>
        </a>

      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

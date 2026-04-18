<?php
$pageTitle = 'Sponsoren';
$activePage = 'sponsors';
$scripts = ['js/main.js', 'js/content.js'];
include __DIR__ . '/includes/header.php';
?>

  <section class="hero" data-page="sponsors">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1>Sponsoren</h1>
      <p>Unsere Unterstützer</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <h2 class="section-title fade-in">Unsere Sponsoren</h2>
      <div class="grid-3 stagger" data-cms="sponsors">

        <div class="card sponsor-card fade-in">
          <h3>Tischlerei Fritzsch</h3>
          <div class="sponsor-info">
            Teichstraße 4a<br>
            08527 Rößnitz<br>
            Tel: 037431 88288<br>
            Web: www.tischlerei-fritzsch.de
          </div>
        </div>

        <div class="card sponsor-card fade-in">
          <h3>Elektrotechnik Plauen GmbH</h3>
          <div class="sponsor-info">
            Weststraße 63<br>
            08523 Plauen<br>
            Tel: 03741 21 20<br>
            Web: www.elektrotechnik-plauen.de
          </div>
        </div>

        <div class="card sponsor-card fade-in">
          <h3>Bauhaus Plauen</h3>
          <div class="sponsor-info">
            Äußere Reichenbacher Straße<br>
            08529 Plauen<br>
            Tel: 03741 48 89 0<br>
            Web: www.bauhaus.info
          </div>
        </div>

        <div class="card sponsor-card fade-in">
          <h3>Simba n3 GmbH</h3>
          <div class="sponsor-info">
            Dr.-Friedrichs-Straße 42<br>
            08606 Oelsnitz<br>
            Tel: 037421 72 24 0<br>
            Web: www.nhochdrei.de/de
          </div>
        </div>

        <div class="card sponsor-card fade-in">
          <h3>S+K Ing.gmbH</h3>
          <div class="sponsor-info">
            Bergstraße 3<br>
            08523 Plauen<br>
            Tel: 03741 131200
          </div>
        </div>

        <div class="card sponsor-card fade-in">
          <h3>Normann Lippert GmbH</h3>
          <div class="sponsor-info">
            Fedor-Schnorr-Straße 20<br>
            08523 Plauen<br>
            Tel: 03741 70 77 73
          </div>
        </div>

        <div class="card sponsor-card fade-in">
          <h3>Lichtwelt Plauen</h3>
          <div class="sponsor-info">
            Dürerstraße 14<br>
            08527 Plauen<br>
            Tel: 03741 40 65 91 0
          </div>
        </div>

      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

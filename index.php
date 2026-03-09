<?php
$pageTitle = '';
$activePage = 'index';
$metaDescription = 'Bogensportverein 1960 Plauen e.V. - Bogenschießen in Plauen, Vogtland. Training, Anfängerkurse und Turniere.';
$scripts = ['js/main.js', 'js/content.js', 'js/termine.js'];
include __DIR__ . '/includes/header.php';
?>

  <!-- Hero -->
  <section class="hero hero-home" data-page="index">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1>Herzlich Willkommen</h1>
      <p>Bogensportverein 1960 Plauen e.V.</p>
    </div>
  </section>

  <!-- Willkommen -->
  <section class="section">
    <div class="container">
      <div class="welcome-text fade-in" data-cms="willkommen">
        <p>Unser Verein wurde am 18. Juni 1960 in Plauen gegründet. Hauptsächlich werden bei uns Recurvebögen (olympische) geschossen, aber auch die Langbogen, Jagdbögen sowie Compoundbögen haben schon ihre Liebhaber gefunden.</p>
        <p>Je nach Leistungsstand nehmen unsere Schützen an Fita- oder Jagdturnieren teil. Dabei sind wir bemüht auch Anfänger zum Leistungsvergleich mit anderen Schützen zu motivieren. Um dies zu erreichen haben wir erfahrene Trainer im Verein, die ihr Wissen an neue Schützen weitergeben.</p>
      </div>
    </div>
  </section>

  <!-- Terminkalender -->
  <section class="section section-alt">
    <div class="container">
      <h2 class="section-title fade-in">Termine</h2>
      <div class="timeline fade-in" id="termine-timeline">
        <p class="timeline-empty">Termine werden geladen...</p>
      </div>
    </div>
  </section>

  <!-- Vorstand -->
  <section class="section">
    <div class="container">
      <h2 class="section-title fade-in">Vorstand</h2>
      <div class="grid-3 stagger" data-cms="board">

        <div class="card vorstand-card fade-in">
          <img src="images/index/2_f_kuenzel.jpg" alt="Florian Künzel" class="card-img">
          <div class="role">Vereinsvorsitzender</div>
          <h3>Florian Künzel</h3>
          <div class="email">info@bogensport-plauen.de</div>
        </div>

        <div class="card vorstand-card fade-in">
          <img src="images/index/1_r_kraus.jpg" alt="Ronny Krauß" class="card-img">
          <div class="role">stell. Vereinsvorsitzender</div>
          <h3>Ronny Krauß</h3>
        </div>

        <div class="card vorstand-card fade-in">
          <img src="images/index/3_a_kus.jpg" alt="Anja Kus" class="card-img">
          <div class="role">Schatzmeisterin</div>
          <h3>Anja Kus</h3>
        </div>

      </div>
    </div>
  </section>

  <!-- CTA -->
  <section class="section">
    <div class="container">
      <div class="cta-section fade-in">
        <h2 data-cms="cta-title">Interesse am Bogenschießen?</h2>
        <p data-cms="cta-text">Wir bieten jedes Jahr Anfängerkurse für Erwachsene und Kinder an.</p>
        <a href="anfaengerkurs.php" class="btn btn-primary">Zum Anfängerkurs</a>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

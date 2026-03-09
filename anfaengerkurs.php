<?php
$pageTitle = 'Anfängerkurs';
$activePage = 'anfaengerkurs';
$scripts = ['js/main.js', 'js/content.js'];
include __DIR__ . '/includes/header.php';
?>

  <section class="hero" data-page="anfaengerkurs">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1>Anfängerkurs</h1>
      <p>Sie haben Interesse am Bogenschießen?</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="welcome-text fade-in mb-3" data-cms="intro">
        <p>Wir bieten jedes Jahr Anfängerkurse für alle diejenigen an, die den Bogensport kennenlernen möchten.</p>
        <p>In 6 Trainingseinheiten bringen wir Ihnen den Umgang mit Pfeil und Bogen näher.</p>
      </div>

      <div class="grid-2 stagger" data-cms="courses">

        <!-- Erwachsene -->
        <div class="card kurs-card fade-in">
          <h3>Erwachsene</h3>
          <span class="kurs-badge warteliste">Nur noch Warteliste!</span>
          <p class="mt-1" style="color: var(--text-dim);">Anmeldefrist für den Kurs 2026 ist der 31.03.2026</p>

          <h4 class="mt-2">Termine</h4>
          <p style="color: var(--text-dim);">Mittwochs 18:30 – ca. 20:00 Uhr</p>
          <ul class="kurs-dates">
            <li>Mittwoch, 06. Mai 2026</li>
            <li>Mittwoch, 13. Mai 2026</li>
            <li>Mittwoch, 20. Mai 2026</li>
            <li>Mittwoch, 27. Mai 2026</li>
            <li>Mittwoch, 03. Juni 2026</li>
            <li>Mittwoch, 10. Juni 2026</li>
          </ul>

          <div class="kurs-price">Unkostenbeitrag: 50 Euro</div>
          <p class="mt-1" style="color: var(--text-dim);">Bogen, Pfeile und Zubehör werden gestellt.</p>
        </div>

        <!-- Kinder -->
        <div class="card kurs-card fade-in">
          <h3>Kinder (ab 10 Jahren) &amp; Jugendliche</h3>
          <span class="kurs-badge warteliste">Nur noch Warteliste!</span>
          <p class="mt-1" style="color: var(--text-dim);">Anmeldefrist für den Kurs 2026 ist der 30.04.2026</p>

          <h4 class="mt-2">Termine</h4>
          <p style="color: var(--text-dim);">Freitags 16:00 – 17:30 Uhr &amp; Samstags 13:00 – 14:30 Uhr</p>
          <ul class="kurs-dates">
            <li>Freitag, 29. Mai 2026</li>
            <li>Samstag, 30. Mai 2026</li>
            <li>Freitag, 05. Juni 2026</li>
            <li>Samstag, 06. Juni 2026</li>
            <li>Freitag, 12. Juni 2026 – ACHTUNG: 15:00 bis 16:30 Uhr!</li>
            <li>Samstag, 13. Juni 2026</li>
          </ul>

          <div class="kurs-price">Unkostenbeitrag: 30 Euro</div>
          <p class="mt-1" style="color: var(--text-dim);">Bogen, Pfeile und Zubehör werden gestellt.</p>
        </div>

      </div>

      <!-- Anmeldung -->
      <div class="cta-section fade-in mt-4">
        <h2>Anmeldung</h2>
        <p data-cms="anmeldung-text">Bitte senden Sie uns eine E-Mail mit Name, Vorname, Alter, E-Mail-Adresse und Telefon (optional).</p>
        <a href="mailto:bsv1960grundkurs@gmail.com" class="btn btn-primary" data-cms="anmeldung-email">bsv1960grundkurs@gmail.com</a>
      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

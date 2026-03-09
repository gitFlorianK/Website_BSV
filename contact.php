<?php
$pageTitle = 'Kontakt';
$activePage = 'contact';
$scripts = ['js/main.js', 'js/content.js'];
include __DIR__ . '/includes/header.php';
?>

  <section class="hero" data-page="contact">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1>Kontakt</h1>
      <p>Wir freuen uns auf Ihre Nachricht</p>
    </div>
  </section>

  <section class="section">
    <div class="container">
      <div class="grid-2">

        <div class="fade-in" data-cms="intro">
          <h2 class="mb-3">Kontaktieren Sie uns</h2>
          <p style="color: var(--text-dim); margin-bottom: 1rem;">Sie haben Interesse am Bogenschießen? Dann schauen Sie doch mal bei unserem <a href="anfaengerkurs.php">Anfängerkurs</a> vorbei.</p>
          <p style="color: var(--text-dim); margin-bottom: 1rem;">Sie haben Fragen zu unserem Verein?</p>
          <p style="color: var(--text-dim); margin-bottom: 1rem;">Sie würden gerne Bogenschießen bei einer Veranstaltung anbieten?</p>
          <p style="color: var(--text-dim);">Dann melden Sie sich bei uns.</p>
        </div>

        <div class="card fade-in">
          <h3 class="mb-3" style="color: var(--accent-orange);">Anschrift</h3>
          <div class="contact-info">
            <p><strong data-cms="org-name">Bogensportverein 1960 Plauen e.V.</strong></p>
            <p data-cms="contact-person">z.H. Herrn Florian Künzel</p>
            <p data-cms="street">Erich-Knauf-Str. 20</p>
            <p data-cms="city">08525 Plauen</p>
            <p class="mt-2" data-cms="phone"><span class="contact-icon">&#9742;</span> <strong>Telefon:</strong> 0152-541 566 36 (ab 16:00 Uhr)</p>
            <p data-cms="email"><span class="contact-icon">&#9993;</span> <strong>E-Mail:</strong> <a href="mailto:info@bogensport-plauen.de">info@bogensport-plauen.de</a></p>
          </div>
        </div>

      </div>
    </div>
  </section>

<?php include __DIR__ . '/includes/footer.php'; ?>

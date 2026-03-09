<?php
$pageTitle = 'Training';
$activePage = 'training';
$scripts = ['js/main.js', 'js/content.js'];
include __DIR__ . '/includes/header.php';
?>

  <section class="hero" data-page="training">
    <div class="hero-bg hero-bg-fallback"></div>
    <div class="hero-content">
      <h1>Training</h1>
      <p>Trainingszeiten &amp; Standorte</p>
    </div>
  </section>

  <div data-cms="training">
  <!-- Sommertraining -->
  <section class="section">
    <div class="container">
      <div class="training-block fade-in">
        <h2 class="section-title">Sommertraining</h2>
        <p class="section-subtitle text-center mb-3">1. April bis 30. September</p>

        <div class="card mb-3">
          <h3>Standort</h3>
          <p class="training-location">
            Bogensportplatz<br>
            Wolfsbergweg, 08525 Plauen
          </p>
          <img src="images/training/bogensportplatz_01.jpg" alt="Bogensportplatz Plauen" style="border-radius: var(--radius); margin-top: 1rem;">
        </div>

        <table class="training-table">
          <thead>
            <tr>
              <th>Tag</th>
              <th>Zeit</th>
              <th>Gruppe</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Montag</td>
              <td>ab 17:00 Uhr</td>
              <td>Bögen ohne Visier (Jagdschützen)</td>
            </tr>
            <tr>
              <td>Dienstag</td>
              <td>17:00 – 18:30 Uhr</td>
              <td>Kinder und Jugendmannschaften</td>
            </tr>
            <tr>
              <td>Donnerstag</td>
              <td>ab 18:00 Uhr</td>
              <td>Bögen mit Visier (Scheibenschießen)</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </section>

  <!-- Wintertraining -->
  <section class="section section-alt">
    <div class="container">
      <div class="training-block fade-in">
        <h2 class="section-title">Wintertraining</h2>
        <p class="section-subtitle text-center mb-3">1. Oktober bis 31. März</p>

        <div class="card mb-3">
          <h3>Standort</h3>
          <p class="training-location">
            Turnhalle Rückertschule<br>
            Rückertstraße 33, 08525 Plauen
          </p>
        </div>

        <table class="training-table">
          <thead>
            <tr>
              <th>Tag</th>
              <th>Zeit</th>
              <th>Gruppe</th>
            </tr>
          </thead>
          <tbody>
            <tr>
              <td>Montag</td>
              <td>16:30 – 18:00 Uhr</td>
              <td>Erwachsene</td>
            </tr>
            <tr>
              <td>Dienstag</td>
              <td>16:30 – 19:30 Uhr</td>
              <td>Kinder und Jugendmannschaften*</td>
            </tr>
            <tr>
              <td>Donnerstag</td>
              <td>18:00 – 20:15 Uhr</td>
              <td>Erwachsene</td>
            </tr>
            <tr>
              <td>Samstag</td>
              <td>10:00 – 12:15 Uhr</td>
              <td>Offenes Training</td>
            </tr>
          </tbody>
        </table>
        <p class="training-note">* Alle anderen Schützen müssen bitte auf die anderen Trainingszeiten ausweichen</p>
      </div>
    </div>
  </section>
  </div>

<?php include __DIR__ . '/includes/footer.php'; ?>

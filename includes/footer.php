  <footer class="footer">
    <div class="container">
      <div class="footer-grid">
        <div class="footer-contact">
          <h4>Kontakt</h4>
          <p>
            Bogensportverein 1960 Plauen e.V.<br>
            z.H. Herrn Florian Künzel<br>
            Erich-Knauf-Str. 20<br>
            08525 Plauen<br><br>
            Tel: 0152-541 566 36<br>
            E-Mail: info@bogensport-plauen.de
          </p>
        </div>
        <div>
          <h4>Quick-Links</h4>
          <ul>
            <li><a href="training.php">Trainingszeiten</a></li>
            <li><a href="anfaengerkurs.php">Anfängerkurs</a></li>
            <li><a href="aktuelles.php">Aktuelles</a></li>
            <li><a href="sponsors.php">Sponsoren</a></li>
          </ul>
        </div>
        <div>
          <h4>Verein</h4>
          <ul>
            <li><a href="contact.php">Kontakt</a></li>
            <li><a href="information.php">Information</a></li>
            <li><a href="imprint.php">Impressum</a></li>
            <li><a href="datenschutz.php">Datenschutz</a></li>
          </ul>
        </div>
      </div>
      <div class="footer-bottom">
        &copy; <?= date('Y') ?> Bogensportverein 1960 Plauen e.V.
      </div>
    </div>
  </footer>

<?php if (!empty($scripts)): ?>
<?php foreach ($scripts as $src): ?>
  <script src="<?= htmlspecialchars($src, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
<?php endif; ?>
</body>
</html>

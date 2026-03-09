<?php
session_start();

define('DB_PATH', __DIR__ . '/data/cms.db');
define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('UPLOAD_URL', 'uploads/');
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5 MB
define('ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/webp', 'image/gif']);

function getDB(): PDO {
    $dir = dirname(DB_PATH);
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $isNew = !file_exists(DB_PATH);
    $db = new PDO('sqlite:' . DB_PATH);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    $db->exec('PRAGMA journal_mode=WAL');
    $db->exec('PRAGMA foreign_keys=ON');

    if ($isNew) {
        initDB($db);
    }

    ensureContentTables($db);

    return $db;
}

function initDB(PDO $db): void {
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            password_hash TEXT NOT NULL,
            display_name TEXT NOT NULL,
            role TEXT NOT NULL DEFAULT 'redakteur' CHECK(role IN ('admin','redakteur')),
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            type TEXT NOT NULL DEFAULT 'blog' CHECK(type IN ('blog','gallery')),
            title TEXT NOT NULL,
            content TEXT,
            category TEXT,
            author_id INTEGER NOT NULL,
            published INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS images (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            post_id INTEGER NOT NULL,
            filename TEXT NOT NULL,
            alt_text TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS events (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            date_text TEXT NOT NULL,
            description TEXT,
            sort_order INTEGER DEFAULT 0,
            published INTEGER DEFAULT 1,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS page_titles (
            page_key TEXT PRIMARY KEY,
            title TEXT NOT NULL,
            subtitle TEXT DEFAULT ''
        );
    ");

    // Seed default events
    $db->exec("
        INSERT INTO events (title, date_text, description, sort_order, published) VALUES
        ('Anfängerkurs Erwachsene', '06.05. – 10.06.2026', 'Mittwochs 18:30 – ca. 20:00 Uhr, 6 Trainingseinheiten auf dem Bogensportplatz', 1, 1),
        ('Anfängerkurs Kinder & Jugendliche', '29.05. – 13.06.2026', 'Freitags 16:00 – 17:30 Uhr & Samstags 13:00 – 14:30 Uhr, 6 Trainingseinheiten', 2, 1),
        ('Reguläres Training', 'Laufend', 'Sommertraining ab April auf dem Bogensportplatz – siehe Trainingszeiten', 3, 1)
    ");

    // Seed page titles
    $db->exec("
        INSERT INTO page_titles (page_key, title, subtitle) VALUES
        ('index', 'Herzlich Willkommen', 'Bogensportverein 1960 Plauen e.V.'),
        ('aktuelles', 'Aktuelles', 'Neuigkeiten, Berichte und Impressionen'),
        ('training', 'Training', 'Trainingszeiten & Standorte'),
        ('sponsors', 'Sponsoren', 'Unsere Unterstützer'),
        ('anfaengerkurs', 'Anfängerkurs', 'Sie haben Interesse am Bogenschießen?'),
        ('contact', 'Kontakt', 'Wir freuen uns auf Ihre Nachricht'),
        ('imprint', 'Impressum', ''),
        ('datenschutz', 'Datenschutzerklärung', ''),
        ('information', 'Informationen', 'Infoseiten')
    ");

    // Default admin account
    $hash = password_hash('admin2024', PASSWORD_DEFAULT);
    $db->prepare("INSERT OR IGNORE INTO users (username, password_hash, display_name, role) VALUES (?, ?, ?, ?)")
       ->execute(['admin', $hash, 'Administrator', 'admin']);
}

function ensureContentTables(PDO $db): void {
    static $done = false;
    if ($done) return;
    $done = true;

    // Custom pages (always ensure, added later)
    $db->exec("
        CREATE TABLE IF NOT EXISTS custom_pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT UNIQUE NOT NULL,
            title TEXT NOT NULL,
            subtitle TEXT DEFAULT '',
            content TEXT NOT NULL DEFAULT '',
            published INTEGER DEFAULT 0,
            show_in_nav INTEGER DEFAULT 0,
            nav_label TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // Check if content migration already done
    $check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='page_sections'")->fetch();
    if ($check) return;

    $db->exec("
        CREATE TABLE IF NOT EXISTS page_sections (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            page_key TEXT NOT NULL,
            section_key TEXT NOT NULL,
            content TEXT NOT NULL DEFAULT '',
            UNIQUE(page_key, section_key)
        );

        CREATE TABLE IF NOT EXISTS board_members (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            role TEXT NOT NULL,
            email TEXT DEFAULT '',
            image TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS sponsors (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            address TEXT NOT NULL DEFAULT '',
            phone TEXT DEFAULT '',
            website TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS training_seasons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            date_range TEXT NOT NULL,
            location_name TEXT NOT NULL DEFAULT '',
            location_address TEXT NOT NULL DEFAULT '',
            location_image TEXT DEFAULT '',
            note TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS training_times (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            season_id INTEGER NOT NULL,
            day TEXT NOT NULL,
            time_text TEXT NOT NULL,
            group_name TEXT NOT NULL,
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (season_id) REFERENCES training_seasons(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS courses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            status_text TEXT DEFAULT '',
            deadline TEXT DEFAULT '',
            schedule_text TEXT DEFAULT '',
            cost TEXT DEFAULT '',
            equipment_note TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0
        );

        CREATE TABLE IF NOT EXISTS course_dates (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            course_id INTEGER NOT NULL,
            date_text TEXT NOT NULL,
            note TEXT DEFAULT '',
            sort_order INTEGER DEFAULT 0,
            FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
        );

        CREATE TABLE IF NOT EXISTS info_links (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category TEXT NOT NULL DEFAULT 'verbaende',
            title TEXT NOT NULL,
            subtitle TEXT DEFAULT '',
            url TEXT NOT NULL,
            sort_order INTEGER DEFAULT 0
        );
    ");

    seedContentData($db);
}

function seedContentData(PDO $db): void {
    $s = $db->prepare('INSERT OR IGNORE INTO page_sections (page_key, section_key, content) VALUES (?, ?, ?)');

    // Index page
    $s->execute(['index', 'willkommen', '<p>Unser Verein wurde am 18. Juni 1960 in Plauen gegründet. Hauptsächlich werden bei uns Recurvebögen (olympische) geschossen, aber auch die Langbogen, Jagdbögen sowie Compoundbögen haben schon ihre Liebhaber gefunden.</p><p>Je nach Leistungsstand nehmen unsere Schützen an Fita- oder Jagdturnieren teil. Dabei sind wir bemüht auch Anfänger zum Leistungsvergleich mit anderen Schützen zu motivieren. Um dies zu erreichen haben wir erfahrene Trainer im Verein, die ihr Wissen an neue Schützen weitergeben.</p>']);
    $s->execute(['index', 'cta_title', 'Interesse am Bogenschießen?']);
    $s->execute(['index', 'cta_text', 'Wir bieten jedes Jahr Anfängerkurse für Erwachsene und Kinder an.']);

    // Contact page
    $s->execute(['contact', 'intro', '<p>Sie haben Interesse am Bogenschießen? Dann schauen Sie doch mal bei unserem <a href="anfaengerkurs.html">Anfängerkurs</a> vorbei.</p><p>Sie haben Fragen zu unserem Verein?</p><p>Sie würden gerne Bogenschießen bei einer Veranstaltung anbieten?</p><p>Dann melden Sie sich bei uns.</p>']);
    $s->execute(['contact', 'org_name', 'Bogensportverein 1960 Plauen e.V.']);
    $s->execute(['contact', 'contact_person', 'z.H. Herrn Florian Künzel']);
    $s->execute(['contact', 'street', 'Erich-Knauf-Str. 20']);
    $s->execute(['contact', 'city', '08525 Plauen']);
    $s->execute(['contact', 'phone', '0152-541 566 36']);
    $s->execute(['contact', 'phone_note', 'ab 16:00 Uhr']);
    $s->execute(['contact', 'email', 'info@bogensport-plauen.de']);

    // Anfaengerkurs page
    $s->execute(['anfaengerkurs', 'intro', '<p>Wir bieten jedes Jahr Anfängerkurse für alle diejenigen an, die den Bogensport kennenlernen möchten.</p><p>In 6 Trainingseinheiten bringen wir Ihnen den Umgang mit Pfeil und Bogen näher.</p>']);
    $s->execute(['anfaengerkurs', 'anmeldung_text', 'Bitte senden Sie uns eine E-Mail mit Name, Vorname, Alter, E-Mail-Adresse und Telefon (optional).']);
    $s->execute(['anfaengerkurs', 'anmeldung_email', 'bsv1960grundkurs@gmail.com']);

    // Imprint
    $s->execute(['imprint', 'content', '<h3>Angaben gemäß § 5 TMG</h3><p>Bogensportverein 1960 Plauen e.V.<br>Erich-Knauf-Str. 20<br>08525 Plauen</p><p>Telefon: 0152-541 366 36<br>E-Mail: info@bogensport-plauen.de<br>Internet: www.bogensport-plauen.de</p><h3>Vertretungsberechtigter Vorstand</h3><p>Florian Künzel (Vorsitzender)<br>Ronny Krauß<br>Anja Kus</p><p>Registergericht: Amtsgericht Plauen<br>Registernummer: VR 120</p><h3>Haftung für Inhalte</h3><p>Die Inhalte unserer Seiten wurden mit größter Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte können wir jedoch keine Gewähr übernehmen. Als Diensteanbieter sind wir gemäß § 7 Abs.1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.</p><h3>Haftung für Links</h3><p>Unser Angebot enthält Links zu externen Webseiten Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar.</p><h3>Urheberrecht</h3><p>Die durch die Seitenbetreiber erstellten Inhalte und Werke auf diesen Seiten unterliegen dem deutschen Urheberrecht. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechtes bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers.</p><p style="margin-top: 2rem; font-size: 0.85rem;">Quellen: eRecht24</p>']);

    // Datenschutz
    $s->execute(['datenschutz', 'content', '<p>Wir, der BSV 1960 Plauen e.V. sind Betreiber dieser Webseite. Wir sind somit verantwortlich für die Erhebung, Verarbeitung und Nutzung von personenbezogenen Daten im Sinne der EU Datenschutzgrundverordnung (DSGVO). Im Folgenden informieren wir über Art, Umfang und Zweck der Erhebung und Verwendung Ihrer personenbezogenen Daten auf unserem Internetauftritt.</p><h3>Personenbezogene Daten</h3><p>Personenbezogene Daten sind Informationen, die sich auf eine identifizierte oder identifizierbare natürliche Person beziehen, dies können z.B. Angaben wie Name, Adresse, Telefonnummer oder E-Mail-Adresse sein.</p><h3>Erhebung und Verarbeitung bei Nutzung des Kontaktformulars</h3><p>Bei der Nutzung des Kontaktformulars erheben wir personenbezogene Daten (Name, E-Mail-Adresse, Nachrichtentext) nur in dem von Ihnen zur Verfügung gestellten Umfang. Die Datenverarbeitung dient dem Zweck der Kontaktaufnahme. Mit Absenden Ihrer Nachricht willigen Sie in die Verarbeitung der übermittelten Daten ein. Die Verarbeitung erfolgt auf Grundlage des Art. 6 (1) lit. a DSGVO mit Ihrer Einwilligung.</p><h3>Links</h3><p>Sofern Sie externe Links nutzen, die im Rahmen unserer Webseite angeboten werden, erstreckt sich diese Datenschutzerklärung nicht auf diese Links. Insofern wir Links anbieten, versichern wir, dass zum Zeitpunkt der Linksetzung keine Verstöße gegen geltendes Recht erkennbar waren. Wir haben jedoch keinen Einfluss auf die Einhaltung der Datenschutz- und Sicherheitsbestimmungen durch andere Anbieter.</p><h3>Weitergabe personenbezogener Daten</h3><p>Eine Weitergabe Ihrer personenbezogenen Daten an Dritte erfolgt nicht ohne Ihre ausdrückliche Einwilligung.</p><h3>Rechte der betroffenen Person</h3><p>Als betroffene Person haben Sie folgende Rechte:</p><p>Gemäß Art. 15 DSGVO haben Sie das Recht, Auskunft über Ihre von uns verarbeiteten personenbezogenen Daten zu verlangen.</p><p>Gemäß Art. 16 DSGVO können Sie unverzüglich die Berichtigung unrichtiger oder die Vervollständigung Ihrer bei uns gespeicherten personenbezogenen Daten verlangen.</p><p>Gemäß Art. 17 DSGVO können Sie die Löschung Ihrer bei uns gespeicherten personenbezogenen Daten verlangen, soweit nicht die Verarbeitung zur Ausübung des Rechts auf freie Meinungsäußerung und Information, zur Erfüllung einer rechtlichen Verpflichtung oder aus Gründen des öffentlichen Interesses erforderlich ist.</p><p>Gemäß Art. 18 DSGVO können Sie die Einschränkung der Verarbeitung Ihrer personenbezogenen Daten verlangen.</p><p>Gemäß Art. 20 DSGVO können Sie Ihre personenbezogenen Daten, die Sie uns bereitgestellt haben, in einem strukturierten, gängigen und maschinenlesbaren Format erhalten oder die Übermittlung an einen anderen Verantwortlichen verlangen.</p><p>Gemäß Art. 21 DSGVO können Sie Widerspruch gegen die künftige Verarbeitung der Sie betreffenden Daten einlegen.</p><h3>Beschwerderecht bei einer Aufsichtsbehörde</h3><p>Sie haben das Recht, sich bei einer Aufsichtsbehörde über die Verarbeitung Ihrer personenbezogenen Daten durch uns zu beschweren.</p><h3>Fragen, Hinweise, Beschwerden</h3><p>Michael Kratzsch<br>Antonstr. 11<br>08523 Plauen<br>E-Mail: info@bogensport-plauen.de</p><h3>Widerspruch Werbe-Mails</h3><p>Der Nutzung von im Rahmen der Impressumspflicht veröffentlichten Kontaktdaten zur Übersendung von nicht ausdrücklich angeforderter Werbung und Informationsmaterialien wird hiermit widersprochen. Die Betreiber der Seiten behalten sich ausdrücklich rechtliche Schritte im Falle der unverlangten Zusendung von Werbeinformationen, etwa durch Spam-E-Mails, vor.</p>']);

    // Board members
    $db->exec("
        INSERT INTO board_members (name, role, email, image, sort_order) VALUES
        ('Florian Künzel', 'Vereinsvorsitzender', 'info@bogensport-plauen.de', 'images/index/2_f_kuenzel.jpg', 1),
        ('Ronny Krauß', 'stell. Vereinsvorsitzender', '', 'images/index/1_r_kraus.jpg', 2),
        ('Anja Kus', 'Schatzmeisterin', '', 'images/index/3_a_kus.jpg', 3)
    ");

    // Sponsors
    $db->exec("
        INSERT INTO sponsors (name, address, phone, website, sort_order) VALUES
        ('Tischlerei Fritzsch', 'Teichstraße 4a, 08527 Rößnitz', '037431 88288', 'https://www.tischlerei-fritzsch.de', 1),
        ('Elektrotechnik Plauen GmbH', 'Weststraße 63, 08523 Plauen', '03741 21 20', 'https://www.elektrotechnik-plauen.de', 2),
        ('Bauhaus Plauen', 'Äußere Reichenbacher Straße, 08529 Plauen', '03741 48 89 0', 'https://www.bauhaus.info', 3),
        ('Simba n3 GmbH', 'Dr.-Friedrichs-Straße 42, 08606 Oelsnitz', '037421 72 24 0', 'https://www.nhochdrei.de/de/', 4),
        ('S+K Ing.gmbH', 'Bergstraße 3, 08523 Plauen', '03741 131200', '', 5),
        ('Normann Lippert GmbH', 'Fedor-Schnorr-Straße 20, 08523 Plauen', '03741 70 77 73', '', 6),
        ('Lichtwelt Plauen', 'Dürerstraße 14, 08527 Plauen', '03741 40 65 91 0', '', 7)
    ");

    // Training seasons
    $db->exec("
        INSERT INTO training_seasons (title, date_range, location_name, location_address, location_image, note, sort_order) VALUES
        ('Sommertraining', '1. April bis 30. September', 'Bogensportplatz', 'Wolfsbergweg, 08525 Plauen', 'images/training/bogensportplatz_01.jpg', '', 1),
        ('Wintertraining', '1. Oktober bis 31. März', 'Turnhalle Rückertschule', 'Rückertstraße 33, 08525 Plauen', '', '* Alle anderen Schützen müssen bitte auf die anderen Trainingszeiten ausweichen', 2)
    ");

    // Training times
    $db->exec("
        INSERT INTO training_times (season_id, day, time_text, group_name, sort_order) VALUES
        (1, 'Montag', 'ab 17:00 Uhr', 'Bögen ohne Visier (Jagdschützen)', 1),
        (1, 'Dienstag', '17:00 – 18:30 Uhr', 'Kinder und Jugendmannschaften', 2),
        (1, 'Donnerstag', 'ab 18:00 Uhr', 'Bögen mit Visier (Scheibenschießen)', 3),
        (2, 'Montag', '16:30 – 18:00 Uhr', 'Erwachsene', 1),
        (2, 'Dienstag', '16:30 – 19:30 Uhr', 'Kinder und Jugendmannschaften*', 2),
        (2, 'Donnerstag', '18:00 – 20:15 Uhr', 'Erwachsene', 3),
        (2, 'Samstag', '10:00 – 12:15 Uhr', 'Offenes Training', 4)
    ");

    // Courses
    $db->exec("
        INSERT INTO courses (title, status_text, deadline, schedule_text, cost, equipment_note, sort_order) VALUES
        ('Erwachsene', 'Nur noch Warteliste!', 'Anmeldefrist für den Kurs 2026 ist der 31.03.2026', 'Mittwochs 18:30 – ca. 20:00 Uhr', '50 Euro', 'Bogen, Pfeile und Zubehör werden gestellt.', 1),
        ('Kinder (ab 10 Jahren) & Jugendliche', 'Nur noch Warteliste!', 'Anmeldefrist für den Kurs 2026 ist der 30.04.2026', 'Freitags 16:00 – 17:30 Uhr & Samstags 13:00 – 14:30 Uhr', '30 Euro', 'Bogen, Pfeile und Zubehör werden gestellt.', 2)
    ");

    // Course dates
    $db->exec("
        INSERT INTO course_dates (course_id, date_text, note, sort_order) VALUES
        (1, 'Mittwoch, 06. Mai 2026', '', 1),
        (1, 'Mittwoch, 13. Mai 2026', '', 2),
        (1, 'Mittwoch, 20. Mai 2026', '', 3),
        (1, 'Mittwoch, 27. Mai 2026', '', 4),
        (1, 'Mittwoch, 03. Juni 2026', '', 5),
        (1, 'Mittwoch, 10. Juni 2026', '', 6),
        (2, 'Freitag, 29. Mai 2026', '', 1),
        (2, 'Samstag, 30. Mai 2026', '', 2),
        (2, 'Freitag, 05. Juni 2026', '', 3),
        (2, 'Samstag, 06. Juni 2026', '', 4),
        (2, 'Freitag, 12. Juni 2026', 'ACHTUNG: 15:00 bis 16:30 Uhr!', 5),
        (2, 'Samstag, 13. Juni 2026', '', 6)
    ");

    // Info links
    $db->exec("
        INSERT INTO info_links (category, title, subtitle, url, sort_order) VALUES
        ('verbaende', 'Deutscher Bogenschützenverband (DBSV)', 'dbsv1959.de', 'https://www.dbsv1959.de/', 1),
        ('verbaende', 'Sächsischer Bogensportverband', 'sachsenbogen.de', 'https://www.sachsenbogen.de/sbv/index.php', 2),
        ('verbaende', 'Bogensportinfo', 'bogensportinfo.de', 'http://www.bogensportinfo.de/', 3),
        ('verbaende', 'Bogensportmagazin', 'bogensport-extra.de', 'https://www.bogensport-extra.de/index.php', 4),
        ('vereine', 'Bogensportclub Glauchau e.V.', 'Glauchau', 'https://www.the-bowmen.de/site/index.php', 1),
        ('vereine', 'SV Koweg Görlitz', 'Görlitz', 'http://www.sv-koweg.de/index.php?id=22', 2),
        ('vereine', 'SG Motor Gohlis-Nord Leipzig e.V.', 'Leipzig', 'https://www.mogono-bogen.de/', 3),
        ('vereine', 'Radeberger Sportverein e.V.', 'Radeberg', 'https://www.radebergersv-bogenschiessen.de/', 4),
        ('vereine', 'OSV Zittau e.V.', 'Zittau', 'http://www.osvzittau.de/bogensport.html', 5)
    ");
}

function requireAuth(): void {
    if (empty($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
}

function requireAdmin(): void {
    requireAuth();
    if ($_SESSION['user_role'] !== 'admin') {
        http_response_code(403);
        echo 'Zugriff verweigert';
        exit;
    }
}

function csrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCsrf(): void {
    $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    if (!hash_equals(csrfToken(), $token)) {
        http_response_code(403);
        echo 'Ungültiges CSRF-Token';
        exit;
    }
}

function sanitize(string $str): string {
    return htmlspecialchars(trim($str), ENT_QUOTES, 'UTF-8');
}

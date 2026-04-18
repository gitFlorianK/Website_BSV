<?php
require_once __DIR__ . '/config.php';
requireAuth();

$db = getDB();
$page = $_GET['page'] ?? '';
$error = '';
$success = '';

$pageLabels = [
    'index' => 'Startseite',
    'training' => 'Training',
    'anfaengerkurs' => 'Anfängerkurs',
    'sponsors' => 'Sponsoren',
    'contact' => 'Kontakt',
    'information' => 'Informationen',
    'imprint' => 'Impressum',
    'datenschutz' => 'Datenschutz',
];

if (!isset($pageLabels[$page])) {
    header('Location: pages.php');
    exit;
}

// Handle POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save_sections';

    if (in_array($action, ['delete_board', 'delete_sponsor', 'delete_course', 'delete_link'], true)) {
        requireAdmin();
    }

    switch ($action) {
        case 'save_sections':
            $keys = $_POST['section_key'] ?? [];
            $contents = $_POST['section_content'] ?? [];
            $stmt = $db->prepare('UPDATE page_sections SET content = ? WHERE page_key = ? AND section_key = ?');
            foreach ($keys as $i => $key) {
                $stmt->execute([trim($contents[$i] ?? ''), $page, $key]);
            }
            $success = 'Inhalte wurden gespeichert.';
            break;

        case 'save_title':
            $title = trim($_POST['page_title'] ?? '');
            $subtitle = trim($_POST['page_subtitle'] ?? '');
            if ($title) {
                $db->prepare('UPDATE page_titles SET title = ?, subtitle = ? WHERE page_key = ?')
                   ->execute([$title, $subtitle, $page]);
                $success = 'Seitentitel wurde gespeichert.';
            }
            break;

        // Board members
        case 'save_board':
            $ids = $_POST['member_id'] ?? [];
            $names = $_POST['member_name'] ?? [];
            $roles = $_POST['member_role'] ?? [];
            $emails = $_POST['member_email'] ?? [];
            $images = $_POST['member_image'] ?? [];
            $stmt = $db->prepare('UPDATE board_members SET name = ?, role = ?, email = ?, image = ? WHERE id = ?');
            foreach ($ids as $i => $id) {
                $stmt->execute([trim($names[$i]), trim($roles[$i]), trim($emails[$i] ?? ''), trim($images[$i] ?? ''), (int)$id]);
            }
            $success = 'Vorstand wurde gespeichert.';
            break;

        case 'add_board':
            $db->prepare('INSERT INTO board_members (name, role, email, image, sort_order) VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM board_members))')
               ->execute([trim($_POST['new_name']), trim($_POST['new_role']), trim($_POST['new_email'] ?? ''), trim($_POST['new_image'] ?? '')]);
            $success = 'Vorstandsmitglied wurde hinzugefügt.';
            break;

        case 'delete_board':
            $db->prepare('DELETE FROM board_members WHERE id = ?')->execute([(int)$_POST['member_id']]);
            $success = 'Vorstandsmitglied wurde gelöscht.';
            break;

        // Sponsors
        case 'save_sponsors':
            $ids = $_POST['sponsor_id'] ?? [];
            $names = $_POST['sponsor_name'] ?? [];
            $addrs = $_POST['sponsor_address'] ?? [];
            $phones = $_POST['sponsor_phone'] ?? [];
            $websites = $_POST['sponsor_website'] ?? [];
            $stmt = $db->prepare('UPDATE sponsors SET name = ?, address = ?, phone = ?, website = ? WHERE id = ?');
            foreach ($ids as $i => $id) {
                $stmt->execute([trim($names[$i]), trim($addrs[$i]), trim($phones[$i] ?? ''), trim($websites[$i] ?? ''), (int)$id]);
            }
            $success = 'Sponsoren wurden gespeichert.';
            break;

        case 'add_sponsor':
            $db->prepare('INSERT INTO sponsors (name, address, phone, website, sort_order) VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM sponsors))')
               ->execute([trim($_POST['new_name']), trim($_POST['new_address']), trim($_POST['new_phone'] ?? ''), trim($_POST['new_website'] ?? '')]);
            $success = 'Sponsor wurde hinzugefügt.';
            break;

        case 'delete_sponsor':
            $db->prepare('DELETE FROM sponsors WHERE id = ?')->execute([(int)$_POST['sponsor_id']]);
            $success = 'Sponsor wurde gelöscht.';
            break;

        // Training
        case 'save_training':
            $sIds = $_POST['season_id'] ?? [];
            $sTitles = $_POST['season_title'] ?? [];
            $sRanges = $_POST['season_date_range'] ?? [];
            $sLocNames = $_POST['season_location_name'] ?? [];
            $sLocAddrs = $_POST['season_location_address'] ?? [];
            $sLocImgs = $_POST['season_location_image'] ?? [];
            $sNotes = $_POST['season_note'] ?? [];
            $stmt = $db->prepare('UPDATE training_seasons SET title = ?, date_range = ?, location_name = ?, location_address = ?, location_image = ?, note = ? WHERE id = ?');
            foreach ($sIds as $i => $id) {
                $stmt->execute([trim($sTitles[$i]), trim($sRanges[$i]), trim($sLocNames[$i]), trim($sLocAddrs[$i]), trim($sLocImgs[$i] ?? ''), trim($sNotes[$i] ?? ''), (int)$id]);
            }
            // Delete old times and re-insert
            foreach ($sIds as $id) {
                $db->prepare('DELETE FROM training_times WHERE season_id = ?')->execute([(int)$id]);
            }
            $tSeasons = $_POST['time_season_id'] ?? [];
            $tDays = $_POST['time_day'] ?? [];
            $tTimes = $_POST['time_text'] ?? [];
            $tGroups = $_POST['time_group'] ?? [];
            $insStmt = $db->prepare('INSERT INTO training_times (season_id, day, time_text, group_name, sort_order) VALUES (?, ?, ?, ?, ?)');
            $sortCounters = [];
            foreach ($tSeasons as $i => $sid) {
                if (empty(trim($tDays[$i]))) continue;
                $sortCounters[$sid] = ($sortCounters[$sid] ?? 0) + 1;
                $insStmt->execute([(int)$sid, trim($tDays[$i]), trim($tTimes[$i]), trim($tGroups[$i]), $sortCounters[$sid]]);
            }
            $success = 'Trainingszeiten wurden gespeichert.';
            break;

        // Courses
        case 'save_courses':
            $cIds = $_POST['course_id'] ?? [];
            $cTitles = $_POST['course_title'] ?? [];
            $cStatuses = $_POST['course_status_text'] ?? [];
            $cDeadlines = $_POST['course_deadline'] ?? [];
            $cSchedules = $_POST['course_schedule_text'] ?? [];
            $cCosts = $_POST['course_cost'] ?? [];
            $cEquipments = $_POST['course_equipment_note'] ?? [];
            $stmt = $db->prepare('UPDATE courses SET title = ?, status_text = ?, deadline = ?, schedule_text = ?, cost = ?, equipment_note = ? WHERE id = ?');
            foreach ($cIds as $i => $id) {
                $stmt->execute([trim($cTitles[$i]), trim($cStatuses[$i] ?? ''), trim($cDeadlines[$i] ?? ''), trim($cSchedules[$i] ?? ''), trim($cCosts[$i] ?? ''), trim($cEquipments[$i] ?? ''), (int)$id]);
            }
            // Delete and re-insert dates
            foreach ($cIds as $id) {
                $db->prepare('DELETE FROM course_dates WHERE course_id = ?')->execute([(int)$id]);
            }
            $dCourseIds = $_POST['date_course_id'] ?? [];
            $dTexts = $_POST['date_text'] ?? [];
            $dNotes = $_POST['date_note'] ?? [];
            $dStmt = $db->prepare('INSERT INTO course_dates (course_id, date_text, note, sort_order) VALUES (?, ?, ?, ?)');
            $sortC = [];
            foreach ($dCourseIds as $i => $cid) {
                if (empty(trim($dTexts[$i]))) continue;
                $sortC[$cid] = ($sortC[$cid] ?? 0) + 1;
                $dStmt->execute([(int)$cid, trim($dTexts[$i]), trim($dNotes[$i] ?? ''), $sortC[$cid]]);
            }
            $success = 'Kurse wurden gespeichert.';
            break;

        case 'add_course':
            $db->prepare('INSERT INTO courses (title, status_text, deadline, schedule_text, cost, equipment_note, sort_order) VALUES (?, ?, ?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM courses))')
               ->execute([trim($_POST['new_title']), trim($_POST['new_status'] ?? ''), trim($_POST['new_deadline'] ?? ''), trim($_POST['new_schedule'] ?? ''), trim($_POST['new_cost'] ?? ''), trim($_POST['new_equipment'] ?? '')]);
            $success = 'Kurs wurde hinzugefügt.';
            break;

        case 'delete_course':
            $db->prepare('DELETE FROM courses WHERE id = ?')->execute([(int)$_POST['course_id']]);
            $success = 'Kurs wurde gelöscht.';
            break;

        // Info links
        case 'save_links':
            $lIds = $_POST['link_id'] ?? [];
            $lTitles = $_POST['link_title'] ?? [];
            $lSubtitles = $_POST['link_subtitle'] ?? [];
            $lUrls = $_POST['link_url'] ?? [];
            $lCats = $_POST['link_category'] ?? [];
            $stmt = $db->prepare('UPDATE info_links SET title = ?, subtitle = ?, url = ?, category = ? WHERE id = ?');
            foreach ($lIds as $i => $id) {
                $stmt->execute([trim($lTitles[$i]), trim($lSubtitles[$i] ?? ''), trim($lUrls[$i]), trim($lCats[$i]), (int)$id]);
            }
            $success = 'Links wurden gespeichert.';
            break;

        case 'add_link':
            $db->prepare('INSERT INTO info_links (category, title, subtitle, url, sort_order) VALUES (?, ?, ?, ?, (SELECT COALESCE(MAX(sort_order),0)+1 FROM info_links WHERE category = ?))')
               ->execute([trim($_POST['new_category']), trim($_POST['new_title']), trim($_POST['new_subtitle'] ?? ''), trim($_POST['new_url']), trim($_POST['new_category'])]);
            $success = 'Link wurde hinzugefügt.';
            break;

        case 'delete_link':
            $db->prepare('DELETE FROM info_links WHERE id = ?')->execute([(int)$_POST['link_id']]);
            $success = 'Link wurde gelöscht.';
            break;
    }
}

// Load current data
$pageTitle = $db->prepare('SELECT title, subtitle FROM page_titles WHERE page_key = ?');
$pageTitle->execute([$page]);
$pageTitle = $pageTitle->fetch();

$sections = $db->prepare('SELECT section_key, content FROM page_sections WHERE page_key = ?');
$sections->execute([$page]);
$sections = $sections->fetchAll();

// Section labels for display
$sectionLabels = [
    'willkommen' => 'Willkommenstext',
    'cta_title' => 'CTA Titel',
    'cta_text' => 'CTA Text',
    'intro' => 'Einleitungstext',
    'org_name' => 'Vereinsname',
    'contact_person' => 'Ansprechpartner',
    'street' => 'Straße',
    'city' => 'PLZ / Ort',
    'phone' => 'Telefon',
    'phone_note' => 'Telefon-Hinweis',
    'email' => 'E-Mail',
    'anmeldung_text' => 'Anmeldungstext',
    'anmeldung_email' => 'Anmeldungs-E-Mail',
    'content' => 'Inhalt',
];
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= sanitize($pageLabels[$page]) ?> bearbeiten | CMS</title>
    <link rel="stylesheet" href="css/admin.css">
</head>
<body>
    <?php include __DIR__ . '/partials/nav.php'; ?>

    <main class="admin-main">
        <div class="admin-container">
            <div class="section-header">
                <h1><?= sanitize($pageLabels[$page]) ?> bearbeiten</h1>
                <a href="pages.php" class="btn btn-outline">Zurück</a>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?= sanitize($error) ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?= sanitize($success) ?></div>
            <?php endif; ?>

            <!-- Page Title -->
            <div class="card admin-card">
                <h2>Seitentitel (Hero-Bereich)</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="save_title">
                    <div class="form-row">
                        <div class="form-group form-group-wide">
                            <label>Titel</label>
                            <input type="text" name="page_title" value="<?= sanitize($pageTitle['title'] ?? '') ?>" required>
                        </div>
                        <div class="form-group form-group-wide">
                            <label>Untertitel</label>
                            <input type="text" name="page_subtitle" value="<?= sanitize($pageTitle['subtitle'] ?? '') ?>">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Titel speichern</button>
                </form>
            </div>

            <!-- Page Sections (text content) -->
            <?php if (!empty($sections)): ?>
            <div class="card admin-card">
                <h2>Textinhalte</h2>
                <form method="post">
                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                    <input type="hidden" name="action" value="save_sections">
                    <?php foreach ($sections as $sec): ?>
                    <div class="form-group">
                        <label><?= sanitize($sectionLabels[$sec['section_key']] ?? $sec['section_key']) ?></label>
                        <input type="hidden" name="section_key[]" value="<?= sanitize($sec['section_key']) ?>">
                        <?php if (strlen($sec['content']) > 200 || strpos($sec['content'], '<') !== false): ?>
                            <textarea name="section_content[]" rows="<?= min(20, max(4, substr_count($sec['content'], '<') + 3)) ?>"><?= sanitize($sec['content']) ?></textarea>
                            <small class="form-help">HTML erlaubt: &lt;p&gt;, &lt;h3&gt;, &lt;br&gt;, &lt;a&gt;, &lt;strong&gt;</small>
                        <?php else: ?>
                            <input type="text" name="section_content[]" value="<?= sanitize($sec['content']) ?>">
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                    <button type="submit" class="btn btn-primary">Inhalte speichern</button>
                </form>
            </div>
            <?php endif; ?>

            <?php /* ===== PAGE-SPECIFIC SECTIONS ===== */ ?>

            <?php if ($page === 'index'): ?>
                <?php
                $members = $db->query('SELECT * FROM board_members ORDER BY sort_order')->fetchAll();
                ?>
                <div class="card admin-card">
                    <h2>Vorstand</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="save_board">
                        <?php foreach ($members as $m): ?>
                        <div class="card admin-card" style="background: var(--bg-dark);">
                            <input type="hidden" name="member_id[]" value="<?= $m['id'] ?>">
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Name</label>
                                    <input type="text" name="member_name[]" value="<?= sanitize($m['name']) ?>" required>
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Rolle</label>
                                    <input type="text" name="member_role[]" value="<?= sanitize($m['role']) ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>E-Mail</label>
                                    <input type="text" name="member_email[]" value="<?= sanitize($m['email']) ?>">
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Bildpfad</label>
                                    <input type="text" name="member_image[]" value="<?= sanitize($m['image']) ?>" placeholder="z.B. images/index/foto.jpg">
                                </div>
                            </div>
                            <form method="post" class="inline-form" onsubmit="return confirm('Mitglied wirklich löschen?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="delete_board">
                                <input type="hidden" name="member_id" value="<?= $m['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary">Vorstand speichern</button>
                    </form>

                    <h3 style="margin-top: 2rem;">Neues Mitglied hinzufügen</h3>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="add_board">
                        <div class="form-row">
                            <div class="form-group form-group-wide">
                                <label>Name</label>
                                <input type="text" name="new_name" required>
                            </div>
                            <div class="form-group form-group-wide">
                                <label>Rolle</label>
                                <input type="text" name="new_role" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group form-group-wide">
                                <label>E-Mail</label>
                                <input type="text" name="new_email">
                            </div>
                            <div class="form-group form-group-wide">
                                <label>Bildpfad</label>
                                <input type="text" name="new_image" placeholder="z.B. images/index/foto.jpg">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Hinzufügen</button>
                    </form>
                </div>

            <?php elseif ($page === 'training'): ?>
                <?php
                $seasons = $db->query('SELECT * FROM training_seasons ORDER BY sort_order')->fetchAll();
                foreach ($seasons as &$season) {
                    $tStmt = $db->prepare('SELECT * FROM training_times WHERE season_id = ? ORDER BY sort_order');
                    $tStmt->execute([$season['id']]);
                    $season['times'] = $tStmt->fetchAll();
                }
                unset($season);
                ?>
                <div class="card admin-card">
                    <h2>Trainingszeiten</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="save_training">

                        <?php foreach ($seasons as $season): ?>
                        <div class="card admin-card" style="background: var(--bg-dark);">
                            <input type="hidden" name="season_id[]" value="<?= $season['id'] ?>">
                            <h3><?= sanitize($season['title']) ?></h3>
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Titel</label>
                                    <input type="text" name="season_title[]" value="<?= sanitize($season['title']) ?>" required>
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Zeitraum</label>
                                    <input type="text" name="season_date_range[]" value="<?= sanitize($season['date_range']) ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Standort-Name</label>
                                    <input type="text" name="season_location_name[]" value="<?= sanitize($season['location_name']) ?>">
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Standort-Adresse</label>
                                    <input type="text" name="season_location_address[]" value="<?= sanitize($season['location_address']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Standort-Bild</label>
                                    <input type="text" name="season_location_image[]" value="<?= sanitize($season['location_image']) ?>" placeholder="z.B. images/training/foto.jpg">
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Hinweis</label>
                                    <input type="text" name="season_note[]" value="<?= sanitize($season['note']) ?>">
                                </div>
                            </div>

                            <h4 style="margin: 1rem 0 0.5rem;">Zeiten</h4>
                            <div class="training-times-block" data-season="<?= $season['id'] ?>">
                                <?php foreach ($season['times'] as $t): ?>
                                <div class="form-row time-row">
                                    <input type="hidden" name="time_season_id[]" value="<?= $season['id'] ?>">
                                    <div class="form-group">
                                        <input type="text" name="time_day[]" value="<?= sanitize($t['day']) ?>" placeholder="Tag">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="time_text[]" value="<?= sanitize($t['time_text']) ?>" placeholder="Zeit">
                                    </div>
                                    <div class="form-group form-group-wide">
                                        <input type="text" name="time_group[]" value="<?= sanitize($t['group_name']) ?>" placeholder="Gruppe">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-row-btn" onclick="this.closest('.time-row').remove()">&#10005;</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm" onclick="addTimeRow(<?= $season['id'] ?>)">+ Zeile hinzufügen</button>
                        </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary">Training speichern</button>
                    </form>
                </div>

            <?php elseif ($page === 'anfaengerkurs'): ?>
                <?php
                $courses = $db->query('SELECT * FROM courses ORDER BY sort_order')->fetchAll();
                foreach ($courses as &$course) {
                    $dStmt = $db->prepare('SELECT * FROM course_dates WHERE course_id = ? ORDER BY sort_order');
                    $dStmt->execute([$course['id']]);
                    $course['dates'] = $dStmt->fetchAll();
                }
                unset($course);
                ?>
                <div class="card admin-card">
                    <h2>Kurse</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="save_courses">

                        <?php foreach ($courses as $course): ?>
                        <div class="card admin-card" style="background: var(--bg-dark);">
                            <input type="hidden" name="course_id[]" value="<?= $course['id'] ?>">
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Kurstitel</label>
                                    <input type="text" name="course_title[]" value="<?= sanitize($course['title']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label>Status</label>
                                    <input type="text" name="course_status_text[]" value="<?= sanitize($course['status_text']) ?>" placeholder="z.B. Nur noch Warteliste!">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Anmeldefrist</label>
                                    <input type="text" name="course_deadline[]" value="<?= sanitize($course['deadline']) ?>">
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Zeitplan</label>
                                    <input type="text" name="course_schedule_text[]" value="<?= sanitize($course['schedule_text']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Kosten</label>
                                    <input type="text" name="course_cost[]" value="<?= sanitize($course['cost']) ?>">
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Ausstattungs-Hinweis</label>
                                    <input type="text" name="course_equipment_note[]" value="<?= sanitize($course['equipment_note']) ?>">
                                </div>
                            </div>

                            <h4 style="margin: 1rem 0 0.5rem;">Termine</h4>
                            <div class="course-dates-block" data-course="<?= $course['id'] ?>">
                                <?php foreach ($course['dates'] as $d): ?>
                                <div class="form-row date-row">
                                    <input type="hidden" name="date_course_id[]" value="<?= $course['id'] ?>">
                                    <div class="form-group form-group-wide">
                                        <input type="text" name="date_text[]" value="<?= sanitize($d['date_text']) ?>" placeholder="z.B. Mittwoch, 06. Mai 2026">
                                    </div>
                                    <div class="form-group">
                                        <input type="text" name="date_note[]" value="<?= sanitize($d['note']) ?>" placeholder="Hinweis (optional)">
                                    </div>
                                    <button type="button" class="btn btn-sm btn-danger remove-row-btn" onclick="this.closest('.date-row').remove()">&#10005;</button>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <button type="button" class="btn btn-sm" onclick="addDateRow(<?= $course['id'] ?>)">+ Termin hinzufügen</button>

                            <div style="margin-top: 1rem;">
                                <form method="post" class="inline-form" onsubmit="return confirm('Kurs wirklich löschen?')">
                                    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                    <input type="hidden" name="action" value="delete_course">
                                    <input type="hidden" name="course_id" value="<?= $course['id'] ?>">
                                    <button type="submit" class="btn btn-sm btn-danger">Kurs löschen</button>
                                </form>
                            </div>
                        </div>
                        <?php endforeach; ?>

                        <button type="submit" class="btn btn-primary">Kurse speichern</button>
                    </form>

                    <h3 style="margin-top: 2rem;">Neuen Kurs hinzufügen</h3>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="add_course">
                        <div class="form-row">
                            <div class="form-group form-group-wide">
                                <label>Titel</label>
                                <input type="text" name="new_title" required placeholder="z.B. Erwachsene">
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <input type="text" name="new_status" placeholder="z.B. Anmeldung offen">
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group form-group-wide"><label>Anmeldefrist</label><input type="text" name="new_deadline"></div>
                            <div class="form-group form-group-wide"><label>Zeitplan</label><input type="text" name="new_schedule"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Kosten</label><input type="text" name="new_cost"></div>
                            <div class="form-group form-group-wide"><label>Ausstattungs-Hinweis</label><input type="text" name="new_equipment"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Kurs hinzufügen</button>
                    </form>
                </div>

            <?php elseif ($page === 'sponsors'): ?>
                <?php $sponsors = $db->query('SELECT * FROM sponsors ORDER BY sort_order')->fetchAll(); ?>
                <div class="card admin-card">
                    <h2>Sponsoren</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="save_sponsors">
                        <?php foreach ($sponsors as $sp): ?>
                        <div class="card admin-card" style="background: var(--bg-dark);">
                            <input type="hidden" name="sponsor_id[]" value="<?= $sp['id'] ?>">
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Name</label>
                                    <input type="text" name="sponsor_name[]" value="<?= sanitize($sp['name']) ?>" required>
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Adresse</label>
                                    <input type="text" name="sponsor_address[]" value="<?= sanitize($sp['address']) ?>">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Telefon</label>
                                    <input type="text" name="sponsor_phone[]" value="<?= sanitize($sp['phone']) ?>">
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>Website</label>
                                    <input type="text" name="sponsor_website[]" value="<?= sanitize($sp['website']) ?>" placeholder="https://...">
                                </div>
                            </div>
                            <form method="post" class="inline-form" onsubmit="return confirm('Sponsor wirklich löschen?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="delete_sponsor">
                                <input type="hidden" name="sponsor_id" value="<?= $sp['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary">Sponsoren speichern</button>
                    </form>

                    <h3 style="margin-top: 2rem;">Neuen Sponsor hinzufügen</h3>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="add_sponsor">
                        <div class="form-row">
                            <div class="form-group form-group-wide"><label>Name</label><input type="text" name="new_name" required></div>
                            <div class="form-group form-group-wide"><label>Adresse</label><input type="text" name="new_address"></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Telefon</label><input type="text" name="new_phone"></div>
                            <div class="form-group form-group-wide"><label>Website</label><input type="text" name="new_website" placeholder="https://..."></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Hinzufügen</button>
                    </form>
                </div>

            <?php elseif ($page === 'information'): ?>
                <?php
                $links = $db->query("SELECT * FROM info_links WHERE category = 'verbaende' ORDER BY sort_order")->fetchAll();
                ?>
                <div class="card admin-card">
                    <h2>Externe Links</h2>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="save_links">
                        <?php foreach ($links as $link): ?>
                        <div class="card admin-card" style="background: var(--bg-dark);">
                            <input type="hidden" name="link_id[]" value="<?= $link['id'] ?>">
                            <input type="hidden" name="link_category[]" value="verbaende">
                            <div class="form-row">
                                <div class="form-group form-group-wide">
                                    <label>Titel</label>
                                    <input type="text" name="link_title[]" value="<?= sanitize($link['title']) ?>" required>
                                </div>
                                <div class="form-group form-group-wide">
                                    <label>URL</label>
                                    <input type="text" name="link_url[]" value="<?= sanitize($link['url']) ?>" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Untertitel</label>
                                    <input type="text" name="link_subtitle[]" value="<?= sanitize($link['subtitle']) ?>" placeholder="z.B. Domain">
                                </div>
                            </div>
                            <form method="post" class="inline-form" onsubmit="return confirm('Link wirklich löschen?')">
                                <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                                <input type="hidden" name="action" value="delete_link">
                                <input type="hidden" name="link_id" value="<?= $link['id'] ?>">
                                <button type="submit" class="btn btn-sm btn-danger">Löschen</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                        <button type="submit" class="btn btn-primary">Links speichern</button>
                    </form>

                    <h3 style="margin-top: 2rem;">Neuen Link hinzufügen</h3>
                    <form method="post">
                        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
                        <input type="hidden" name="action" value="add_link">
                        <input type="hidden" name="new_category" value="verbaende">
                        <div class="form-row">
                            <div class="form-group form-group-wide"><label>Titel</label><input type="text" name="new_title" required></div>
                            <div class="form-group form-group-wide"><label>URL</label><input type="text" name="new_url" required placeholder="https://..."></div>
                        </div>
                        <div class="form-row">
                            <div class="form-group"><label>Untertitel</label><input type="text" name="new_subtitle" placeholder="Domain"></div>
                        </div>
                        <button type="submit" class="btn btn-primary">Hinzufügen</button>
                    </form>
                </div>

            <?php endif; ?>
        </div>
    </main>

    <script>
    function addTimeRow(seasonId) {
        const block = document.querySelector(`.training-times-block[data-season="${seasonId}"]`);
        const row = document.createElement('div');
        row.className = 'form-row time-row';
        row.innerHTML = `
            <input type="hidden" name="time_season_id[]" value="${seasonId}">
            <div class="form-group"><input type="text" name="time_day[]" placeholder="Tag"></div>
            <div class="form-group"><input type="text" name="time_text[]" placeholder="Zeit"></div>
            <div class="form-group form-group-wide"><input type="text" name="time_group[]" placeholder="Gruppe"></div>
            <button type="button" class="btn btn-sm btn-danger remove-row-btn" onclick="this.closest('.time-row').remove()">&#10005;</button>
        `;
        block.appendChild(row);
    }

    function addDateRow(courseId) {
        const block = document.querySelector(`.course-dates-block[data-course="${courseId}"]`);
        const row = document.createElement('div');
        row.className = 'form-row date-row';
        row.innerHTML = `
            <input type="hidden" name="date_course_id[]" value="${courseId}">
            <div class="form-group form-group-wide"><input type="text" name="date_text[]" placeholder="z.B. Mittwoch, 06. Mai 2026"></div>
            <div class="form-group"><input type="text" name="date_note[]" placeholder="Hinweis (optional)"></div>
            <button type="button" class="btn btn-sm btn-danger remove-row-btn" onclick="this.closest('.date-row').remove()">&#10005;</button>
        `;
        block.appendChild(row);
    }
    </script>
</body>
</html>

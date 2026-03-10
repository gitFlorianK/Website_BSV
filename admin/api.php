<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

// CORS: only allow same origin (no cross-origin API access)
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$serverHost = $_SERVER['HTTP_HOST'] ?? '';
if ($origin && parse_url($origin, PHP_URL_HOST) === $serverHost) {
    header('Access-Control-Allow-Origin: ' . $origin);
}

$db = getDB();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'posts':
        $type = $_GET['type'] ?? '';
        $limit = min(max((int)($_GET['limit'] ?? 50), 1), 200);
        $offset = max((int)($_GET['offset'] ?? 0), 0);
        $where = 'WHERE p.published = 1';
        $params = [];

        if ($type === 'blog' || $type === 'gallery') {
            $where .= ' AND p.type = ?';
            $params[] = $type;
        }

        $params[] = $limit;
        $params[] = $offset;

        $stmt = $db->prepare("
            SELECT p.id, p.type, p.title, p.content, p.category, p.created_at,
                   u.display_name as author
            FROM posts p
            JOIN users u ON p.author_id = u.id
            $where
            ORDER BY p.created_at DESC
            LIMIT ? OFFSET ?
        ");
        $stmt->execute($params);
        $posts = $stmt->fetchAll();

        // Attach images to each post
        foreach ($posts as &$post) {
            $imgStmt = $db->prepare('SELECT id, filename, alt_text FROM images WHERE post_id = ? ORDER BY sort_order');
            $imgStmt->execute([$post['id']]);
            $post['images'] = $imgStmt->fetchAll();
        }

        echo json_encode($posts, JSON_UNESCAPED_UNICODE);
        break;

    case 'post':
        $id = (int)($_GET['id'] ?? 0);
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'Parameter id fehlt'], JSON_UNESCAPED_UNICODE);
            break;
        }
        $stmt = $db->prepare("
            SELECT p.id, p.type, p.title, p.content, p.category, p.created_at,
                   u.display_name as author
            FROM posts p
            JOIN users u ON p.author_id = u.id
            WHERE p.id = ? AND p.published = 1
        ");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if (!$post) {
            http_response_code(404);
            echo json_encode(['error' => 'Beitrag nicht gefunden'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $imgStmt = $db->prepare('SELECT id, filename, alt_text FROM images WHERE post_id = ? ORDER BY sort_order');
        $imgStmt->execute([$post['id']]);
        $post['images'] = $imgStmt->fetchAll();

        echo json_encode($post, JSON_UNESCAPED_UNICODE);
        break;

    case 'events':
        $stmt = $db->query('SELECT id, title, date_text, description FROM events WHERE published = 1 ORDER BY sort_order ASC');
        echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
        break;

    case 'custom_page':
        $slug = $_GET['slug'] ?? '';
        if (!$slug) {
            http_response_code(400);
            echo json_encode(['error' => 'Parameter slug fehlt'], JSON_UNESCAPED_UNICODE);
            break;
        }
        $stmt = $db->prepare('SELECT id, slug, title, subtitle, content FROM custom_pages WHERE slug = ? AND published = 1');
        $stmt->execute([$slug]);
        $page = $stmt->fetch();
        if (!$page) {
            http_response_code(404);
            echo json_encode(['error' => 'Seite nicht gefunden'], JSON_UNESCAPED_UNICODE);
            break;
        }
        echo json_encode($page, JSON_UNESCAPED_UNICODE);
        break;

    case 'custom_pages_nav':
        $stmt = $db->query('SELECT slug, nav_label, title FROM custom_pages WHERE published = 1 AND show_in_nav = 1 ORDER BY sort_order');
        echo json_encode($stmt->fetchAll(), JSON_UNESCAPED_UNICODE);
        break;

    case 'page_titles':
        $stmt = $db->query('SELECT page_key, title, subtitle FROM page_titles ORDER BY page_key');
        $rows = $stmt->fetchAll();
        $result = [];
        foreach ($rows as $row) {
            $result[$row['page_key']] = ['title' => $row['title'], 'subtitle' => $row['subtitle']];
        }
        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    case 'page_data':
        $page = $_GET['page'] ?? '';
        if (!$page) {
            http_response_code(400);
            echo json_encode(['error' => 'Parameter page fehlt'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $validPages = ['index', 'training', 'anfaengerkurs', 'sponsors', 'contact', 'information', 'imprint', 'datenschutz', 'aktuelles'];
        if (!in_array($page, $validPages)) {
            http_response_code(404);
            echo json_encode(['error' => 'Unbekannte Seite'], JSON_UNESCAPED_UNICODE);
            break;
        }

        $result = [];

        // Page title
        $stmt = $db->prepare('SELECT title, subtitle FROM page_titles WHERE page_key = ?');
        $stmt->execute([$page]);
        $result['title'] = $stmt->fetch() ?: null;

        // Page sections
        $stmt = $db->prepare('SELECT section_key, content FROM page_sections WHERE page_key = ?');
        $stmt->execute([$page]);
        $sections = [];
        foreach ($stmt->fetchAll() as $row) {
            $sections[$row['section_key']] = $row['content'];
        }
        $result['sections'] = $sections;

        // Page-specific structured data
        switch ($page) {
            case 'index':
                $result['board_members'] = $db->query('SELECT id, name, role, email, image FROM board_members ORDER BY sort_order')->fetchAll();
                break;

            case 'training':
                $seasons = $db->query('SELECT * FROM training_seasons ORDER BY sort_order')->fetchAll();
                foreach ($seasons as &$season) {
                    $tStmt = $db->prepare('SELECT day, time_text, group_name FROM training_times WHERE season_id = ? ORDER BY sort_order');
                    $tStmt->execute([$season['id']]);
                    $season['times'] = $tStmt->fetchAll();
                }
                $result['seasons'] = $seasons;
                break;

            case 'anfaengerkurs':
                $courses = $db->query('SELECT * FROM courses ORDER BY sort_order')->fetchAll();
                foreach ($courses as &$course) {
                    $dStmt = $db->prepare('SELECT date_text, note FROM course_dates WHERE course_id = ? ORDER BY sort_order');
                    $dStmt->execute([$course['id']]);
                    $course['dates'] = $dStmt->fetchAll();
                }
                $result['courses'] = $courses;
                break;

            case 'sponsors':
                $result['sponsors'] = $db->query('SELECT id, name, address, phone, website FROM sponsors ORDER BY sort_order')->fetchAll();
                break;

            case 'information':
                $links = $db->query('SELECT id, category, title, subtitle, url FROM info_links ORDER BY category, sort_order')->fetchAll();
                $grouped = ['verbaende' => [], 'vereine' => []];
                foreach ($links as $link) {
                    $grouped[$link['category']][] = $link;
                }
                $result['links'] = $grouped;
                break;
        }

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unbekannte Aktion'], JSON_UNESCAPED_UNICODE);
}

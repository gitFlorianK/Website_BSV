<?php
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$db = getDB();

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'posts':
        $type = $_GET['type'] ?? '';
        $where = 'WHERE p.published = 1';
        $params = [];

        if ($type === 'blog' || $type === 'gallery') {
            $where .= ' AND p.type = ?';
            $params[] = $type;
        }

        $stmt = $db->prepare("
            SELECT p.id, p.type, p.title, p.content, p.category, p.created_at,
                   u.display_name as author
            FROM posts p
            JOIN users u ON p.author_id = u.id
            $where
            ORDER BY p.created_at DESC
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
        $stmt = $db->prepare("
            SELECT p.id, p.type, p.title, p.content, p.category, p.created_at,
                   u.display_name as author
            FROM posts p
            JOIN users u ON p.author_id = u.id
            WHERE p.id = ? AND p.published = 1
        ");
        $stmt->execute([$id]);
        $post = $stmt->fetch();

        if ($post) {
            $imgStmt = $db->prepare('SELECT id, filename, alt_text FROM images WHERE post_id = ? ORDER BY sort_order');
            $imgStmt->execute([$post['id']]);
            $post['images'] = $imgStmt->fetchAll();
        }

        echo json_encode($post ?: null, JSON_UNESCAPED_UNICODE);
        break;

    case 'events':
        $stmt = $db->query('SELECT id, title, date_text, description FROM events WHERE published = 1 ORDER BY sort_order ASC');
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

    default:
        http_response_code(400);
        echo json_encode(['error' => 'Unbekannte Aktion']);
}

<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_out(['success' => true, 'content' => fetch_content($pdo)]);
}

if ($method === 'POST') {
    $action = $_GET['action'] ?? '';

    if ($action === 'reset') {
        $pdo->exec('DELETE FROM content');
        $now = gmdate('c');
        $stmt = $pdo->prepare('INSERT INTO content (key, value, updated_at) VALUES (:key, :value, :updated_at)');
        foreach (default_content() as $key => $value) {
            $stmt->execute([':key' => $key, ':value' => $value, ':updated_at' => $now]);
        }
        json_out(['success' => true, 'content' => fetch_content($pdo)]);
    }

    $data = parse_json_input();
    $content = is_array($data['content'] ?? null) ? $data['content'] : [];
    if (!$content) {
        json_out(['success' => false, 'message' => 'Missing content payload.'], 400);
    }

    $pdo->beginTransaction();
    try {
        $now = gmdate('c');
        $stmt = $pdo->prepare('INSERT INTO content (key, value, updated_at) VALUES (:key, :value, :updated_at) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=excluded.updated_at');
        foreach ($content as $key => $value) {
            $stmt->execute([
                ':key' => (string)$key,
                ':value' => (string)$value,
                ':updated_at' => $now,
            ]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        json_out(['success' => false, 'message' => 'Failed to save content.'], 500);
    }

    json_out(['success' => true, 'content' => fetch_content($pdo)]);
}

json_out(['success' => false, 'message' => 'Method not allowed'], 405);

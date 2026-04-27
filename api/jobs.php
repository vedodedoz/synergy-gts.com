<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_out(['success' => true, 'jobs' => fetch_jobs($pdo)]);
}

if ($method === 'POST' || $method === 'PUT') {
    $data = parse_json_input();

    $id = trim((string)($data['id'] ?? ''));
    if ($id === '') {
        $id = 'job_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 6);
    }

    $title = trim((string)($data['title'] ?? ''));
    $department = trim((string)($data['department'] ?? ''));
    $location = trim((string)($data['location'] ?? ''));
    $type = trim((string)($data['type'] ?? ''));
    $description = trim((string)($data['description'] ?? ''));
    $responsibilities = is_array($data['responsibilities'] ?? null) ? array_values(array_filter(array_map('strval', $data['responsibilities']), static fn($v) => trim($v) !== '')) : [];
    $requirements = is_array($data['requirements'] ?? null) ? array_values(array_filter(array_map('strval', $data['requirements']), static fn($v) => trim($v) !== '')) : [];

    if ($title === '' || $department === '' || $location === '' || $type === '' || $description === '' || !$responsibilities || !$requirements) {
        json_out(['success' => false, 'message' => 'Missing required job fields.'], 400);
    }

    $now = gmdate('c');
    $existsStmt = $pdo->prepare('SELECT COUNT(*) FROM jobs WHERE id = :id');
    $existsStmt->execute([':id' => $id]);
    $exists = (int)$existsStmt->fetchColumn() > 0;

    if ($exists) {
        $stmt = $pdo->prepare('UPDATE jobs SET title=:title, department=:department, location=:location, type=:type, description=:description, responsibilities_json=:responsibilities_json, requirements_json=:requirements_json, updated_at=:updated_at WHERE id=:id');
        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':department' => $department,
            ':location' => $location,
            ':type' => $type,
            ':description' => $description,
            ':responsibilities_json' => json_encode($responsibilities, JSON_UNESCAPED_UNICODE),
            ':requirements_json' => json_encode($requirements, JSON_UNESCAPED_UNICODE),
            ':updated_at' => $now,
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO jobs (id,title,department,location,type,description,responsibilities_json,requirements_json,created_at,updated_at) VALUES (:id,:title,:department,:location,:type,:description,:responsibilities_json,:requirements_json,:created_at,:updated_at)');
        $stmt->execute([
            ':id' => $id,
            ':title' => $title,
            ':department' => $department,
            ':location' => $location,
            ':type' => $type,
            ':description' => $description,
            ':responsibilities_json' => json_encode($responsibilities, JSON_UNESCAPED_UNICODE),
            ':requirements_json' => json_encode($requirements, JSON_UNESCAPED_UNICODE),
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }

    json_out(['success' => true, 'jobs' => fetch_jobs($pdo)]);
}

if ($method === 'DELETE') {
    $data = parse_json_input();
    $id = trim((string)($data['id'] ?? ''));
    if ($id === '') {
        json_out(['success' => false, 'message' => 'Missing id.'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM jobs WHERE id = :id');
    $stmt->execute([':id' => $id]);
    json_out(['success' => true, 'jobs' => fetch_jobs($pdo)]);
}

json_out(['success' => false, 'message' => 'Method not allowed'], 405);

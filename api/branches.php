<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method === 'GET') {
    json_out(['success' => true, 'branches' => fetch_branches($pdo)]);
}

if ($method === 'POST' || $method === 'PUT') {
    $data = parse_json_input();

    $id = trim((string)($data['id'] ?? ''));
    if ($id === '') {
        $id = 'branch_' . time() . '_' . substr(bin2hex(random_bytes(3)), 0, 6);
    }

    $name = trim((string)($data['name'] ?? ''));
    $city = trim((string)($data['city'] ?? ''));
    $province = trim((string)($data['province'] ?? ''));
    $country = trim((string)($data['country'] ?? 'Canada'));
    $address = trim((string)($data['address'] ?? ''));
    $email = trim((string)($data['email'] ?? ''));
    $hours = trim((string)($data['hours'] ?? ''));
    $primary = !empty($data['primary']) ? 1 : 0;

    if ($name === '' || $city === '' || $province === '' || $email === '') {
        json_out(['success' => false, 'message' => 'Missing required branch fields.'], 400);
    }

    $now = gmdate('c');

    if ($primary === 1) {
        $pdo->exec('UPDATE branches SET is_primary = 0');
    }

    $existsStmt = $pdo->prepare('SELECT COUNT(*) FROM branches WHERE id = :id');
    $existsStmt->execute([':id' => $id]);
    $exists = (int)$existsStmt->fetchColumn() > 0;

    if ($exists) {
        $stmt = $pdo->prepare('UPDATE branches SET name=:name, city=:city, province=:province, country=:country, address=:address, email=:email, hours=:hours, is_primary=:is_primary, updated_at=:updated_at WHERE id=:id');
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':city' => $city,
            ':province' => $province,
            ':country' => $country,
            ':address' => $address,
            ':email' => $email,
            ':hours' => $hours,
            ':is_primary' => $primary,
            ':updated_at' => $now,
        ]);
    } else {
        $stmt = $pdo->prepare('INSERT INTO branches (id,name,city,province,country,address,email,hours,is_primary,created_at,updated_at) VALUES (:id,:name,:city,:province,:country,:address,:email,:hours,:is_primary,:created_at,:updated_at)');
        $stmt->execute([
            ':id' => $id,
            ':name' => $name,
            ':city' => $city,
            ':province' => $province,
            ':country' => $country,
            ':address' => $address,
            ':email' => $email,
            ':hours' => $hours,
            ':is_primary' => $primary,
            ':created_at' => $now,
            ':updated_at' => $now,
        ]);
    }

    json_out(['success' => true, 'branches' => fetch_branches($pdo)]);
}

if ($method === 'DELETE') {
    $data = parse_json_input();
    $id = trim((string)($data['id'] ?? ''));
    if ($id === '') {
        json_out(['success' => false, 'message' => 'Missing id.'], 400);
    }

    $stmt = $pdo->prepare('DELETE FROM branches WHERE id = :id');
    $stmt->execute([':id' => $id]);
    json_out(['success' => true, 'branches' => fetch_branches($pdo)]);
}

json_out(['success' => false, 'message' => 'Method not allowed'], 405);

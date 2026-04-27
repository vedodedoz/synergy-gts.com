<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = db_connect();
$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

if ($method !== 'POST') {
    json_out(['success' => false, 'message' => 'Method not allowed'], 405);
}

$data = parse_json_input();
$action = (string)($data['action'] ?? '');

if ($action === 'verify_login') {
    $password = (string)($data['password'] ?? '');
    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = :key');
    $stmt->execute([':key' => 'admin_password_b64']);
    $stored = (string)($stmt->fetchColumn() ?: base64_encode('Admin@2026'));

    if (base64_encode($password) !== $stored) {
        json_out(['success' => false, 'message' => 'Incorrect password.'], 401);
    }

    json_out(['success' => true]);
}

if ($action === 'change_password') {
    $current = (string)($data['currentPassword'] ?? '');
    $new = (string)($data['newPassword'] ?? '');

    if (strlen($new) < 8) {
        json_out(['success' => false, 'message' => 'New password must be at least 8 characters.'], 400);
    }

    $stmt = $pdo->prepare('SELECT value FROM settings WHERE key = :key');
    $stmt->execute([':key' => 'admin_password_b64']);
    $stored = (string)($stmt->fetchColumn() ?: base64_encode('Admin@2026'));

    if (base64_encode($current) !== $stored) {
        json_out(['success' => false, 'message' => 'Current password is incorrect.'], 401);
    }

    $now = gmdate('c');
    $upsert = $pdo->prepare('INSERT INTO settings (key, value, updated_at) VALUES (:key,:value,:updated_at) ON CONFLICT(key) DO UPDATE SET value=excluded.value, updated_at=excluded.updated_at');
    $upsert->execute([
        ':key' => 'admin_password_b64',
        ':value' => base64_encode($new),
        ':updated_at' => $now,
    ]);

    json_out(['success' => true]);
}

json_out(['success' => false, 'message' => 'Unsupported action.'], 400);

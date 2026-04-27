<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'Method not allowed'], 405);
}

$pdo = db_connect();

$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
$id = isset($data['id']) ? trim((string)$data['id']) : '';
if ($id === '') {
    json_out(['success' => false, 'message' => 'Missing application id'], 400);
}

$stmt = $pdo->prepare('DELETE FROM applications WHERE id = :id');
$stmt->execute([':id' => $id]);
$deleted = $stmt->rowCount() > 0;

if (!$deleted) {
    json_out(['success' => false, 'message' => 'Application not found'], 404);
}

json_out(['success' => true, 'message' => 'Application deleted']);

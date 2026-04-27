<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    json_out(['success' => false, 'message' => 'Method not allowed'], 405);
}

$pdo = db_connect();

$pdo->beginTransaction();
try {
    $pdo->exec('DELETE FROM applications');
    $pdo->exec('DELETE FROM jobs');
    $pdo->exec('DELETE FROM branches');
    $pdo->exec('DELETE FROM content');
    $pdo->exec('DELETE FROM settings');
    seed_defaults($pdo);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    json_out(['success' => false, 'message' => 'Failed to reset data.'], 500);
}

json_out(['success' => true, 'message' => 'All data reset to defaults.']);

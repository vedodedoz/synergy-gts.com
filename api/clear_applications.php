<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'Method not allowed'], 405);
}

$pdo = db_connect();
$pdo->exec('DELETE FROM applications');

json_out(['success' => true, 'message' => 'All applications cleared']);

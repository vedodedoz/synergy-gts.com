<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = db_connect();

json_out([
    'success' => true,
    'jobs' => fetch_jobs($pdo),
    'branches' => fetch_branches($pdo),
    'content' => fetch_content($pdo),
]);

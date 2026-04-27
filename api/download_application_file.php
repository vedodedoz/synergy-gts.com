<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

$pdo = db_connect();
$id = trim((string)($_GET['id'] ?? ''));
$type = trim((string)($_GET['type'] ?? 'cv'));

if ($id === '' || !in_array($type, ['cv', 'details'], true)) {
    http_response_code(400);
    echo 'Invalid request';
    exit;
}

$stmt = $pdo->prepare('SELECT cv_file_name, cv_mime, cv_blob, details_pdf_name, details_pdf_blob FROM applications WHERE id = :id');
$stmt->execute([':id' => $id]);
$row = $stmt->fetch();

if (!$row) {
    http_response_code(404);
    echo 'File not found';
    exit;
}

if ($type === 'cv') {
    header('Content-Type: ' . ($row['cv_mime'] ?: 'application/octet-stream'));
    header('Content-Disposition: attachment; filename="' . ($row['cv_file_name'] ?: 'cv_file') . '"');
    echo $row['cv_blob'];
    exit;
}

header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . ($row['details_pdf_name'] ?: 'application_details.pdf') . '"');
echo $row['details_pdf_blob'];
exit;

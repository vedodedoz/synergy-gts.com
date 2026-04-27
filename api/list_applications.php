<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_out(['success' => false, 'message' => 'Method not allowed'], 405);
}

$pdo = db_connect();
$rows = $pdo->query('SELECT id, first_name, last_name, email, phone, linkedin, job_title, experience, cover, submitted_at, cv_file_name, details_pdf_name FROM applications ORDER BY submitted_at DESC')->fetchAll();

$applications = [];
foreach ($rows ?: [] as $row) {
    $id = (string)$row['id'];
    $applications[] = [
        'id' => $id,
        'firstName' => $row['first_name'],
        'lastName' => $row['last_name'],
        'email' => $row['email'],
        'phone' => $row['phone'] ?? '',
        'linkedin' => $row['linkedin'] ?? '',
        'jobTitle' => $row['job_title'],
        'experience' => $row['experience'],
        'cover' => $row['cover'],
        'submittedAt' => $row['submitted_at'],
        'cvFileName' => $row['cv_file_name'],
        'cvUrl' => 'api/download_application_file.php?id=' . rawurlencode($id) . '&type=cv',
        'detailsPdfName' => $row['details_pdf_name'],
        'detailsPdfUrl' => 'api/download_application_file.php?id=' . rawurlencode($id) . '&type=details',
    ];
}

json_out([
    'success' => true,
    'applications' => $applications,
]);

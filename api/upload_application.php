<?php

declare(strict_types=1);

require __DIR__ . '/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['success' => false, 'message' => 'Method not allowed'], 405);
}

$pdo = db_connect();

$requiredFields = ['firstName', 'lastName', 'email', 'jobTitle', 'experience', 'cover'];
foreach ($requiredFields as $field) {
    if (!isset($_POST[$field]) || trim((string)$_POST[$field]) === '') {
        json_out(['success' => false, 'message' => 'Missing field: ' . $field], 400);
    }
}

if (!isset($_FILES['cv']) || !is_array($_FILES['cv'])) {
    json_out(['success' => false, 'message' => 'CV file is required.'], 400);
}

$file = $_FILES['cv'];
if (($file['error'] ?? UPLOAD_ERR_OK) !== UPLOAD_ERR_OK) {
    json_out(['success' => false, 'message' => 'Upload failed.'], 400);
}

$allowedExtensions = ['pdf', 'doc', 'docx'];
$originalName = (string)($file['name'] ?? 'cv');
$extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
if (!in_array($extension, $allowedExtensions, true)) {
    json_out(['success' => false, 'message' => 'Only PDF, DOC, DOCX are allowed.'], 400);
}

$size = (int)($file['size'] ?? 0);
if ($size <= 0 || $size > 5 * 1024 * 1024) {
    json_out(['success' => false, 'message' => 'File size must be between 1 byte and 5 MB.'], 400);
}

$appId = 'app_' . date('Ymd_His') . '_' . substr(bin2hex(random_bytes(4)), 0, 8);

$cvBlob = file_get_contents((string)$file['tmp_name']);
if ($cvBlob === false) {
    json_out(['success' => false, 'message' => 'Could not read uploaded CV.'], 500);
}

$jobTitle = trim((string)$_POST['jobTitle']);
$email = trim((string)$_POST['email']);
$phone = trim((string)($_POST['phone'] ?? ''));
$linkedin = trim((string)($_POST['linkedin'] ?? ''));
$experience = trim((string)$_POST['experience']);
$cover = trim((string)$_POST['cover']);
$submittedAt = gmdate('c');

$fullName = trim((string)$_POST['firstName'] . ' ' . (string)$_POST['lastName']);

$detailsLines = [
    'Synergy-GTS Job Application Details',
    'Generated: ' . gmdate('Y-m-d H:i:s') . ' UTC',
    '',
    'Application ID: ' . $appId,
    'Full Name: ' . $fullName,
    'Email: ' . $email,
    'Phone: ' . ($phone !== '' ? $phone : 'N/A'),
    'LinkedIn: ' . ($linkedin !== '' ? $linkedin : 'N/A'),
    'Job Title: ' . $jobTitle,
    'Experience: ' . $experience,
    '',
    'Cover Letter:',
];

$coverLines = preg_split('/\r\n|\r|\n/', $cover) ?: [];
foreach ($coverLines as $line) {
    $detailsLines[] = $line;
}

$detailsPdfName = 'Application_Details_' . $appId . '.pdf';
$detailsPdfBlob = generate_simple_pdf($detailsLines);

$stmt = $pdo->prepare('INSERT INTO applications (id,first_name,last_name,email,phone,linkedin,job_title,experience,cover,submitted_at,cv_file_name,cv_mime,cv_blob,details_pdf_name,details_pdf_blob) VALUES (:id,:first_name,:last_name,:email,:phone,:linkedin,:job_title,:experience,:cover,:submitted_at,:cv_file_name,:cv_mime,:cv_blob,:details_pdf_name,:details_pdf_blob)');
$stmt->bindValue(':id', $appId);
$stmt->bindValue(':first_name', (string)$_POST['firstName']);
$stmt->bindValue(':last_name', (string)$_POST['lastName']);
$stmt->bindValue(':email', $email);
$stmt->bindValue(':phone', $phone);
$stmt->bindValue(':linkedin', $linkedin);
$stmt->bindValue(':job_title', $jobTitle);
$stmt->bindValue(':experience', $experience);
$stmt->bindValue(':cover', $cover);
$stmt->bindValue(':submitted_at', $submittedAt);
$stmt->bindValue(':cv_file_name', $originalName);
$stmt->bindValue(':cv_mime', (string)($file['type'] ?? 'application/octet-stream'));
$stmt->bindValue(':cv_blob', $cvBlob, PDO::PARAM_LOB);
$stmt->bindValue(':details_pdf_name', $detailsPdfName);
$stmt->bindValue(':details_pdf_blob', $detailsPdfBlob, PDO::PARAM_LOB);
$stmt->execute();

json_out([
    'success' => true,
    'message' => 'Application saved.',
    'id' => $appId,
    'cvUrl' => 'api/download_application_file.php?id=' . rawurlencode($appId) . '&type=cv',
    'detailsPdfUrl' => 'api/download_application_file.php?id=' . rawurlencode($appId) . '&type=details',
]);

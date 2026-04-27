<?php

declare(strict_types=1);

function app_root_path(string $relative = ''): string
{
    $base = dirname(__DIR__);
    return $relative === '' ? $base : $base . DIRECTORY_SEPARATOR . $relative;
}

function cvs_root_path(string $relative = ''): string
{
    $base = app_root_path('CVs');
    return $relative === '' ? $base : $base . DIRECTORY_SEPARATOR . $relative;
}

function ensure_dir(string $dir): void
{
    if (!is_dir($dir)) {
        mkdir($dir, 0775, true);
    }
}

function sanitize_name(string $value): string
{
    $value = trim($value);
    $value = preg_replace('/[^a-zA-Z0-9\-_ ]+/', '', $value) ?? '';
    $value = preg_replace('/\s+/', '_', $value) ?? '';
    return trim($value, '_- ');
}

function sanitize_filename(string $filename): string
{
    $filename = basename($filename);
    $filename = preg_replace('/[^a-zA-Z0-9\.\-_]+/', '_', $filename) ?? 'file';
    return trim($filename, '._') ?: 'file';
}

function json_file_path(): string
{
    return cvs_root_path('applications.json');
}

function read_applications(): array
{
    $file = json_file_path();
    if (!is_file($file)) {
        return [];
    }

    $raw = file_get_contents($file);
    if ($raw === false || trim($raw) === '') {
        return [];
    }

    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function write_applications(array $applications): void
{
    ensure_dir(cvs_root_path());
    file_put_contents(json_file_path(), json_encode($applications, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function rel_path_from_root(string $absolutePath): string
{
    $root = app_root_path();
    $relative = str_replace($root, '', $absolutePath);
    return str_replace('\\', '/', ltrim($relative, '\\/'));
}

function output_json(array $payload, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_SLASHES);
    exit;
}

function generate_simple_pdf(array $lines): string
{
    $stream = "BT\n/F1 11 Tf\n50 790 Td\n14 TL\n";
    foreach ($lines as $line) {
        $line = str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], (string)$line);
        $stream .= '(' . $line . ") Tj\nT*\n";
    }
    $stream .= "ET";

    $len = strlen($stream);

    $pdf = "%PDF-1.4\n";
    $offsets = [];

    $offsets[1] = strlen($pdf);
    $pdf .= "1 0 obj\n<< /Type /Catalog /Pages 2 0 R >>\nendobj\n";

    $offsets[2] = strlen($pdf);
    $pdf .= "2 0 obj\n<< /Type /Pages /Kids [3 0 R] /Count 1 >>\nendobj\n";

    $offsets[3] = strlen($pdf);
    $pdf .= "3 0 obj\n<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 4 0 R >> >> /Contents 5 0 R >>\nendobj\n";

    $offsets[4] = strlen($pdf);
    $pdf .= "4 0 obj\n<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>\nendobj\n";

    $offsets[5] = strlen($pdf);
    $pdf .= "5 0 obj\n<< /Length {$len} >>\nstream\n{$stream}\nendstream\nendobj\n";

    $xrefOffset = strlen($pdf);
    $pdf .= "xref\n0 6\n";
    $pdf .= "0000000000 65535 f \n";
    for ($i = 1; $i <= 5; $i++) {
        $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
    }

    $pdf .= "trailer\n<< /Size 6 /Root 1 0 R >>\nstartxref\n{$xrefOffset}\n%%EOF";

    return $pdf;
}

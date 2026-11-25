<?php
session_start();
if (!isset($_SESSION['user_login'])) {
    header('Location: ../login.php');
    exit();
}

$file = $_GET['file'] ?? '';
if (empty($file)) {
    http_response_code(400);
    echo "No file specified.";
    exit;
}

$file_path = realpath(__DIR__ . '/birth_certificates/' . $file);

if (!file_exists($file_path)) {
    $base_name = basename($file, '.*');
    $files = glob(__DIR__ . '/birth_certificates/' . $base_name . '_*.*');
    $file_path = !empty($files) ? realpath($files[0]) : $file_path;
}

if (file_exists($file_path) && pathinfo($file_path, PATHINFO_EXTENSION) === 'pdf') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    readfile($file_path);
    exit;
} else {
    http_response_code(404);
    echo "File not found. Path checked: " . htmlspecialchars($file_path) . "<br>Requested file: " . htmlspecialchars($file);
    exit;
}
?>
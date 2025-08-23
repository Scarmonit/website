<?php
// Handle authentication and includes
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Check if a file was specified
if (!isset($_GET['file']) || empty($_GET['file'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'No file specified']);
    exit;
}

// Sanitize the filename
$zipName = sanitizeFilename($_GET['file']);
$zipPath = '../temp/' . $zipName;

// Security check: ensure file exists and is within the temp directory
if (!file_exists($zipPath) || strpos(realpath($zipPath), realpath('../temp/')) !== 0) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Invalid file']);
    exit;
}

// Set headers for file download
header('Content-Type: application/zip');
header('Content-Disposition: attachment; filename="' . $zipName . '"');
header('Content-Length: ' . filesize($zipPath));
header('Pragma: no-cache');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Expires: 0');

// Clear any output buffers
if (ob_get_level()) {
    ob_end_clean();
}
flush();

// Output the file
readfile($zipPath);

// Delete the file after download
unlink($zipPath);
exit;

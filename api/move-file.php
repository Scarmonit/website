<?php
// Include authentication and helpers
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['success' => false, 'message' => 'Only POST requests are allowed']);
    exit;
}

// Get the request body as JSON
$requestBody = file_get_contents('php://input');
$data = json_decode($requestBody, true);

// Check if all required parameters are provided
if (!isset($data['fileName']) || empty($data['fileName']) || 
    !isset($data['destinationFolder']) || $data['destinationFolder'] === null) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$fileName = sanitizeFilename($data['fileName']);
$destinationFolder = sanitizeFilename($data['destinationFolder']);
$currentFolder = isset($data['currentFolder']) ? sanitizeFilename($data['currentFolder']) : '';

// Build the source and destination paths
$sourcePath = "../" . ($currentFolder ? $currentFolder . '/' : '') . $fileName;
$destPath = "../" . ($currentFolder ? $currentFolder . '/' : '') . $destinationFolder . '/' . $fileName;

// Check if source file exists
if (!file_exists($sourcePath)) {
    echo json_encode(['success' => false, 'message' => 'Source file does not exist']);
    exit;
}

// Check if destination folder exists
$destFolderPath = "../" . ($currentFolder ? $currentFolder . '/' : '') . $destinationFolder;
if (!is_dir($destFolderPath)) {
    echo json_encode(['success' => false, 'message' => 'Destination folder does not exist']);
    exit;
}

// Check if the file already exists in the destination
if (file_exists($destPath)) {
    echo json_encode(['success' => false, 'message' => 'A file with the same name already exists in the destination folder']);
    exit;
}

// Move the file
if (rename($sourcePath, $destPath)) {
    echo json_encode([
        'success' => true,
        'message' => 'File moved successfully',
        'fileName' => $fileName,
        'destinationFolder' => $destinationFolder
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to move file. Please check permissions.']);
}

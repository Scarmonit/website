<?php
// Handle authentication and includes
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set the content type to JSON for error responses
header('Content-Type: application/json');

// Check if this is a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON data from the request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Validate input
if (!isset($data['files']) || !is_array($data['files']) || empty($data['files'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No files specified']);
    exit;
}

// Set filename for the archive
$zipName = 'parker_files_' . date('Y-m-d_H-i-s') . '.zip';
$zipPath = '../temp/' . $zipName;

// Create temp directory if it doesn't exist
if (!is_dir('../temp')) {
    mkdir('../temp', 0777, true);
}

// Initialize ZIP archive
$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE) !== TRUE) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to create ZIP archive']);
    exit;
}

// Add files to the archive
$baseDir = '../';
$fileCount = 0;
$errors = [];

foreach ($data['files'] as $file) {
    // Security check: prevent directory traversal
    $file = sanitizeFilename($file);
    $filePath = $baseDir . $file;
    
    if (strpos(realpath($filePath), realpath($baseDir)) !== 0) {
        $errors[] = "Security error: Invalid file path for $file";
        continue;
    }
    
    if (!file_exists($filePath)) {
        $errors[] = "File not found: $file";
        continue;
    }
    
    if (is_file($filePath)) {
        // Add file to ZIP with local path only
        if ($zip->addFile($filePath, basename($file))) {
            $fileCount++;
        } else {
            $errors[] = "Failed to add $file to archive";
        }
    }
}

// Close the ZIP file
$zip->close();

// If no files were added, return an error
if ($fileCount === 0) {
    unlink($zipPath);
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'No valid files to compress', 
        'errors' => $errors
    ]);
    exit;
}

// Success response with download URL
echo json_encode([
    'success' => true,
    'message' => "$fileCount files compressed successfully",
    'downloadUrl' => 'api/download-zip.php?file=' . urlencode($zipName),
    'errors' => $errors
]);

<?php
// Handle authentication and includes
require_once '../includes/auth.php';
require_once '../includes/config.php';
require_once '../includes/functions.php';

// Set content type to JSON
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
if (!isset($data['source']) || !isset($data['destination'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing source or destination']);
    exit;
}

$source = $data['source'];
$destination = $data['destination'];
$type = isset($data['type']) ? $data['type'] : 'file';

// Base directory
$baseDir = '../';

// Source and destination paths
$sourcePath = $baseDir . $source;
$destPath = $baseDir . $destination;

// Security check: prevent directory traversal
if (strpos(realpath($sourcePath), realpath($baseDir)) !== 0 || 
    strpos(realpath(dirname($destPath)), realpath($baseDir)) !== 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid path. Security violation detected.']);
    exit;
}

// Check if source exists
if (!file_exists($sourcePath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Source file not found']);
    exit;
}

// Check if destination already exists
if (file_exists($destPath)) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Destination already exists']);
    exit;
}

// Check if trying to move a file/folder into itself
if ($type === 'folder' && strpos($destination, $source . '/') === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot move a folder into itself']);
    exit;
}

// Check for invalid filename characters
$destFilename = basename($destPath);
if (preg_match('/[\\/:*?"<>|]/', $destFilename)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Destination filename contains invalid characters']);
    exit;
}

// Create destination directory if it doesn't exist
$destDir = dirname($destPath);
if (!is_dir($destDir)) {
    if (!mkdir($destDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create destination directory']);
        exit;
    }
}

// Perform the move
if (rename($sourcePath, $destPath)) {
    echo json_encode([
        'success' => true,
        'message' => ($type === 'folder' ? 'Folder' : 'File') . ' moved successfully',
        'source' => $source,
        'destination' => $destination
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to move ' . ($type === 'folder' ? 'folder' : 'file')]);
}

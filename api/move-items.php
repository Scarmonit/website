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
if (!isset($data['items']) || !is_array($data['items']) || empty($data['items']) || !isset($data['targetFolder'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request data']);
    exit;
}

// Base directory
$baseDir = '../';

// Current folder
$currentFolder = isset($data['currentFolder']) ? $data['currentFolder'] : '';
$currentFolderPath = $baseDir . ($currentFolder ? rtrim($currentFolder, '/') . '/' : '');

// Target folder
$targetFolder = $data['targetFolder'];
$targetPath = $baseDir . $targetFolder;

// Check if target folder exists
if (!is_dir($targetPath)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Target folder does not exist']);
    exit;
}

// Security check: prevent directory traversal
if (strpos(realpath($targetPath), realpath($baseDir)) !== 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid target folder path']);
    exit;
}

// Create target folder if it doesn't exist
if (!is_dir($targetPath)) {
    if (!mkdir($targetPath, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create target folder']);
        exit;
    }
}

// Track success and failures
$moved = [];
$failed = [];

// Process each item
foreach ($data['items'] as $item) {
    $itemName = $item['name'];
    $itemType = $item['type'];
    
    // Skip protected files
    $protectedFiles = ['login.php', '.htaccess', 'logout.php', 'index.php', 'file-viewer.php', 'me.php'];
    if ($itemType === 'file' && in_array($itemName, $protectedFiles)) {
        $failed[] = ['name' => $itemName, 'reason' => 'Protected file cannot be moved'];
        continue;
    }
    
    // Source and destination paths
    $sourcePath = $currentFolderPath . $itemName;
    $destPath = $targetPath . '/' . $itemName;
    
    // Security check: prevent directory traversal
    if (strpos(realpath($sourcePath), realpath($baseDir)) !== 0) {
        $failed[] = ['name' => $itemName, 'reason' => 'Invalid source path'];
        continue;
    }
    
    // Check if source exists
    if (!file_exists($sourcePath)) {
        $failed[] = ['name' => $itemName, 'reason' => 'Source not found'];
        continue;
    }
    
    // Check if destination already exists
    if (file_exists($destPath)) {
        $failed[] = ['name' => $itemName, 'reason' => 'Destination already exists'];
        continue;
    }
    
    // Perform the move
    if (rename($sourcePath, $destPath)) {
        $moved[] = $itemName;
    } else {
        $failed[] = ['name' => $itemName, 'reason' => 'Failed to move'];
    }
}

// Prepare response
$success = count($moved) > 0;
$message = '';

if (count($moved) > 0) {
    $message = count($moved) . ' ' . (count($moved) === 1 ? 'item' : 'items') . ' moved successfully';
}

if (count($failed) > 0) {
    $message .= (count($moved) > 0 ? '. ' : '') . count($failed) . ' ' . (count($failed) === 1 ? 'item' : 'items') . ' failed to move';
}

echo json_encode([
    'success' => $success,
    'message' => $message,
    'moved' => $moved,
    'failed' => $failed
]);

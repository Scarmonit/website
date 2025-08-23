<?php
// Include authentication and config
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Set content type to JSON
header('Content-Type: application/json');

// Check request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get JSON request data
$requestData = json_decode(file_get_contents('php://input'), true);

// Validate path
if (!isset($requestData['path']) || empty($requestData['path'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Path is required']);
    exit;
}

$path = $requestData['path'];

// Sanitize path for security
$path = str_replace(['../', '..\\'], '', $path);
$fullPath = './' . $path;

// Check if path exists
if (!file_exists($fullPath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File or folder not found']);
    exit;
}

// Check if item is file or directory
$isDirectory = is_dir($fullPath);

// Delete the item
$deleted = false;
if ($isDirectory) {
    // Recursively delete directory
    $deleted = deleteDirectory($fullPath);
} else {
    // Delete file
    $deleted = unlink($fullPath);
}

if ($deleted) {
    echo json_encode([
        'success' => true,
        'message' => ($isDirectory ? 'Folder' : 'File') . ' deleted successfully',
        'path' => $path,
        'type' => $isDirectory ? 'folder' : 'file'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete ' . ($isDirectory ? 'folder' : 'file')
    ]);
}

/**
 * Recursively delete a directory
 *
 * @param string $dir Directory path
 * @return bool True on success, false on failure
 */
function deleteDirectory($dir) {
    if (!file_exists($dir)) {
        return true;
    }
    
    if (!is_dir($dir)) {
        return unlink($dir);
    }
    
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') {
            continue;
        }
        
        if (!deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
            return false;
        }
    }
    
    return rmdir($dir);
}

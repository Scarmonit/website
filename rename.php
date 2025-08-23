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

// Validate input
if (!isset($requestData['path']) || empty($requestData['path'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Path is required']);
    exit;
}

if (!isset($requestData['newName']) || empty(trim($requestData['newName']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New name is required']);
    exit;
}

$path = $requestData['path'];
$newName = trim($requestData['newName']);

// Check for invalid characters in new name
if (preg_match('/[\/:*?"<>|]/', $newName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'New name contains invalid characters']);
    exit;
}

// Sanitize path for security
$path = str_replace(['../', '..\\'], '', $path);
$fullPath = './' . $path;

// Check if path exists
if (!file_exists($fullPath)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'File or folder not found']);
    exit;
}

// Get directory and file parts
$pathInfo = pathinfo($fullPath);
$directory = $pathInfo['dirname'];
$newPath = $directory . '/' . $newName;

// Check if the new name already exists
if (file_exists($newPath)) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'A file or folder with this name already exists']);
    exit;
}

// Perform the rename
if (rename($fullPath, $newPath)) {
    $isDirectory = is_dir($newPath);
    
    // Get item info for response
    $itemInfo = [
        'name' => $newName,
        'path' => str_replace('./', '', $newPath),
        'type' => $isDirectory ? 'folder' : 'file',
        'modified' => filemtime($newPath)
    ];
    
    if (!$isDirectory) {
        $itemInfo['size'] = filesize($newPath);
    }
    
    echo json_encode([
        'success' => true,
        'message' => ($isDirectory ? 'Folder' : 'File') . ' renamed successfully',
        'item' => $itemInfo
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to rename item. Please try again.'
    ]);
}

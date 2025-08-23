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

// Validate folder name
if (!isset($requestData['folderName']) || empty(trim($requestData['folderName']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Folder name is required']);
    exit;
}

$folderName = trim($requestData['folderName']);

// Check for invalid characters in folder name
if (preg_match('/[\/:*?"<>|]/', $folderName)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Folder name contains invalid characters']);
    exit;
}

// Get current folder from query string
$currentFolder = isset($_GET['folder']) ? $_GET['folder'] : '';

// Validate folder path for security
if (!empty($currentFolder) && !isValidPath($currentFolder)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid folder path']);
    exit;
}

// Define target directory - use uploads folder and add subfolder if specified
$targetDir = './uploads/';
if (!empty($currentFolder)) {
    $targetDir .= $currentFolder . '/';
}

// Create parent directory if it doesn't exist
if (!file_exists($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create parent directory']);
        exit;
    }
}

// Check if folder already exists
$newFolderPath = $targetDir . $folderName;
if (file_exists($newFolderPath)) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'A folder with this name already exists']);
    exit;
}

// Create the new folder
if (mkdir($newFolderPath, 0755)) {
    // Get folder info for response
    $folderInfo = [
        'name' => $folderName,
        'path' => str_replace('./', '', $newFolderPath),
        'type' => 'folder',
        'modified' => time()
    ];
    
    echo json_encode([
        'success' => true, 
        'message' => 'Folder created successfully',
        'folder' => $folderInfo
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to create folder. Please try again.'
    ]);
}

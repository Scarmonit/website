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

// Check if folder name is provided
if (!isset($data['folderName']) || empty($data['folderName'])) {
    echo json_encode(['success' => false, 'message' => 'Folder name is required']);
    exit;
}

$folderName = trim($data['folderName']);

// Validate folder name
if (preg_match('/[\\\\\/\:*?"<>|]/', $folderName)) {
    echo json_encode(['success' => false, 'message' => 'Folder name contains invalid characters']);
    exit;
}

// Determine the folder path
$currentFolder = isset($_GET['folder']) ? sanitizeFilename($_GET['folder']) . '/' : '';
$folderPath = realpath('../') . '/' . $currentFolder . $folderName;

// Check if folder already exists
if (file_exists($folderPath)) {
    echo json_encode(['success' => false, 'message' => 'A folder with this name already exists']);
    exit;
}

// Create the folder
if (mkdir($folderPath, 0755)) {
    echo json_encode([
        'success' => true,
        'message' => 'Folder created successfully',
        'folderName' => $folderName,
        'path' => $currentFolder . $folderName
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to create folder. Please check permissions.']);
}

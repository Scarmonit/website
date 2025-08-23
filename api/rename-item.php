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

// Check if required data is provided
if (!isset($data['oldName']) || empty($data['oldName']) || 
    !isset($data['newName']) || empty($data['newName']) ||
    !isset($data['type']) || empty($data['type'])) {
    echo json_encode(['success' => false, 'message' => 'Missing required parameters']);
    exit;
}

$oldName = trim($data['oldName']);
$newName = trim($data['newName']);
$type = trim($data['type']); // 'file' or 'folder'
$currentFolder = isset($data['currentFolder']) ? trim($data['currentFolder']) : '';

// Validate the new name
if (preg_match('/[\\\\\/\:*?"<>|]/', $newName)) {
    echo json_encode(['success' => false, 'message' => 'Name contains invalid characters']);
    exit;
}

// Check if old name exists
$oldPath = realpath('../') . '/' . $currentFolder . $oldName;
if (!file_exists($oldPath)) {
    echo json_encode(['success' => false, 'message' => 'The item to rename does not exist']);
    exit;
}

// Check if new name already exists
$newPath = realpath('../') . '/' . $currentFolder . $newName;
if (file_exists($newPath)) {
    echo json_encode(['success' => false, 'message' => 'An item with this name already exists']);
    exit;
}

// Check if it's a file or folder
$isFolder = is_dir($oldPath);
if (($type === 'file' && $isFolder) || ($type === 'folder' && !$isFolder)) {
    echo json_encode(['success' => false, 'message' => 'Type mismatch: the specified item is not a ' . $type]);
    exit;
}

// Perform the rename operation
if (rename($oldPath, $newPath)) {
    echo json_encode([
        'success' => true, 
        'message' => ($type === 'folder' ? 'Folder' : 'File') . ' renamed successfully',
        'oldName' => $oldName,
        'newName' => $newName,
        'type' => $type
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Failed to rename ' . $type . '. Please check permissions.']);
}

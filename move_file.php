<?php
// Check for authentication cookie
if (!isset($_COOKIE['parker_authenticated']) || $_COOKIE['parker_authenticated'] !== 'true') {
    header('HTTP/1.1 401 Unauthorized');
    echo json_encode(['success' => false, 'message' => 'Authentication required']);
    exit;
}

// Set content type to JSON
header('Content-Type: application/json');

// Only allow POST requests
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

// Basic security check: prevent directory traversal
if (strpos($source, '../') !== false || strpos($destination, '../') !== false) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid path']);
    exit;
}

// Check if source exists
if (!file_exists($source)) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Source file not found']);
    exit;
}

// Check if destination already exists
if (file_exists($destination)) {
    http_response_code(409);
    echo json_encode(['success' => false, 'message' => 'Destination already exists']);
    exit;
}

// Check if trying to move a file into itself or subdirectory
if (is_dir($source) && strpos($destination, $source . '/') === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cannot move a folder into itself']);
    exit;
}

// Perform the move operation
if (rename($source, $destination)) {
    echo json_encode(['success' => true, 'message' => 'File moved successfully']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to move file']);
}
?>

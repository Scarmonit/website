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

// Create directory if it doesn't exist
if (!file_exists($targetDir)) {
    if (!mkdir($targetDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to create upload directory']);
        exit;
    }
}

// Check if file was uploaded
if (!isset($_FILES['file'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No file uploaded']);
    exit;
}

$uploadedFile = $_FILES['file'];
$fileName = basename(sanitizeFilename($uploadedFile['name']));
$targetFile = $targetDir . $fileName;

// Validate file
$validationResult = validateUploadedFile($uploadedFile, $targetFile);
if (!$validationResult['valid']) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $validationResult['message']]);
    exit;
}

// Attempt to upload the file
if (move_uploaded_file($uploadedFile['tmp_name'], $targetFile)) {
    // Get file info for response
    $fileInfo = [
        'name' => $fileName,
        'path' => str_replace('./', '', $targetFile),
        'size' => filesize($targetFile),
        'type' => mime_content_type($targetFile),
        'modified' => filemtime($targetFile)
    ];
    
    echo json_encode([
        'success' => true, 
        'message' => 'File uploaded successfully',
        'file' => $fileInfo
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Failed to upload file. Please try again.'
    ]);
}

/**
 * Validate uploaded file
 * 
 * @param array $file The uploaded file data
 * @param string $targetPath The target file path
 * @return array Validation result with 'valid' and 'message' keys
 */
function validateUploadedFile($file, $targetPath) {
    // Check upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds the upload_max_filesize directive',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds the MAX_FILE_SIZE directive',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload'
        ];
        
        $message = isset($errorMessages[$file['error']]) 
            ? $errorMessages[$file['error']] 
            : 'Unknown upload error';
            
        return ['valid' => false, 'message' => $message];
    }
    
    // Check if file already exists
    if (file_exists($targetPath)) {
        return ['valid' => false, 'message' => 'File already exists'];
    }
    
    // Check file size (limit to 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        return ['valid' => false, 'message' => 'File is too large (max 10MB)'];
    }
    
    return ['valid' => true, 'message' => ''];
}

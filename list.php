<?php
// Include authentication and config
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Define isValidPath function if it doesn't exist
if (!function_exists('isValidPath')) {
    /**
     * Validate a folder path to prevent directory traversal and ensure it's within allowed boundaries
     * 
     * @param string $path The path to validate
     * @return bool True if the path is valid, false otherwise
     */
    function isValidPath($path) {
        // Check for directory traversal attempts
        if (strpos($path, '../') !== false || strpos($path, '..\\') !== false) {
            return false;
        }
        
        // Remove any leading or trailing slashes
        $path = trim($path, '/\\');
        
        // Ensure path only contains allowed characters
        if (!preg_match('/^[a-zA-Z0-9_\-\/\\\\. ]+$/', $path)) {
            return false;
        }
        
        return true;
    }
}

// Determine response format (HTML or JSON)
$responseFormat = isset($_GET['format']) && $_GET['format'] === 'json' ? 'json' : 'html';

// Get current folder from query string
$currentFolder = isset($_GET['folder']) ? $_GET['folder'] : '';

// Validate folder path for security
if (!empty($currentFolder) && !isValidPath($currentFolder)) {
    if ($responseFormat === 'json') {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid folder path']);
    } else {
        echo '<div class="error-message">Invalid folder path</div>';
    }
    exit;
}

// Get directory path
$dirPath = './';
if (!empty($currentFolder)) {
    $dirPath .= 'uploads/' . $currentFolder . '/';
} else {
    $dirPath .= 'uploads/';
}

// Create directory if it doesn't exist
if (!file_exists($dirPath)) {
    if (!mkdir($dirPath, 0755, true)) {
        if ($responseFormat === 'json') {
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Failed to create directory']);
        } else {
            echo '<div class="error-message">Failed to create directory</div>';
        }
        exit;
    }
}

// Get directory contents
$contents = getDirectoryContents($dirPath); // Fixed: Removed second parameter ($currentFolder)

// Return appropriate response format
if ($responseFormat === 'json') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'contents' => $contents
    ]);
} else {
    // HTML output for folders
    if (!empty($contents['folders'])) {
        echo '<div class="folder-section">';
        echo '<h2><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z"/>
              </svg> Folders</h2>';
        echo '<ul class="file-grid folder-grid" role="list" id="folderList">';
        
        foreach ($contents['folders'] as $folder) {
            $folderPath = !empty($currentFolder) ? $currentFolder . '/' . $folder['name'] : $folder['name'];
            
            echo '<li class="file-item folder-item" data-type="folder" data-name="' . htmlspecialchars($folder['name']) . '" data-path="' . htmlspecialchars($folderPath) . '">';
            echo '<div class="file-card">';
            echo '<div class="card-selection"><input type="checkbox" class="item-checkbox" aria-label="Select ' . htmlspecialchars($folder['name']) . '"></div>';
            echo '<a href="index.php?folder=' . urlencode($folderPath) . '" class="file-link folder-link">';
            echo '<div class="file-icon-wrapper folder-icon-wrapper"><span class="file-icon" aria-hidden="true">📁</span></div>';
            echo '<div class="file-info">';
            echo '<div class="file-name">' . htmlspecialchars($folder['name']) . '</div>';
            echo '<div class="file-meta">';
            echo '<span class="file-type">Folder</span>';
            echo '<span class="file-date">' . date("M d, Y", $folder['modified']) . '</span>';
            echo '</div></div></a>';
            echo '<div class="file-actions">';
            echo '<button type="button" class="action-btn rename-btn" data-name="' . htmlspecialchars($folder['name']) . '" title="Rename"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg></button>';
            echo '<button type="button" class="action-btn delete-btn" data-name="' . htmlspecialchars($folder['name']) . '" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg></button>';
            echo '</div></div></li>';
        }
        
        echo '</ul></div>';
    }
    
    // HTML output for files
    if (!empty($contents['files'])) {
        echo '<div class="files-section">';
        echo '<h2><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                  <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                  <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
              </svg> Files</h2>';
        echo '<ul class="file-grid" role="list" id="fileList">';
        
        foreach ($contents['files'] as $file) {
            $filePath = !empty($currentFolder) ? 'uploads/' . $currentFolder . '/' . $file['name'] : 'uploads/' . $file['name'];
            $fileExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            
            // Get file icon based on type
            $fileIcon = getFileIcon($fileExt);
            
            echo '<li class="file-item" data-type="file" data-name="' . htmlspecialchars($file['name']) . 
                 '" data-path="' . htmlspecialchars($filePath) . '" data-extension="' . htmlspecialchars($fileExt) . '">';
            echo '<div class="file-card">';
            echo '<div class="card-selection"><input type="checkbox" class="item-checkbox" aria-label="Select ' . htmlspecialchars($file['name']) . '"></div>';
            echo '<a href="file-viewer.php?file=' . urlencode($filePath) . '" class="file-link">';
            echo '<div class="file-icon-wrapper"><span class="file-icon" aria-hidden="true">' . $fileIcon . '</span></div>';
            echo '<div class="file-info">';
            echo '<div class="file-name">' . htmlspecialchars($file['name']) . '</div>';
            echo '<div class="file-meta">';
            echo '<span class="file-type">' . strtoupper($fileExt) . '</span>';
            echo '<span class="file-size">' . formatFileSize($file['size']) . '</span>';
            echo '<span class="file-date">' . date("M d, Y", $file['modified']) . '</span>';
            echo '</div></div></a>';
            echo '<div class="file-actions">';
            echo '<button type="button" class="action-btn view-btn" data-path="' . htmlspecialchars($filePath) . '" title="View"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M10.5 8a2.5 2.5 0 1 1-5 0 2.5 2.5 0 0 1 5 0z"/><path d="M0 8s3-5.5 8-5.5S16 8 16 8s-3 5.5-8 5.5S0 8 0 8zm8 3.5a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7z"/></svg></button>';
            echo '<button type="button" class="action-btn download-btn" data-path="' . htmlspecialchars($filePath) . '" title="Download"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/><path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/></svg></button>';
            echo '<button type="button" class="action-btn rename-btn" data-name="' . htmlspecialchars($file['name']) . '" title="Rename"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/></svg></button>';
            echo '<button type="button" class="action-btn delete-btn" data-name="' . htmlspecialchars($file['name']) . '" title="Delete"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6z"/><path fill-rule="evenodd" d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1zM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118zM2.5 3V2h11v1h-11z"/></svg></button>';
            echo '</div></div></li>';
        }
        
        echo '</ul></div>';
    }
    
    if (empty($contents['folders']) && empty($contents['files'])) {
        echo '<div class="empty-directory">';
        echo '<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" viewBox="0 0 16 16">
                <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.334 1.82 3 2.19 3h5.396l-.707-.707z"/>
              </svg>';
        echo '<p>This folder is empty</p>';
        echo '<p class="empty-folder-subtext">Upload files or create a new folder to get started</p>';
        echo '</div>';
    }
}

/**
 * Get file icon based on extension
 * 
 * @param string $extension The file extension
 * @return string The appropriate icon
 */
function getFileIcon($extension) {
    $icons = [
        // Documents
        'pdf' => '📕',
        'doc' => '📘',
        'docx' => '📘',
        'txt' => '📄',
        'rtf' => '📄',
        'md' => '📝',
        
        // Code
        'html' => '🌐',
        'css' => '🎨',
        'js' => '⚡',
        'php' => '🐘',
        'json' => '📊',
        'xml' => '📰',
        
        // Images
        'jpg' => '🖼️',
        'jpeg' => '🖼️',
        'png' => '🖼️',
        'gif' => '🖼️',
        'svg' => '🖼️',
        'webp' => '🖼️',
        
        // Media
        'mp3' => '🎵',
        'wav' => '🎵',
        'mp4' => '🎬',
        'webm' => '🎬',
        'avi' => '🎬',
        'mov' => '🎬',
        
        // Archives
        'zip' => '📦',
        'rar' => '📦',
        'tar' => '📦',
        'gz' => '📦'
    ];
    
    return isset($icons[$extension]) ? $icons[$extension] : '📄';
}
?>

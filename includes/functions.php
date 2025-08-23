<?php
/**
 * Common functions for Parker Directory
 */

/**
 * Get directory contents with better handling of current folder
 * 
 * @param string $baseDir Base directory path
 * @return array Associative array with 'folders' and 'files'
 */
function getDirectoryContents($baseDir = "./") {
    // Get current folder from URL, sanitize it to prevent directory traversal
    $currentFolder = "";
    if (isset($_GET['folder'])) {
        $folder = $_GET['folder'];
        // Remove any directory traversal attempts
        $folder = str_replace(['../', '../', '..\\', '.\\'], '', $folder);
        // Make sure the folder exists
        if (is_dir($baseDir . $folder)) {
            $currentFolder = rtrim($folder, '/') . '/';
        }
    }
    
    $fullPath = $baseDir . $currentFolder;
    
    // Initialize arrays for folders and files
    $folders = [];
    $files = [];
    
    // System files that shouldn't be listed
    $hiddenFiles = [
        'index.php', 
        'login.php', 
        'logout.php', 
        'file-viewer.php', 
        'me.php', 
        '.htaccess',
        'README.md'
    ];
    
    // Check if directory exists and is readable
    if (is_dir($fullPath) && is_readable($fullPath)) {
        $dirHandle = opendir($fullPath);
        
        while (($item = readdir($dirHandle)) !== false) {
            // Skip dot entries
            if ($item == '.' || $item == '..') {
                continue;
            }
            
            $itemPath = $fullPath . $item;
            
            // Skip hidden files (starting with .)
            if (substr($item, 0, 1) === '.') {
                continue;
            }
            
            // Skip system files
            if (in_array($item, $hiddenFiles)) {
                continue;
            }
            
            // Add to appropriate array
            if (is_dir($itemPath)) {
                $folders[] = $item;
            } else {
                $files[] = $item;
            }
        }
        
        closedir($dirHandle);
        
        // Sort alphabetically
        sort($folders);
        sort($files);
    }
    
    return [
        'folders' => $folders,
        'files' => $files,
        'currentFolder' => $currentFolder,
        'parentFolder' => getParentFolder($currentFolder)
    ];
}

/**
 * Get parent folder path
 * 
 * @param string $currentFolder Current folder path
 * @return string|null Parent folder path or null if at root
 */
function getParentFolder($currentFolder) {
    if (empty($currentFolder)) {
        return null;
    }
    
    // Remove trailing slash
    $folder = rtrim($currentFolder, '/');
    
    // Find the last slash
    $lastSlashPos = strrpos($folder, '/');
    
    if ($lastSlashPos === false) {
        // No slashes, so parent is root
        return '';
    }
    
    // Return everything before the last slash
    return substr($folder, 0, $lastSlashPos);
}

/**
 * Format file size in human-readable format
 * 
 * @param int $bytes File size in bytes
 * @return string Formatted file size
 */
function formatFileSize($bytes) {
    $units = ['B', 'KB', 'MB', 'GB', 'TB'];
    
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    
    $bytes /= pow(1024, $pow);
    
    return round($bytes, 2) . ' ' . $units[$pow];
}

/**
 * Get appropriate icon for file based on extension
 * 
 * @param string $extension File extension
 * @return string Icon character
 */
function getFileIcon($extension) {
    $extension = strtolower($extension);
    
    $iconMap = [
        // Documents
        'txt' => '📝',
        'pdf' => '📕',
        'doc' => '📘',
        'docx' => '📘',
        'rtf' => '📄',
        'md' => '📖',
        
        // Code
        'php' => '🐘',
        'html' => '🌐',
        'css' => '🎨',
        'js' => '⚡',
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
        'mp4' => '🎬',
        'mov' => '🎬',
        'avi' => '🎬',
        'mp3' => '🎵',
        'wav' => '🎵',
        
        // Archives
        'zip' => '🗜️',
        'rar' => '🗜️',
        'tar' => '🗜️',
        'gz' => '🗜️',
        
        // Other
        'exe' => '⚙️',
        'dll' => '🔧',
    ];
    
    return isset($iconMap[$extension]) ? $iconMap[$extension] : '📄';
}

/**
 * Get descriptive title for file icon emoji
 * 
 * @param string $emoji Icon emoji
 * @param string $fileExt File extension
 * @return string Human-readable description
 */
function getIconTitle($emoji, $fileExt = '') {
    $iconDescriptions = [
        '📁' => 'Folder',
        '📝' => 'Text Document',
        '📕' => 'PDF Document',
        '📘' => 'Word Document',
        '📄' => 'Plain Text',
        '📖' => 'Markdown Document',
        '🐘' => 'PHP Script',
        '🌐' => 'HTML Document',
        '🎨' => 'CSS Stylesheet',
        '⚡' => 'JavaScript File',
        '📊' => 'JSON Data',
        '📰' => 'XML Document',
        '🖼️' => 'Image File',
        '🎬' => 'Video File',
        '🎵' => 'Audio File',
        '🗜️' => 'Archive/Compressed File',
        '⚙️' => 'Executable Program',
        '🔧' => 'System File',
        '📄' => 'Document'
    ];
    
    if (array_key_exists($emoji, $iconDescriptions)) {
        if ($fileExt) {
            return $iconDescriptions[$emoji] . ' (.' . strtoupper($fileExt) . ')';
        }
        return $iconDescriptions[$emoji];
    }
    
    return $fileExt ? 'File type: .' . strtoupper($fileExt) : 'File';
}

/**
 * Sanitize filename to remove unsafe characters
 * 
 * @param string $filename The filename to sanitize
 * @return string Sanitized filename
 */
function sanitizeFilename($filename) {
    // Remove any directory traversal attempts
    $filename = str_replace(['../', '../', '..\\', '.\\'], '', $filename);
    
    // Remove any potentially dangerous characters
    $filename = preg_replace('/[\/\\\:*?"<>|]/', '_', $filename);
    
    // Ensure filename isn't too long
    $maxLength = 255;
    if (strlen($filename) > $maxLength) {
        $ext = pathinfo($filename, PATHINFO_EXTENSION);
        $basename = pathinfo($filename, PATHINFO_FILENAME);
        $basename = substr($basename, 0, $maxLength - strlen($ext) - 1);
        $filename = $basename . '.' . $ext;
    }
    
    return $filename;
}
?>
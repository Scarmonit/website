<?php
// Check for authentication
require_once '../includes/auth.php'; // Handles authentication check
require_once '../includes/config.php'; // Get configuration variables
require_once '../includes/functions.php'; // Include helper functions

// Get the file to view from URL parameter
$fileToView = isset($_GET['file']) ? $_GET['file'] : null;
$folderToView = isset($_GET['folder']) ? $_GET['folder'] : null;
$fileContent = '';
$fileExtension = '';
$fileName = '';
$error = '';

// View file if specified
if ($fileToView) {
    $filePath = "../" . $fileToView;
    
    // Security check: prevent directory traversal
    if (strpos($fileToView, '..') !== false) {
        $error = "Invalid file path.";
    } elseif (!file_exists($filePath)) {
        $error = "File not found.";
    } elseif (!is_file($filePath)) {
        $error = "Not a valid file.";
    } else {
        $fileExtension = strtolower(pathinfo($fileToView, PATHINFO_EXTENSION));
        $fileName = $fileToView;
        
        if (!isViewableFile($fileToView)) {
            $error = "File type not supported for viewing.";
        } else {
            $fileContent = file_get_contents($filePath);
            if ($fileContent === false) {
                $error = "Unable to read file contents.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parker - File Manager</title>
    <link rel="stylesheet" href="/parker/assets/css/style.css">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="container">
        <?php include '../views/components/navbar.php'; ?>
        
        <main id="main-content" role="main">
            <h1><?php echo $fileToView ? 'File Viewer' : 'File Manager'; ?></h1>
            
            <?php if ($error): ?>
                <div class="error-message" role="alert">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
                <a href="/parker/views/file-manager.php" class="back-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                    </svg>
                    Back to File List
                </a>
            <?php endif; ?>
            
            <?php if ($fileToView && !$error): ?>
                <!-- File Viewer Mode -->
                <a href="/parker/views/file-manager.php" class="back-button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15 8a.5.5 0 0 0-.5-.5H2.707l3.147-3.146a.5.5 0 1 0-.708-.708l-4 4a.5.5 0 0 0 0 .708l4 4a.5.5 0 0 0 .708-.708L2.707 8.5H14.5A.5.5 0 0 0 15 8z"/>
                    </svg>
                    Back to File List
                </a>
                
                <div class="file-viewer">
                    <div class="file-header">
                        <div class="file-title">
                            <?php
                            $icon = '📄';
                            $ext = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                            
                            if (isset($FILE_ICONS[$ext])) {
                                $icon = $FILE_ICONS[$ext]['icon'];
                            } elseif (isset($FILE_ICONS['default'])) {
                                $icon = $FILE_ICONS['default']['icon'];
                            }
                            ?>
                            <span class="file-title-icon"><?php echo $icon; ?></span>
                            <?php echo htmlspecialchars($fileName); ?>
                        </div>
                        <div class="file-actions">
                            <button id="copy-btn" class="file-action-btn" aria-label="Copy file content">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/>
                                    <path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/>
                                </svg>
                                Copy
                            </button>
                            <span class="file-type-badge"><?php echo strtoupper($fileExtension); ?></span>
                        </div>
                    </div>
                    
                    <div class="file-content" id="file-content">
                        <pre class="code-block" id="code-block"><code><?php 
                            // Split content into lines for line numbers
                            $lines = explode("\n", htmlspecialchars($fileContent));
                            foreach ($lines as $line) {
                                echo '<div class="code-line">' . $line . '</div>';
                            }
                        ?></code></pre>
                    </div>
                </div>
                
            <?php else: ?>
                <!-- File List/Browser Mode -->
                <div class="file-list">
                    <h2>Select a file to view</h2>
                    <p>Browse and view files in your directory. Supported file types include code, text, and markup files.</p>
                    
                    <div class="file-search">
                        <input type="text" id="fileSearch" placeholder="Search files..." aria-label="Search files">
                        <span class="file-search-icon" aria-hidden="true">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                            </svg>
                        </span>
                    </div>
                    
                    <?php
                    // Get directory contents
                    $basePath = '../';
                    $viewPath = $folderToView ? $folderToView . '/' : '';
                    $dirContents = getDirectoryContents($basePath . $viewPath);
                    
                    // Display folders first if any exist
                    if (!empty($dirContents['folders'])) {
                        echo '<div class="folder-section">';
                        echo '<h2><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19zm4.69-1.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707z"/>
                              </svg> Folders</h2>';
                        echo '<ul class="file-grid folder-grid" role="list" id="folderList">';
                        
                        foreach ($dirContents['folders'] as $folder) {
                            $folderPath = $viewPath . $folder;
                            include '../views/components/file-card.php';
                        }
                        
                        echo '</ul>';
                        echo '</div>';
                    }
                    
                    // Display files
                    if (!empty($dirContents['files'])) {
                        echo '<h2><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                                  <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5zM5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1H5zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1H5z"/>
                                  <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1z"/>
                                </svg> Files</h2>';
                        echo '<ul class="file-grid" role="list" id="fileList">';
                        
                        foreach ($dirContents['files'] as $file) {
                            $filePath = $viewPath . $file;
                            $fullPath = $basePath . $filePath;
                            $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                            $fileModTime = date("M d, Y", filemtime($fullPath));
                            $fileSize = formatFileSize(filesize($fullPath));
                            
                            // Set properties for file card component
                            $fileName = $file;
                            $isDraggable = true;
                            $isDropTarget = false;
                            
                            include '../views/components/file-card.php';
                        }
                        
                        echo '</ul>';
                    }
                    
                    if (empty($dirContents['folders']) && empty($dirContents['files'])) {
                        echo '<div class="empty-directory">';
                        echo '<svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"/>
                              </svg>';
                        echo '<p>No files found in the directory.</p>';
                        echo '</div>';
                    }
                    ?>
                </div>
            <?php endif; ?>
        </main>
        
        <?php include '../views/components/footer.php'; ?>
    </div>
    
    <script src="/parker/assets/js/script.js"></script>
    <script src="/parker/assets/js/file-manager.js"></script>
</body>
</html>

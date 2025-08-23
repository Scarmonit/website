<?php
// Include authentication and config
require_once 'includes/auth.php'; 
require_once 'includes/config.php';
require_once 'includes/functions.php';

// File size limit (10MB by default)
$maxFileSize = defined('MAX_FILE_SIZE') ? MAX_FILE_SIZE : 10 * 1024 * 1024;

// Get the file to view from URL parameter and sanitize
$fileToView = isset($_GET['file']) ? $_GET['file'] : null;
$fileContent = '';
$fileExtension = '';
$fileName = '';
$fileSize = 0;
$error = '';
$isTextFile = false;
$isImageFile = false;
$isMediaFile = false;
$isPdfFile = false;

// Supported file types
$supportedTextTypes = ['txt', 'md', 'html', 'css', 'js', 'php', 'json', 'xml', 'csv', 'log', 'yml', 'yaml', 'ini', 'conf', 'sh', 'bat', 'sql'];
$supportedImageTypes = ['jpg', 'jpeg', 'png', 'gif', 'svg', 'webp', 'bmp', 'ico'];
$supportedMediaTypes = ['mp4', 'webm', 'ogg', 'mp3', 'wav'];
$supportedDocTypes = ['pdf'];

if ($fileToView) {
    // Sanitize: Remove any directory traversal attempts
    $fileToView = str_replace(['../', '../', '..\\', '.\\'], '', $fileToView);
    $filePath = "./" . $fileToView;
    
    // Security check: prevent directory traversal with realpath
    $realFilePath = realpath($filePath);
    $realBasePath = realpath('./');
    
    if (!$realFilePath || strpos($realFilePath, $realBasePath) !== 0) {
        $error = "Invalid file path. Security violation detected.";
    } elseif (!file_exists($filePath)) {
        $error = "File not found: " . htmlspecialchars(basename($fileToView));
    } elseif (!is_file($filePath)) {
        $error = "Not a valid file: " . htmlspecialchars(basename($fileToView));
    } else {
        $fileExtension = strtolower(pathinfo($fileToView, PATHINFO_EXTENSION));
        $fileName = basename($fileToView); // Use basename for extra safety
        $fileSize = filesize($filePath);
        
        // Check file size
        if ($fileSize > $maxFileSize) {
            $error = "File exceeds the maximum size limit of " . formatFileSize($maxFileSize);
        } 
        // Add PHP file restriction
        elseif ($fileExtension === 'php') {
            $error = "PHP files cannot be viewed for security reasons";
        } 
        else {
            // Determine file type using the same logic as in index.php
            $isTextFile = in_array($fileExtension, $supportedTextTypes);
            $isImageFile = in_array($fileExtension, $supportedImageTypes);
            $isMediaFile = in_array($fileExtension, $supportedMediaTypes);
            $isPdfFile = in_array($fileExtension, $supportedDocTypes);
            
            // Load file content for text files
            if ($isTextFile) {
                $fileContent = file_get_contents($filePath);
                if ($fileContent === false) {
                    $error = "Unable to read file contents.";
                }
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
    <title>Parker - File Viewer: <?php echo htmlspecialchars($fileName); ?></title>
    <link rel="stylesheet" href="/parker/assets/css/style.css">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="container">
        <?php include 'views/components/navbar.php'; ?>
        
        <main id="main-content" role="main">
            <h1>File Viewer</h1>
            
            <?php if ($error): ?>
                <div class="error-message" role="alert">
                    <strong>Error:</strong> <?php echo $error; ?>
                </div>
                <a href="index.php" class="back-button">
                    ← Back to Directory
                </a>
            <?php elseif ($fileToView): ?>
                <a href="index.php" class="back-button">
                    ← Back to Directory
                </a>
                
                <div class="file-viewer">
                    <div class="file-header">
                        <div class="file-title">
                            <?php echo htmlspecialchars($fileName); ?>
                            <span class="file-size">(<?php echo formatFileSize($fileSize); ?>)</span>
                        </div>
                        <div class="file-type-badge"><?php echo strtoupper($fileExtension); ?></div>
                    </div>
                    
                    <div class="file-content">
                        <?php if ($isTextFile): ?>
                            <pre class="code-block"><code><?php echo htmlspecialchars($fileContent); ?></code></pre>
                        <?php elseif ($isImageFile): ?>
                            <div class="image-viewer">
                                <img src="<?php echo htmlspecialchars($fileToView); ?>" alt="<?php echo htmlspecialchars($fileName); ?>" class="preview-image">
                            </div>
                        <?php elseif ($isMediaFile): ?>
                            <div class="media-viewer">
                                <?php if (in_array($fileExtension, ['mp4', 'webm', 'ogg'])): ?>
                                <video controls class="preview-video">
                                    <source src="<?php echo htmlspecialchars($fileToView); ?>" type="video/<?php echo $fileExtension === 'mp4' ? 'mp4' : ($fileExtension === 'webm' ? 'webm' : 'ogg'); ?>">
                                    Your browser does not support the video tag.
                                </video>
                                <?php elseif (in_array($fileExtension, ['mp3', 'wav'])): ?>
                                <audio controls class="preview-audio">
                                    <source src="<?php echo htmlspecialchars($fileToView); ?>" type="audio/<?php echo $fileExtension; ?>">
                                    Your browser does not support the audio tag.
                                </audio>
                                <?php endif; ?>
                            </div>
                        <?php elseif ($isPdfFile): ?>
                            <div class="pdf-viewer">
                                <embed src="<?php echo htmlspecialchars($fileToView); ?>" type="application/pdf" width="100%" height="600px" class="preview-pdf">
                            </div>
                        <?php else: ?>
                            <div class="generic-file-info">
                                <p>This file type (<?php echo strtoupper($fileExtension); ?>) can't be previewed directly in the browser.</p>
                                <div class="file-actions">
                                    <a href="<?php echo htmlspecialchars($fileToView); ?>" download class="btn btn-primary">
                                        Download File
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="file-list">
                    <h2>Select a file to view</h2>
                    
                    <div class="file-controls">
                        <div class="file-search">
                            <input type="text" id="fileSearch" placeholder="Search files..." aria-label="Search files">
                            <span class="file-search-icon" aria-hidden="true">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                                </svg>
                            </span>
                        </div>
                        
                        <!-- Add view mode toggle -->
                        <div class="view-mode-toggle" role="group" aria-label="Change view mode">
                            <button id="gridViewBtn" class="view-mode-btn active" aria-pressed="true" title="Grid View">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M1 2.5A1.5 1.5 0 0 1 2.5 1h3A1.5 1.5 0 0 1 7 2.5v3A1.5 1.5 0 0 1 5.5 7h-3A1.5 1.5 0 0 1 1 5.5v-3zm8 0A1.5 1.5 0 0 1 10.5 1h3A1.5 1.5 0 0 1 15 2.5v3A1.5 1.5 0 0 1 13.5 7h-3A1.5 1.5 0 0 1 9 5.5v-3zm-8 8A1.5 1.5 0 0 1 2.5 9h3A1.5 1.5 0 0 1 7 10.5v3A1.5 1.5 0 0 1 5.5 15h-3A1.5 1.5 0 0 1 1 13.5v-3zm8 0A1.5 1.5 0 0 1 10.5 9h3a1.5 1.5 0 0 1 1.5 1.5v3a1.5 1.5 0 0 1-1.5 1.5h-3a1.5 1.5 0 0 1-1.5-1.5v-3z"/>
                                </svg>
                                <span class="sr-only">Grid View</span>
                            </button>
                            <button id="listViewBtn" class="view-mode-btn" aria-pressed="false" title="List View">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M2 2.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5V3a.5.5 0 0 0-.5-.5H2zM3 3H2v1h1V3z"/>
                                    <path d="M5 3.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5zM5.5 7a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9zm0 4a.5.5 0 0 0 0 1h9a.5.5 0 0 0 0-1h-9z"/>
                                    <path fill-rule="evenodd" d="M1.5 7a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5H2a.5.5 0 0 1-.5-.5V7zM2 7h1v1H2V7zm0 3.5a.5.5 0 0 0-.5.5v1a.5.5 0 0 0 .5.5h1a.5.5 0 0 0 .5-.5v-1a.5.5 0 0 0-.5-.5H2zm1 .5H2v1h1v-1z"/>
                                </svg>
                                <span class="sr-only">List View</span>
                            </button>
                        </div>
                    </div>
                    
                    <ul class="file-grid" role="list">
                        <?php
                        $dirContents = getDirectoryContents("./");
                        $viewableFiles = [];
                        
                        // We now allow all file types
                        foreach ($dirContents['files'] as $file) {
                            if ($file != "login.php" && $file != ".htaccess") {
                                $viewableFiles[] = $file;
                            }
                        }
                        
                        if (empty($viewableFiles)) {
                            echo '<li class="empty-directory">';
                            echo 'No viewable files found in the directory.';
                            echo '</li>';
                        } else {
                            foreach ($viewableFiles as $file) {
                                $fileExt = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $fileName = basename($file);
                                $filePath = "./" . $file;
                                $fileSize = filesize($filePath);
                                
                                // Get appropriate icon based on file type
                                $icon = '📄'; // default
                                $isViewable = true;
                                
                                if (in_array($fileExt, $supportedTextTypes)) {
                                    switch ($fileExt) {
                                        case 'php': $icon = '🐘'; break;
                                        case 'html': $icon = '🌐'; break;
                                        case 'css': $icon = '🎨'; break;
                                        case 'js': $icon = '⚡'; break;
                                        case 'txt': $icon = '📝'; break;
                                        case 'md': $icon = '📖'; break;
                                        case 'json': $icon = '📊'; break;
                                        case 'xml': $icon = '📰'; break;
                                        default: $icon = '📄';
                                    }
                                } elseif (in_array($fileExt, $supportedImageTypes)) {
                                    $icon = '🖼️';
                                } elseif (in_array($fileExt, $supportedMediaTypes)) {
                                    $icon = in_array($fileExt, ['mp4', 'webm', 'ogg']) ? '🎬' : '🎵';
                                } elseif (in_array($fileExt, $supportedDocTypes)) {
                                    $icon = '📕';
                                } else {
                                    $icon = '📦';
                                }
                                
                                echo '<li class="file-item">';
                                echo '<a href="file-viewer.php?file=' . urlencode($file) . '" class="file-link">';
                                echo '<span class="file-icon">' . $icon . '</span>';
                                echo '<div class="file-info">';
                                echo '<span class="file-name">' . htmlspecialchars($fileName) . '</span>';
                                echo '<div class="file-meta">';
                                echo '<span>' . strtoupper($fileExt) . '</span>';
                                echo '<span>' . formatFileSize($fileSize) . '</span>';
                                echo '</div>';
                                echo '</div>';
                                echo '</a>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
        </main>
        
        <?php include 'views/components/footer.php'; ?>
    </div>
    
    <script src="/parker/assets/js/script.js"></script>
    <script>
        // Enhance code display with line numbers
        document.addEventListener('DOMContentLoaded', function() {
            const codeBlock = document.querySelector('.code-block code');
            if (codeBlock) {
                // Add line numbers
                const lines = codeBlock.textContent.split('\n');
                let numberedContent = '';
                
                lines.forEach((line, index) => {
                    numberedContent += `<div class="code-line" data-line="${index + 1}">${line}</div>`;
                });
                
                codeBlock.innerHTML = numberedContent;
                
                // Apply syntax highlighting based on file type
                const fileExtension = '<?php echo $fileExtension; ?>';
                applySyntaxHighlighting(codeBlock, fileExtension);
            }
            
            // Initialize file search if we're on the file list page
            const searchInput = document.getElementById('fileSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.toLowerCase();
                    const fileItems = document.querySelectorAll('.file-item');
                    
                    fileItems.forEach(item => {
                        const fileName = item.querySelector('.file-name').textContent.toLowerCase();
                        item.style.display = fileName.includes(searchTerm) ? '' : 'none';
                    });
                });
                
                // Focus search on page load
                setTimeout(() => searchInput.focus(), 100);
            }
        });
        
        function applySyntaxHighlighting(element, extension) {
            // Apply language-specific syntax highlighting classes
            // This is a simple implementation - could be enhanced with a proper syntax highlighting library
            const codeLines = element.querySelectorAll('.code-line');
            
            codeLines.forEach(line => {
                let content = line.textContent;
                
                switch (extension) {
                    case 'php':
                        content = content.replace(/(&lt;\?php|\?&gt;)/g, '<span class="syntax-keyword">$1</span>');
                        content = content.replace(/\b(function|class|if|else|foreach|for|while|return|echo|print|require|include)\b/g, '<span class="syntax-keyword">$1</span>');
                        content = content.replace(/(["'])((?:(?!\1)[^\\]|\\.)*)(\1)/g, '<span class="syntax-string">$1$2$3</span>');
                        content = content.replace(/(\/\/.*$|\/\*[\s\S]*?\*\/)/gm, '<span class="syntax-comment">$1</span>');
                        break;
                        
                    case 'js':
                        content = content.replace(/\b(function|var|let|const|if|else|for|while|return|true|false|null|undefined)\b/g, '<span class="syntax-keyword">$1</span>');
                        content = content.replace(/(["'])((?:(?!\1)[^\\]|\\.)*)(\1)/g, '<span class="syntax-string">$1$2$3</span>');
                        content = content.replace(/(\/\/.*$|\/\*[\s\S]*?\*\/)/gm, '<span class="syntax-comment">$1</span>');
                        break;
                        
                    case 'html':
                        content = content.replace(/(&lt;[\w\/].*?&gt;)/g, '<span class="syntax-tag">$1</span>');
                        content = content.replace(/(["'])((?:(?!\1)[^\\]|\\.)*)(\1)/g, '<span class="syntax-string">$1$2$3</span>');
                        content = content.replace(/(&lt;!--[\s\S]*?--&gt;)/g, '<span class="syntax-comment">$1</span>');
                        break;
                        
                    case 'css':
                        content = content.replace(/([\w-]+)(?=\s*:)/g, '<span class="syntax-property">$1</span>');
                        content = content.replace(/(#[a-fA-F0-9]{3,6})/g, '<span class="syntax-number">$1</span>');
                        content = content.replace(/(\/\*[\s\S]*?\*\/)/g, '<span class="syntax-comment">$1</span>');
                        break;
                }
                
                line.innerHTML = content;
            });
        }
    </script>
</body>
</html>
</html>

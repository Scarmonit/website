<?php
// Check for authentication cookie
if (!isset($_COOKIE['parker_authenticated']) || $_COOKIE['parker_authenticated'] !== 'true') {
    header("Location: /parker/login.php");
    exit;
}

// Get the file to view from URL parameter
$fileToView = isset($_GET['file']) ? $_GET['file'] : null;
$fileContent = '';
$fileExtension = '';
$fileName = '';
$error = '';

// Supported file types for viewing
$supportedTypes = ['txt', 'md', 'html', 'css', 'js', 'php'];

if ($fileToView) {
    $filePath = "./" . $fileToView;
    
    // Security check: prevent directory traversal
    if (strpos($fileToView, '..') !== false || strpos($fileToView, '/') !== false) {
        $error = "Invalid file path.";
    } elseif (!file_exists($filePath)) {
        $error = "File not found.";
    } elseif (!is_file($filePath)) {
        $error = "Not a valid file.";
    } else {
        $fileExtension = strtolower(pathinfo($fileToView, PATHINFO_EXTENSION));
        $fileName = $fileToView;
        
        if (!in_array($fileExtension, $supportedTypes)) {
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
    <title>Parker - File Viewer</title>
    <style>
        /* Mobile-first CSS reset and base styles */
        * {
            box-sizing: border-box;
        }
        
        :root {
            /* CSS custom properties for consistent mobile spacing */
            --mobile-padding: 4vw;
            --desktop-padding: 20px;
            --touch-target: 44px;
            --border-radius: clamp(4px, 1vw, 8px);
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            margin: 0;
            /* Mobile-first padding using viewport units */
            padding: var(--mobile-padding);
            max-width: min(1200px, 95vw);
            margin: 0 auto;
            background-color: #f8f9fa;
            color: #212529;
            /* Fluid font size for better mobile readability */
            font-size: clamp(14px, 4vw, 16px);
        }

        .container {
            background-color: white;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
            /* Ensure full width on very small screens */
            min-width: 0;
        }
        
        header {
            background-color: #343a40;
            color: white;
            /* Mobile-optimized padding with better touch spacing */
            padding: clamp(12px, 3vw, 20px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: clamp(10px, 3vw, 15px);
            /* Better mobile header stacking */
            flex-direction: column;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: clamp(8px, 2vw, 20px);
            flex-wrap: wrap;
            /* Center navigation on mobile */
            justify-content: center;
            width: 100%;
        }
        
        nav a {
            text-decoration: none;
            color: #adb5bd;
            font-weight: 500;
            /* Enhanced touch targets for mobile */
            padding: clamp(12px, 3vw, 12px) clamp(16px, 4vw, 18px);
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            /* Minimum touch target size */
            min-height: var(--touch-target);
            min-width: var(--touch-target);
            display: flex;
            align-items: center;
            justify-content: center;
            /* Better text sizing for mobile */
            font-size: clamp(14px, 3.5vw, 16px);
        }
        
        nav a:hover,
        nav a:focus {
            color: white;
            background-color: rgba(255,255,255,0.1);
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        nav a.active {
            color: white;
            background-color: #007bff;
        }
        
        .logout-btn {
            background-color: #dc3545;
            color: white;
            border: none;
            /* Enhanced mobile touch target */
            padding: clamp(12px, 3vw, 12px) clamp(20px, 5vw, 24px);
            border-radius: var(--border-radius);
            cursor: pointer;
            font-size: clamp(14px, 3.5vw, 16px);
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
            /* Ensure proper touch target */
            min-height: var(--touch-target);
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .logout-btn:hover,
        .logout-btn:focus {
            background-color: #c82333;
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        
        main {
            /* Mobile-first padding with viewport-based scaling */
            padding: clamp(16px, 5vw, 30px);
        }
        
        h1 {
            color: #343a40;
            margin-top: 0;
            margin-bottom: clamp(15px, 4vw, 25px);
            /* Fluid heading size for better mobile scaling */
            font-size: clamp(1.5rem, 6vw, 2.2rem);
            font-weight: 600;
            /* Better line height for mobile readability */
            line-height: 1.2;
        }
        
        h2 {
            color: #343a40;
            margin-bottom: clamp(12px, 3vw, 20px);
            font-size: clamp(1.25rem, 5vw, 1.75rem);
            font-weight: 600;
            line-height: 1.3;
        }
        
        .file-list {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            padding: clamp(16px, 4vw, 25px);
            margin-bottom: clamp(20px, 5vw, 30px);
            border-left: 4px solid #007bff;
        }
        
        .file-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: clamp(8px, 2vw, 12px);
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .file-item {
            background-color: white;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            padding: clamp(12px, 3vw, 16px);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: clamp(8px, 2vw, 12px);
        }
        
        .file-item:hover {
            border-color: #007bff;
            background-color: #f0f8ff;
            transform: translateY(-1px);
        }
        
        .file-link {
            display: flex;
            align-items: center;
            gap: clamp(8px, 2vw, 12px);
            text-decoration: none;
            color: #343a40;
            width: 100%;
            min-height: var(--touch-target);
        }
        
        .file-icon {
            font-size: clamp(1.2rem, 4vw, 1.5rem);
            flex-shrink: 0;
        }
        
        .file-name {
            font-weight: 500;
            font-size: clamp(0.9rem, 3.5vw, 1rem);
            word-break: break-word;
        }
        
        .file-viewer {
            background-color: #f8f9fa;
            border-radius: var(--border-radius);
            overflow: hidden;
            border: 1px solid #dee2e6;
        }
        
        .file-header {
            background-color: #343a40;
            color: white;
            padding: clamp(12px, 3vw, 16px);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: clamp(8px, 2vw, 12px);
        }
        
        .file-title {
            font-weight: 600;
            font-size: clamp(1rem, 4vw, 1.1rem);
            word-break: break-word;
        }
        
        .file-type-badge {
            background-color: #007bff;
            color: white;
            padding: clamp(4px, 1vw, 6px) clamp(8px, 2vw, 12px);
            border-radius: calc(var(--border-radius) / 2);
            font-size: clamp(0.75rem, 3vw, 0.85rem);
            font-weight: 500;
            text-transform: uppercase;
        }
        
        .file-content {
            max-height: 70vh;
            overflow: auto;
            background-color: #ffffff;
        }
        
        .code-block {
            margin: 0;
            padding: clamp(16px, 4vw, 24px);
            font-family: 'Consolas', 'Monaco', 'Courier New', monospace;
            font-size: clamp(12px, 3.5vw, 14px);
            line-height: 1.5;
            white-space: pre-wrap;
            word-wrap: break-word;
            background-color: #ffffff;
            color: #333;
            border: none;
            overflow: visible;
        }
        
        .error-message {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: clamp(12px, 3vw, 16px);
            border-radius: var(--border-radius);
            margin-bottom: clamp(15px, 4vw, 20px);
        }
        
        .back-button {
            display: inline-flex;
            align-items: center;
            gap: clamp(6px, 1.5vw, 8px);
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: clamp(8px, 2vw, 10px) clamp(12px, 3vw, 16px);
            border-radius: var(--border-radius);
            font-size: clamp(0.9rem, 3.5vw, 1rem);
            font-weight: 500;
            transition: background-color 0.3s ease;
            margin-bottom: clamp(15px, 4vw, 20px);
        }
        
        .back-button:hover,
        .back-button:focus {
            background-color: #5a6268;
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        /* Enhanced mobile responsiveness */
        @media (min-width: 480px) {
            .file-grid {
                grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            }
        }
        
        @media (min-width: 768px) {
            body {
                padding: var(--desktop-padding);
                font-size: 16px;
            }
            
            header {
                flex-direction: row;
                justify-content: space-between;
            }
            
            nav ul {
                width: auto;
                justify-content: flex-start;
            }
            
            .file-grid {
                grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            }
        }
        
        /* Enhanced focus styles for accessibility */
        *:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -60px;
            left: clamp(6px, 2vw, 12px);
            background: #007bff;
            color: white;
            padding: clamp(8px, 2vw, 12px) clamp(12px, 3vw, 16px);
            text-decoration: none;
            border-radius: var(--border-radius);
            z-index: 1000;
            font-size: clamp(14px, 3.5vw, 16px);
            min-height: var(--touch-target);
            display: flex;
            align-items: center;
        }
        
        .skip-link:focus {
            top: clamp(6px, 2vw, 12px);
        }
        
        /* Touch device optimizations */
        @media (hover: none) and (pointer: coarse) {
            nav a,
            .logout-btn,
            .file-link,
            .back-button {
                min-height: 48px;
            }
        }
        
        /* Syntax highlighting colors */
        .syntax-keyword { color: #0000ff; font-weight: bold; }
        .syntax-string { color: #008000; }
        .syntax-comment { color: #808080; font-style: italic; }
        .syntax-number { color: #ff0000; }
        .syntax-tag { color: #800080; }
        .syntax-attribute { color: #ff0000; }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="container">
        <header role="banner">
            <nav role="navigation" aria-label="Main navigation">
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <li><a href="file-viewer.php" class="active" aria-current="page">File Viewer</a></li>
                    <li><a href="me.php">About Me</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout-btn" aria-label="Logout from Parker Directory">Logout</a>
        </header>
        
        <main id="main-content" role="main">
            <h1>File Viewer</h1>
            
            <?php if ($error): ?>
                <div class="error-message" role="alert">
                    <strong>Error:</strong> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>
            
            <?php if ($fileToView && !$error): ?>
                <a href="file-viewer.php" class="back-button">
                    ← Back to File List
                </a>
                
                <div class="file-viewer">
                    <div class="file-header">
                        <div class="file-title"><?php echo htmlspecialchars($fileName); ?></div>
                        <div class="file-type-badge"><?php echo strtoupper($fileExtension); ?></div>
                    </div>
                    
                    <div class="file-content">
                        <pre class="code-block"><code><?php echo htmlspecialchars($fileContent); ?></code></pre>
                    </div>
                </div>
                
            <?php else: ?>
                <div class="file-list">
                    <h2>Select a file to view</h2>
                    <p>Supported file types: .txt, .md, .html, .css, .js, .php</p>
                    
                    <ul class="file-grid" role="list">
                        <?php
                        $dir = "./";
                        $files = scandir($dir);
                        $viewableFiles = [];
                        
                        // Filter for supported file types
                        foreach ($files as $file) {
                            if ($file != "." && $file != ".." && $file != "login.php" && $file != ".htaccess" && is_file($dir . $file)) {
                                $ext = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                if (in_array($ext, $supportedTypes)) {
                                    $viewableFiles[] = $file;
                                }
                            }
                        }
                        
                        if (empty($viewableFiles)) {
                            echo '<li style="text-align: center; padding: 20px; color: #6c757d; font-style: italic;">';
                            echo 'No viewable files found in the directory.';
                            echo '</li>';
                        } else {
                            foreach ($viewableFiles as $file) {
                                $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                                $fileName = htmlspecialchars($file);
                                
                                // Get appropriate icon
                                $icon = '📄';
                                switch ($fileExtension) {
                                    case 'php': $icon = '🐘'; break;
                                    case 'html': $icon = '🌐'; break;
                                    case 'css': $icon = '🎨'; break;
                                    case 'js': $icon = '⚡'; break;
                                    case 'txt': $icon = '📝'; break;
                                    case 'md': $icon = '📖'; break;
                                }
                                
                                echo '<li class="file-item">';
                                echo '<a href="file-viewer.php?file=' . urlencode($file) . '" class="file-link">';
                                echo '<span class="file-icon">' . $icon . '</span>';
                                echo '<span class="file-name">' . $fileName . '</span>';
                                echo '</a>';
                                echo '</li>';
                            }
                        }
                        ?>
                    </ul>
                </div>
            <?php endif; ?>
        </main>
    </div>
    
    <script>
        // Basic syntax highlighting for better readability
        document.addEventListener('DOMContentLoaded', function() {
            const codeBlock = document.querySelector('.code-block code');
            if (codeBlock) {
                const fileExtension = '<?php echo $fileExtension; ?>';
                applySyntaxHighlighting(codeBlock, fileExtension);
            }
        });
        
        function applySyntaxHighlighting(element, extension) {
            let content = element.textContent;
            
            // Simple syntax highlighting patterns
            if (extension === 'php') {
                content = content.replace(/(&lt;\?php|\?&gt;)/g, '<span class="syntax-tag">$1</span>');
                content = content.replace(/\b(function|class|if|else|foreach|for|while|return|echo|print)\b/g, '<span class="syntax-keyword">$1</span>');
                content = content.replace(/(["'])((?:(?!\1)[^\\]|\\.)*)(\1)/g, '<span class="syntax-string">$1$2$3</span>');
                content = content.replace(/(\/\*[\s\S]*?\*\/|\/\/.*$)/gm, '<span class="syntax-comment">$1</span>');
            } else if (extension === 'html') {
                content = content.replace(/(&lt;\/?\w+(?:\s+\w+(?:=(?:"[^"]*"|'[^']*'|[^\s&gt;]+))?)*\s*\/?\&gt;)/g, '<span class="syntax-tag">$1</span>');
                content = content.replace(/(\w+)=(["'][^"']*["'])/g, '<span class="syntax-attribute">$1</span>=$2');
            } else if (extension === 'css') {
                content = content.replace(/([a-zA-Z-]+)(?=\s*:)/g, '<span class="syntax-attribute">$1</span>');
                content = content.replace(/(["'][^"']*["'])/g, '<span class="syntax-string">$1</span>');
                content = content.replace(/(\/\*[\s\S]*?\*\/)/g, '<span class="syntax-comment">$1</span>');
            } else if (extension === 'js') {
                content = content.replace(/\b(function|var|let|const|if|else|for|while|return|true|false|null|undefined)\b/g, '<span class="syntax-keyword">$1</span>');
                content = content.replace(/(["'])((?:(?!\1)[^\\]|\\.)*)(\1)/g, '<span class="syntax-string">$1$2$3</span>');
                content = content.replace(/(\/\*[\s\S]*?\*\/|\/\/.*$)/gm, '<span class="syntax-comment">$1</span>');
                content = content.replace(/\b(\d+\.?\d*)\b/g, '<span class="syntax-number">$1</span>');
            }
            
            element.innerHTML = content;
        }
        
        // Mobile scroll optimization for code viewer
        const fileContent = document.querySelector('.file-content');
        if (fileContent) {
            // Smooth scrolling for mobile
            fileContent.style.webkitOverflowScrolling = 'touch';
            
            // Show scroll hint on mobile
            if (window.innerWidth <= 768 && fileContent.scrollHeight > fileContent.clientHeight) {
                console.log('File content is scrollable on mobile');
            }
        }
    </script>
</body>
</html>
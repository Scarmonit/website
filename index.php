<?php
// Check for authentication cookie
if (!isset($_COOKIE['parker_authenticated']) || $_COOKIE['parker_authenticated'] !== 'true') {
    header("Location: /parker/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parker - Directory</title>
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
            max-width: min(900px, 95vw);
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
            margin-bottom: clamp(12px, 3vw, 20px);
            /* Fluid heading size for better mobile scaling */
            font-size: clamp(1.5rem, 6vw, 2.2rem);
            font-weight: 600;
            /* Better line height for mobile readability */
            line-height: 1.2;
        }
        
        .welcome-message {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: var(--border-radius);
            /* Mobile-optimized padding */
            padding: clamp(12px, 4vw, 20px);
            margin-bottom: clamp(20px, 5vw, 30px);
            color: #0c5460;
            /* Better mobile font size */
            font-size: clamp(14px, 3.8vw, 16px);
        }
        
        .directory-section {
            margin-top: clamp(20px, 5vw, 30px);
        }
        
        .directory-section h2 {
            color: #343a40;
            margin-bottom: clamp(15px, 4vw, 25px);
            /* Responsive heading size */
            font-size: clamp(1.25rem, 5vw, 1.75rem);
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: clamp(8px, 2vw, 12px);
            line-height: 1.3;
        }
        
        .file-grid {
            display: grid;
            /* Mobile-first grid with better small screen handling */
            grid-template-columns: 1fr;
            gap: clamp(12px, 3vw, 18px);
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .file-item {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
            overflow: hidden;
            /* Better mobile shadow */
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        
        .file-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        
        .file-link {
            display: block;
            /* Enhanced mobile touch area */
            padding: clamp(16px, 4vw, 24px);
            text-decoration: none;
            color: #343a40;
            height: 100%;
            /* Minimum touch target for mobile */
            min-height: var(--touch-target);
            display: flex;
            align-items: center;
            gap: clamp(12px, 3vw, 16px);
        }
        
        .file-link:focus {
            outline: 2px solid #007bff;
            outline-offset: -2px;
        }
        
        .file-icon {
            /* Responsive icon size for mobile */
            font-size: clamp(1.8rem, 6vw, 2.5rem);
            flex-shrink: 0;
            line-height: 1;
        }
        
        .file-info {
            flex: 1;
            min-width: 0;
        }
        
        .file-name {
            font-weight: 600;
            /* Mobile-optimized text size */
            font-size: clamp(1rem, 4vw, 1.2rem);
            margin-bottom: clamp(4px, 1vw, 8px);
            word-break: break-word;
            line-height: 1.3;
        }
        
        .file-type {
            /* Better mobile readability */
            font-size: clamp(0.8rem, 3vw, 0.95rem);
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .empty-directory {
            text-align: center;
            padding: clamp(30px, 8vw, 50px);
            color: #6c757d;
            font-style: italic;
            font-size: clamp(14px, 4vw, 16px);
        }
        
        /* Enhanced mobile responsiveness with more breakpoints */
        @media (min-width: 480px) {
            /* Small tablets and large phones */
            .file-grid {
                grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            }
            
            .file-link {
                flex-direction: column;
                text-align: center;
                align-items: center;
            }
            
            .file-info {
                width: 100%;
            }
        }
        
        @media (min-width: 768px) {
            /* Tablets and small desktops */
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
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 20px;
            }
            
            .file-link {
                flex-direction: column;
                text-align: center;
                padding: 24px;
            }
        }
        
        @media (min-width: 1024px) {
            /* Large screens */
            .file-grid {
                grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
                gap: 24px;
            }
        }
        
        /* Enhanced focus styles for better mobile accessibility */
        *:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        /* Better mobile skip link */
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
            /* Remove hover effects on touch devices */
            .file-item:hover {
                transform: none;
            }
            
            /* Increase touch targets on touch devices */
            nav a,
            .logout-btn,
            .file-link {
                min-height: 48px;
            }
        }
    </style>
</head>

<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="container">
        <header role="banner">
            <nav role="navigation" aria-label="Main navigation">
                <ul>
                    <li><a href="index.php" class="active" aria-current="page">Home</a></li>
                    <li><a href="me.php">About Me</a></li>
                </ul>
            </nav>
            <a href="logout.php" class="logout-btn" aria-label="Logout from Parker Directory">Logout</a>
        </header>
        
        <main id="main-content" role="main">
            <h1>Parker Directory</h1>
            
            <div class="welcome-message">
                <p><strong>Welcome!</strong> You have successfully authenticated and can now access the directory contents.</p>
            </div>

            <section class="directory-section">
                <h2>Directory Contents</h2>
                
                <?php
                // Display directory contents
                $dir = "./";
                $files = scandir($dir);
                $validFiles = [];
                
                // Filter files
                foreach ($files as $file) {
                    if ($file != "." && $file != ".." && $file != "login.php" && $file != ".htaccess") {
                        $validFiles[] = $file;
                    }
                }
                
                if (empty($validFiles)) {
                    echo '<div class="empty-directory">';
                    echo '<p>No files found in the directory.</p>';
                    echo '</div>';
                } else {
                    echo '<ul class="file-grid" role="list">';
                    
                    foreach ($validFiles as $file) {
                        $fileExtension = strtolower(pathinfo($file, PATHINFO_EXTENSION));
                        $fileName = htmlspecialchars($file);
                        $fileLink = htmlspecialchars($file);
                        
                        // Determine file icon based on extension
                        $icon = '📄'; // default
                        switch ($fileExtension) {
                            case 'php':
                                $icon = '🐘';
                                $fileType = 'PHP File';
                                break;
                            case 'html':
                            case 'htm':
                                $icon = '🌐';
                                $fileType = 'HTML File';
                                break;
                            case 'css':
                                $icon = '🎨';
                                $fileType = 'CSS File';
                                break;
                            case 'js':
                                $icon = '⚡';
                                $fileType = 'JavaScript File';
                                break;
                            case 'txt':
                                $icon = '📝';
                                $fileType = 'Text File';
                                break;
                            case 'pdf':
                                $icon = '📕';
                                $fileType = 'PDF Document';
                                break;
                            case 'jpg':
                            case 'jpeg':
                            case 'png':
                            case 'gif':
                                $icon = '🖼️';
                                $fileType = 'Image File';
                                break;
                            default:
                                $icon = '📄';
                                $fileType = strtoupper($fileExtension) . ' File';
                        }
                        
                        if (empty($fileExtension)) {
                            $fileType = 'File';
                        }
                        
                        echo '<li class="file-item">';
                        echo '<a href="' . $fileLink . '" class="file-link" aria-label="Open ' . $fileName . '">';
                        echo '<span class="file-icon" aria-hidden="true">' . $icon . '</span>';
                        echo '<div class="file-info">';
                        echo '<div class="file-name">' . $fileName . '</div>';
                        echo '<div class="file-type">' . $fileType . '</div>';
                        echo '</div>';
                        echo '</a>';
                        echo '</li>';
                    }
                    
                    echo '</ul>';
                }
                ?>
            </section>
        </main>
    </div>
</body>

</html>
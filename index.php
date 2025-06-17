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
        * {
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 20px;
            max-width: 900px;
            margin: 0 auto;
            background-color: #f8f9fa;
            color: #212529;
        }

        .container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        header {
            background-color: #343a40;
            color: white;
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
        }
        
        nav a {
            text-decoration: none;
            color: #adb5bd;
            font-weight: 500;
            padding: 8px 12px;
            border-radius: 4px;
            transition: all 0.3s ease;
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
            padding: 8px 16px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            text-decoration: none;
            font-weight: 500;
            transition: background-color 0.3s ease;
        }
        
        .logout-btn:hover,
        .logout-btn:focus {
            background-color: #c82333;
            outline: 2px solid #fff;
            outline-offset: 2px;
        }
        
        main {
            padding: 30px;
        }
        
        h1 {
            color: #343a40;
            margin-top: 0;
            margin-bottom: 15px;
            font-size: 2rem;
            font-weight: 600;
        }
        
        .welcome-message {
            background-color: #d1ecf1;
            border: 1px solid #bee5eb;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 30px;
            color: #0c5460;
        }
        
        .directory-section {
            margin-top: 30px;
        }
        
        .directory-section h2 {
            color: #343a40;
            margin-bottom: 20px;
            font-size: 1.5rem;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
            padding-bottom: 10px;
        }
        
        .file-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 15px;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .file-item {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            transition: all 0.3s ease;
            overflow: hidden;
        }
        
        .file-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            border-color: #007bff;
        }
        
        .file-link {
            display: block;
            padding: 20px;
            text-decoration: none;
            color: #343a40;
            height: 100%;
        }
        
        .file-link:focus {
            outline: 2px solid #007bff;
            outline-offset: -2px;
        }
        
        .file-icon {
            font-size: 2rem;
            margin-bottom: 10px;
            display: block;
        }
        
        .file-name {
            font-weight: 600;
            font-size: 1.1rem;
            margin-bottom: 5px;
            word-break: break-word;
        }
        
        .file-type {
            font-size: 0.9rem;
            color: #6c757d;
            text-transform: uppercase;
        }
        
        .empty-directory {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
        
        /* Mobile responsiveness */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }
            
            header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            nav ul {
                justify-content: center;
            }
            
            main {
                padding: 20px;
            }
            
            h1 {
                font-size: 1.75rem;
            }
            
            .file-grid {
                grid-template-columns: 1fr;
                gap: 12px;
            }
            
            .file-link {
                padding: 15px;
            }
        }
        
        @media (max-width: 480px) {
            nav ul {
                flex-direction: column;
                gap: 10px;
                width: 100%;
            }
            
            nav a {
                text-align: center;
                width: 100%;
            }
            
            .file-grid {
                grid-template-columns: 1fr;
            }
        }
        
        /* Focus styles for accessibility */
        *:focus {
            outline: 2px solid #007bff;
            outline-offset: 2px;
        }
        
        /* Skip link for accessibility */
        .skip-link {
            position: absolute;
            top: -40px;
            left: 6px;
            background: #007bff;
            color: white;
            padding: 8px;
            text-decoration: none;
            border-radius: 4px;
            z-index: 1000;
        }
        
        .skip-link:focus {
            top: 6px;
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
                        echo '<div class="file-name">' . $fileName . '</div>';
                        echo '<div class="file-type">' . $fileType . '</div>';
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
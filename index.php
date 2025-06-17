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
        
        /* Drag and drop styles */
        .file-item[draggable="true"] {
            cursor: grab;
        }
        
        .file-item[draggable="true"]:active {
            cursor: grabbing;
        }
        
        .file-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg);
            z-index: 1000;
        }
        
        .file-item[data-drop-target="true"] {
            position: relative;
        }
        
        .file-item[data-drop-target="true"].drag-over {
            border: 2px dashed #007bff;
            background-color: #e3f2fd;
            transform: scale(1.02);
        }
        
        .file-item[data-drop-target="true"].drag-over .file-icon {
            animation: folderPulse 0.6s infinite alternate;
        }
        
        @keyframes folderPulse {
            from { transform: scale(1); }
            to { transform: scale(1.1); }
        }
        
        .folder-link {
            cursor: pointer;
        }
        
        .folder-link:hover .file-icon {
            transform: scale(1.05);
        }
        
        /* Visual feedback for successful operations */
        .file-item.move-success {
            border: 2px solid #28a745;
            background-color: #d4edda;
        }
        
        .file-item.move-error {
            border: 2px solid #dc3545;
            background-color: #f8d7da;
        }
        
        /* Mobile drag and drop adjustments */
        @media (max-width: 768px) {
            .file-item[data-drop-target="true"].drag-over {
                transform: scale(1.05);
                border-width: 3px;
            }
            
            .file-item.dragging {
                transform: scale(0.95) rotate(3deg);
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
                        $isFolder = false;
                        
                        // Check if item is a directory
                        if (is_dir($dir . $file)) {
                            $icon = '📁';
                            $fileType = 'Folder';
                            $isFolder = true;
                        } else {
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
                        }
                        
                        // Add drag-and-drop attributes
                        $dragAttributes = '';
                        $dropAttributes = '';
                        
                        if ($isFolder) {
                            // Folders are drop targets
                            $dropAttributes = 'data-drop-target="true" data-folder-name="' . htmlspecialchars($file) . '"';
                        } else {
                            // Files are draggable
                            $dragAttributes = 'draggable="true" data-file-name="' . htmlspecialchars($file) . '"';
                        }
                        
                        echo '<li class="file-item" ' . $dragAttributes . ' ' . $dropAttributes . '>';
                        
                        if ($isFolder) {
                            echo '<div class="file-link folder-link" aria-label="Open folder ' . $fileName . '">';
                        } else {
                            echo '<a href="' . $fileLink . '" class="file-link" aria-label="Open ' . $fileName . '">';
                        }
                        
                        echo '<span class="file-icon" aria-hidden="true">' . $icon . '</span>';
                        echo '<div class="file-info">';
                        echo '<div class="file-name">' . $fileName . '</div>';
                        echo '<div class="file-type">' . $fileType . '</div>';
                        echo '</div>';
                        
                        if ($isFolder) {
                            echo '</div>';
                        } else {
                            echo '</a>';
                        }
                        
                        echo '</li>';
                    }
                    
                    echo '</ul>';
                }
                ?>
            </section>
        </main>
    </div>
    
    <script>
        // Drag and Drop File Organization System
        document.addEventListener('DOMContentLoaded', function() {
            let draggedElement = null;
            let draggedFileName = null;
            
            // Get all draggable files and drop targets
            const draggableFiles = document.querySelectorAll('.file-item[draggable="true"]');
            const dropTargets = document.querySelectorAll('.file-item[data-drop-target="true"]');
            
            // Add drag event listeners to files
            draggableFiles.forEach(file => {
                // Drag start - when user starts dragging a file
                file.addEventListener('dragstart', function(e) {
                    draggedElement = this;
                    draggedFileName = this.dataset.fileName;
                    this.classList.add('dragging');
                    
                    // Set drag data for accessibility
                    e.dataTransfer.setData('text/plain', draggedFileName);
                    e.dataTransfer.effectAllowed = 'move';
                    
                    console.log('Started dragging:', draggedFileName);
                });
                
                // Drag end - when user stops dragging
                file.addEventListener('dragend', function(e) {
                    this.classList.remove('dragging');
                    
                    // Remove drag-over class from all drop targets
                    dropTargets.forEach(target => {
                        target.classList.remove('drag-over');
                    });
                    
                    draggedElement = null;
                    draggedFileName = null;
                });
            });
            
            // Add drop event listeners to folders
            dropTargets.forEach(folder => {
                // Prevent default drag over behavior
                folder.addEventListener('dragover', function(e) {
                    e.preventDefault();
                    e.dataTransfer.dropEffect = 'move';
                    
                    // Add visual feedback
                    this.classList.add('drag-over');
                });
                
                // Remove visual feedback when leaving drop zone
                folder.addEventListener('dragleave', function(e) {
                    // Only remove if we're not moving to a child element
                    if (!this.contains(e.relatedTarget)) {
                        this.classList.remove('drag-over');
                    }
                });
                
                // Handle the drop event
                folder.addEventListener('drop', function(e) {
                    e.preventDefault();
                    this.classList.remove('drag-over');
                    
                    if (draggedElement && draggedFileName) {
                        const targetFolderName = this.dataset.folderName;
                        
                        // Simulate file move operation
                        moveFileToFolder(draggedFileName, targetFolderName, draggedElement, this);
                    }
                });
                
                // Add click event for folder navigation (bonus feature)
                const folderLink = folder.querySelector('.folder-link');
                if (folderLink) {
                    folderLink.addEventListener('click', function(e) {
                        e.preventDefault();
                        const folderName = folder.dataset.folderName;
                        navigateToFolder(folderName);
                    });
                }
            });
            
            // Simulated backend file move operation
            function moveFileToFolder(fileName, folderName, fileElement, folderElement) {
                console.log(`Moving ${fileName} to ${folderName}`);
                
                // Show loading state
                fileElement.style.opacity = '0.6';
                fileElement.style.pointerEvents = 'none';
                
                // REAL BACKEND INTEGRATION WOULD GO HERE:
                // fetch('move_file.php', {
                //     method: 'POST',
                //     headers: { 'Content-Type': 'application/json' },
                //     body: JSON.stringify({ fileName, folderName })
                // })
                // .then(response => response.json())
                // .then(data => {
                //     if (data.success) {
                //         showMoveSuccess(fileElement, folderElement, fileName, folderName);
                //     } else {
                //         showMoveError(fileElement, fileName, folderName);
                //     }
                // })
                // .catch(error => showMoveError(fileElement, fileName, folderName));
                
                // Simulate API call delay
                setTimeout(() => {
                    // Simulate success/failure (90% success rate for demo)
                    const success = Math.random() > 0.1;
                    
                    if (success) {
                        // Success: Remove file from current view
                        showMoveSuccess(fileElement, folderElement, fileName, folderName);
                        
                        // Remove file after animation
                        setTimeout(() => {
                            fileElement.remove();
                        }, 1500);
                        
                    } else {
                        // Error: Show error feedback
                        showMoveError(fileElement, fileName, folderName);
                    }
                    
                    // Reset element state
                    fileElement.style.opacity = '';
                    fileElement.style.pointerEvents = '';
                    
                }, 500); // Simulate network delay
            }
            
            // Show success feedback
            function showMoveSuccess(fileElement, folderElement, fileName, folderName) {
                // Animate file moving towards folder
                fileElement.classList.add('move-success');
                folderElement.classList.add('move-success');
                
                // Show success message
                showNotification(`✅ Moved "${fileName}" to "${folderName}"`, 'success');
                
                // Clean up classes
                setTimeout(() => {
                    folderElement.classList.remove('move-success');
                }, 1500);
            }
            
            // Show error feedback
            function showMoveError(fileElement, fileName, folderName) {
                fileElement.classList.add('move-error');
                
                // Show error message
                showNotification(`❌ Failed to move "${fileName}" to "${folderName}"`, 'error');
                
                // Clean up classes
                setTimeout(() => {
                    fileElement.classList.remove('move-error');
                }, 2000);
            }
            
            // Folder navigation (bonus feature)
            function navigateToFolder(folderName) {
                console.log(`Navigating to folder: ${folderName}`);
                showNotification(`📁 Opening "${folderName}"...`, 'info');
                
                // In a real app, this would navigate to the folder
                // For demo purposes, we'll just show a message
                setTimeout(() => {
                    showNotification(`This would open the "${folderName}" folder`, 'info');
                }, 1000);
            }
            
            // Notification system
            function showNotification(message, type = 'info') {
                // Remove existing notification
                const existing = document.querySelector('.file-notification');
                if (existing) {
                    existing.remove();
                }
                
                // Create notification element
                const notification = document.createElement('div');
                notification.className = `file-notification file-notification-${type}`;
                
                // Detect mobile for responsive positioning
                const isMobile = window.innerWidth <= 768;
                
                notification.style.cssText = `
                    position: fixed;
                    top: ${isMobile ? '10px' : '20px'};
                    ${isMobile ? 'left: 10px; right: 10px;' : 'right: 20px; max-width: 300px;'}
                    background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
                    border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
                    color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
                    padding: ${isMobile ? '10px 15px' : '12px 20px'};
                    border-radius: 6px;
                    font-size: ${isMobile ? '13px' : '14px'};
                    z-index: 10000;
                    word-wrap: break-word;
                    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                `;
                
                notification.textContent = message;
                document.body.appendChild(notification);
                
                // Animate in
                setTimeout(() => {
                    notification.style.transform = 'translateX(0)';
                }, 10);
                
                // Auto remove after delay
                setTimeout(() => {
                    notification.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        if (notification.parentNode) {
                            notification.remove();
                        }
                    }, 300);
                }, type === 'error' ? 4000 : 3000);
            }
            
            // Touch device support for mobile
            if ('ontouchstart' in window) {
                console.log('Touch device detected - drag and drop available');
                
                // Add touch-specific instructions
                const firstDraggable = document.querySelector('.file-item[draggable="true"]');
                if (firstDraggable) {
                    showNotification('💡 Long press and drag files to folders', 'info');
                }
            }
            
            // Keyboard accessibility for drag and drop
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape' && draggedElement) {
                    // Cancel current drag operation
                    draggedElement.classList.remove('dragging');
                    dropTargets.forEach(target => {
                        target.classList.remove('drag-over');
                    });
                    draggedElement = null;
                    draggedFileName = null;
                    showNotification('Drag operation cancelled', 'info');
                }
            });
            
            // Log initialization
            console.log(`File manager initialized with ${draggableFiles.length} files and ${dropTargets.length} folders`);
            
            // Show initial tip if there are draggable files and folders
            if (draggableFiles.length > 0 && dropTargets.length > 0) {
                setTimeout(() => {
                    showNotification('💡 Drag files onto folders to organize them', 'info');
                }, 1500);
            }
        });
    </script>
</body>

</html>
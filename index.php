<?php
// Include authentication and config
require_once 'includes/auth.php';
require_once 'includes/config.php';
require_once 'includes/functions.php';

// Get current folder from query string
$currentFolder = isset($_GET['folder']) ? $_GET['folder'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parker - Directory</title>
    <link rel="stylesheet" href="/parker/assets/css/style.css">
    <link rel="stylesheet" href="/parker/assets/css/dark-mode.css">
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="container">
        <?php include 'views/components/navbar.php'; ?>
        
        <!-- Global search bar -->
        <div class="global-search-container">
            <div class="search-wrapper">
                <input type="text" id="globalFileSearch" class="global-search-input" placeholder="Search files and folders..." aria-label="Search all files and folders">
                <button type="button" id="clearSearch" class="clear-search-btn" aria-label="Clear search">×</button>
                <span class="global-search-icon" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                    </svg>
                </span>
            </div>
            <div id="searchResults" class="search-results-count" aria-live="polite"></div>
        </div>
        
        <main id="main-content" role="main">
            <h1>Parker Directory</h1>
            
            <div class="welcome-message">
                <p><strong>Welcome!</strong> You have successfully authenticated and can now access the directory contents.</p>
            </div>

            <!-- Modern directory controls with filter pills -->
            <div class="directory-controls">
                <div class="search-container">
                    <input type="text" id="fileSearch" class="search-input" placeholder="Search files..." aria-label="Search files">
                    <span class="search-icon" aria-hidden="true">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z"/>
                        </svg>
                    </span>
                </div>
                
                <!-- Filter buttons replaced with toggle pills -->
                <div class="filter-pills" id="fileTypeFilters">
                    <button class="filter-pill active" data-filter="all">All Files</button>
                    <button class="filter-pill" data-filter="folder">Folders</button>
                    <button class="filter-pill" data-filter="document">Documents</button>
                    <button class="filter-pill" data-filter="image">Images</button>
                    <button class="filter-pill" data-filter="code">Code</button>
                </div>
                
                <!-- Sort options -->
                <div class="sort-options">
                    <label for="fileSort" class="sort-label">Sort by:</label>
                    <div class="sort-dropdown-wrapper">
                        <select id="fileSort" class="sort-dropdown" aria-label="Sort files">
                            <option value="name-asc">Name (A-Z)</option>
                            <option value="name-desc">Name (Z-A)</option>
                            <option value="date-desc">Date (New-Old)</option>
                            <option value="date-asc">Date (Old-New)</option>
                            <option value="size-desc">Size (Large-Small)</option>
                            <option value="size-asc">Size (Small-Large)</option>
                        </select>
                    </div>
                </div>
                
                <!-- View mode toggle -->
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
                
                <!-- Action buttons -->
                <div class="action-buttons">
                    <button id="createFolderBtn" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.54 3.87.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.826a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31zM2.19 4a1 1 0 0 0-.996 1.09l.637 7a1 1 0 0 0 .995.91h10.348a1 1 0 0 0 .995-.91l.637-7A1 1 0 0 0 13.81 4H2.19z"/>
                            <path d="M8.5 7a.5.5 0 0 1 .5.5V9h1.5a.5.5 0 0 1 0 1H9v1.5a.5.5 0 0 1-1 0V10H6.5a.5.5 0 0 1 0-1H8V7.5a.5.5 0 0 1 .5-.5z"/>
                        </svg>
                        Create Folder
                    </button>
                    
                    <button id="uploadButton" class="btn btn-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                        </svg>
                        Upload File
                    </button>
                </div>
            </div>

            <!-- Breadcrumb navigation -->
            <div class="breadcrumb-navigation">
                <a href="index.php" class="breadcrumb-item root">Home</a>
                <?php if (!empty($currentFolder)): ?>
                    <?php
                    $pathParts = explode('/', rtrim($currentFolder, '/'));
                    $cumulativePath = '';
                    
                    foreach ($pathParts as $part) {
                        $cumulativePath .= $part . '/';
                        echo '<span class="breadcrumb-separator">/</span>';
                        echo '<a href="index.php?folder=' . urlencode(rtrim($cumulativePath, '/')) . '" class="breadcrumb-item">' . htmlspecialchars($part) . '</a>';
                    }
                    ?>
                <?php endif; ?>
            </div>
            
            <!-- Modern drag-and-drop upload zone -->
            <div id="dropZone" class="drop-zone">
                <div class="drop-zone-content">
                    <div class="drop-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                        </svg>
                    </div>
                    <h3>Drag & Drop Files Here</h3>
                    <p>or</p>
                    <label for="fileInputDrop" class="btn btn-primary">Choose Files</label>
                    <input type="file" id="fileInputDrop" multiple style="display:none">
                </div>
            </div>
            
            <!-- File and folder listing container - will be populated by AJAX -->
            <div id="directoryContents" class="directory-contents">
                <!-- Loading spinner -->
                <div id="loadingSpinner" class="loading-spinner">
                    <div class="spinner"></div>
                    <p>Loading content...</p>
                </div>
            </div>
            
            <!-- Selection toolbar -->
            <div id="selectionToolbar" class="selection-toolbar">
                <div class="selection-count"><span id="selectedCount">0</span> items selected</div>
                <div class="selection-actions">
                    <button id="downloadSelected" class="btn btn-sm btn-secondary">Download</button>
                    <button id="moveSelected" class="btn btn-sm btn-secondary">Move</button>
                    <button id="deleteSelected" class="btn btn-sm btn-danger">Delete</button>
                </div>
            </div>
        </main>
        
        <?php include 'views/components/footer.php'; ?>
    </div>
    
    <!-- Toast notifications container -->
    <div id="toastContainer" class="toast-container"></div>
    
    <!-- Modals -->
    <div id="uploadModal" class="modal">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Upload Files</h3>
                <button type="button" class="modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="upload-dropzone">
                    <div class="upload-icon">
                        <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                        </svg>
                    </div>
                    <h4>Drag & Drop Files Here</h4>
                    <p>or</p>
                    <label for="fileInput" class="btn btn-primary">Select Files</label>
                    <input type="file" id="fileInput" multiple style="display:none">
                </div>
                
                <div id="uploadProgress" class="upload-progress-container" style="display:none;">
                    <h4>Uploading Files</h4>
                    <div id="uploadProgressList" class="upload-progress-list"></div>
                </div>
                
                <div class="upload-info">
                    <p class="upload-note">
                        <strong>Note:</strong> Maximum file size is 10MB.
                        <?php if (!empty($currentFolder)): ?>
                        Files will be uploaded to: <strong><?php echo htmlspecialchars($currentFolder); ?></strong>
                        <?php else: ?>
                        Files will be uploaded to the root folder.
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="button" id="uploadSubmit" class="btn btn-primary">Upload</button>
            </div>
        </div>
    </div>
    
    <!-- Create folder modal -->
    <div id="createFolderModal" class="modal">
        <div class="modal-backdrop"></div>
        <div class="modal-container">
            <div class="modal-header">
                <h3 class="modal-title">Create New Folder</h3>
                <button type="button" class="modal-close" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="folderName">Folder Name:</label>
                    <input type="text" id="folderName" class="form-control" placeholder="Enter folder name">
                    <div id="folderNameError" class="error-message" style="display:none;"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary modal-cancel">Cancel</button>
                <button type="button" id="createFolderSubmit" class="btn btn-primary">Create</button>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="/parker/assets/js/app.js" type="module"></script>
</body>
</html>
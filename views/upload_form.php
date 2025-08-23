<?php
require_once '../includes/auth.php';
require_once '../includes/config.php';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['files'])) {
    $uploadResults = [];
    $uploadedFiles = [];
    $errors = [];
    
    foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
        if ($_FILES['files']['error'][$key] === UPLOAD_ERR_OK) {
            $fileName = sanitizeFilename($_FILES['files']['name'][$key]);
            $fileSize = $_FILES['files']['size'][$key];
            $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
            
            // Validate file
            if ($fileSize > UPLOAD_MAX_SIZE) {
                $errors[] = "$fileName: File too large (max " . formatFileSize(UPLOAD_MAX_SIZE) . ")";
                continue;
            }
            
            if (!in_array($fileExt, ALLOWED_EXTENSIONS)) {
                $errors[] = "$fileName: File type not allowed";
                continue;
            }
            
            $targetPath = ROOT_DIR . $fileName;
            
            if (file_exists($targetPath)) {
                $errors[] = "$fileName: File already exists";
                continue;
            }
            
            if (move_uploaded_file($tmpName, $targetPath)) {
                $uploadedFiles[] = $fileName;
                logAction('FILE_UPLOAD', $fileName);
            } else {
                $errors[] = "$fileName: Upload failed";
            }
        } else {
            $errors[] = $_FILES['files']['name'][$key] . ": Upload error";
        }
    }
    
    $uploadSuccess = !empty($uploadedFiles);
    $uploadMessage = '';
    
    if ($uploadSuccess) {
        $count = count($uploadedFiles);
        $uploadMessage = "$count file" . ($count > 1 ? 's' : '') . " uploaded successfully";
        if (!empty($errors)) {
            $uploadMessage .= " (with " . count($errors) . " error" . (count($errors) > 1 ? 's' : '') . ")";
        }
    } else {
        $uploadMessage = "Upload failed: " . implode(', ', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parker - Upload Files</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .upload-area {
            border: 2px dashed var(--light-accent);
            border-radius: var(--border-radius);
            padding: clamp(30px, 6vw, 50px);
            text-align: center;
            background-color: var(--light);
            transition: all var(--transition-normal) ease;
            cursor: pointer;
            margin-bottom: clamp(20px, 4vw, 30px);
        }
        
        .upload-area:hover,
        .upload-area.drag-active {
            border-color: var(--primary);
            background-color: var(--primary-light);
        }
        
        .upload-icon {
            font-size: clamp(2rem, 6vw, 3rem);
            margin-bottom: 16px;
            color: var(--text-medium);
        }
        
        .upload-text {
            font-size: clamp(1rem, 3.5vw, 1.1rem);
            color: var(--text-medium);
            margin-bottom: 16px;
        }
        
        .upload-subtitle {
            font-size: clamp(0.9rem, 3vw, 1rem);
            color: var(--text-light);
        }
        
        .file-input {
            display: none;
        }
        
        .selected-files {
            margin-top: 20px;
            padding: 16px;
            background-color: var(--light);
            border-radius: var(--border-radius);
        }
        
        .file-preview {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 8px 0;
            border-bottom: 1px solid var(--light-accent);
        }
        
        .file-preview:last-child {
            border-bottom: none;
        }
        
        .upload-controls {
            display: flex;
            gap: 12px;
            justify-content: center;
            margin-top: 20px;
        }
        
        .result-message {
            padding: 16px;
            border-radius: var(--border-radius);
            margin-bottom: 20px;
        }
        
        .result-success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        
        .result-error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
    </style>
</head>
<body>
    <a href="#main-content" class="skip-link">Skip to main content</a>
    
    <div class="container">
        <?php include '../views/components/navbar.php'; ?>
        
        <main id="main-content" role="main">
            <h1>Upload Files</h1>
            
            <?php if (isset($uploadMessage)): ?>
                <div class="result-message <?php echo $uploadSuccess ? 'result-success' : 'result-error'; ?>">
                    <?php echo htmlspecialchars($uploadMessage); ?>
                    <?php if (!empty($errors)): ?>
                        <ul style="margin: 8px 0 0 20px;">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            
            <form method="post" enctype="multipart/form-data" id="uploadForm">
                <div class="upload-area" id="uploadArea">
                    <div class="upload-icon">📁</div>
                    <div class="upload-text">
                        <strong>Drop files here or click to browse</strong>
                    </div>
                    <div class="upload-subtitle">
                        Supported: <?php echo implode(', ', ALLOWED_EXTENSIONS); ?><br>
                        Max size: <?php echo formatFileSize(UPLOAD_MAX_SIZE); ?> per file
                    </div>
                    <input type="file" name="files[]" multiple class="file-input" id="fileInput" 
                           accept=".<?php echo implode(',.', ALLOWED_EXTENSIONS); ?>">
                </div>
                
                <div id="selectedFiles" class="selected-files" style="display: none;">
                    <h3>Selected Files:</h3>
                    <div id="fileList"></div>
                </div>
                
                <div class="upload-controls">
                    <button type="submit" class="btn btn-primary" id="uploadBtn" disabled>
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                            <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                            <path d="M7.646 1.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1-.708.708L8.5 2.707V11.5a.5.5 0 0 1-1 0V2.707L5.354 4.854a.5.5 0 1 1-.708-.708l3-3z"/>
                        </svg>
                        Upload Files
                    </button>
                    <a href="../index.php" class="btn btn-secondary">
                        Cancel
                    </a>
                </div>
            </form>
            
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--light-accent);">
                <h3>Upload Guidelines:</h3>
                <ul>
                    <li>Maximum file size: <?php echo formatFileSize(UPLOAD_MAX_SIZE); ?></li>
                    <li>Allowed file types: <?php echo strtoupper(implode(', ', ALLOWED_EXTENSIONS)); ?></li>
                    <li>Multiple files can be selected at once</li>
                    <li>Files with the same name will be rejected</li>
                    <li>Large files may take longer to upload</li>
                </ul>
            </div>
        </main>
        
        <?php include '../views/components/footer.php'; ?>
    </div>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileInput');
            const selectedFiles = document.getElementById('selectedFiles');
            const fileList = document.getElementById('fileList');
            const uploadBtn = document.getElementById('uploadBtn');
            
            // Click to browse files
            uploadArea.addEventListener('click', () => fileInput.click());
            
            // Drag and drop
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('drag-active');
            });
            
            uploadArea.addEventListener('dragleave', function(e) {
                if (!this.contains(e.relatedTarget)) {
                    this.classList.remove('drag-active');
                }
            });
            
            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('drag-active');
                fileInput.files = e.dataTransfer.files;
                updateFileList();
            });
            
            // File input change
            fileInput.addEventListener('change', updateFileList);
            
            function updateFileList() {
                const files = Array.from(fileInput.files);
                
                if (files.length === 0) {
                    selectedFiles.style.display = 'none';
                    uploadBtn.disabled = true;
                    return;
                }
                
                fileList.innerHTML = '';
                files.forEach(file => {
                    const filePreview = document.createElement('div');
                    filePreview.className = 'file-preview';
                    filePreview.innerHTML = `
                        <span class="file-icon">📄</span>
                        <span class="file-name">${file.name}</span>
                        <span class="file-size">${formatFileSize(file.size)}</span>
                    `;
                    fileList.appendChild(filePreview);
                });
                
                selectedFiles.style.display = 'block';
                uploadBtn.disabled = false;
            }
            
            function formatFileSize(bytes) {
                if (bytes >= 1073741824) {
                    return (bytes / 1073741824).toFixed(2) + ' GB';
                } else if (bytes >= 1048576) {
                    return (bytes / 1048576).toFixed(2) + ' MB';
                } else if (bytes >= 1024) {
                    return (bytes / 1024).toFixed(2) + ' KB';
                } else {
                    return bytes + ' bytes';
                }
            }
        });
    </script>
</body>
</html>
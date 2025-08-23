// File management functionality for Parker Directory

document.addEventListener('DOMContentLoaded', function() {
    initializeDragAndDrop();
    initializeFileUpload();
});

// Drag and drop functionality
function initializeDragAndDrop() {
    let draggedElement = null;

    // File dragging
    document.addEventListener('dragstart', function(e) {
        if (e.target.hasAttribute('draggable')) {
            draggedElement = e.target;
            e.target.style.opacity = '0.5';
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/html', e.target.outerHTML);
        }
    });

    document.addEventListener('dragend', function(e) {
        if (e.target.hasAttribute('draggable')) {
            e.target.style.opacity = '';
            draggedElement = null;
        }
    });

    // Drop target handling
    document.addEventListener('dragover', function(e) {
        if (e.target.closest('.drop-target')) {
            e.preventDefault();
            e.dataTransfer.dropEffect = 'move';
            e.target.closest('.drop-target').classList.add('drag-over');
        }
    });

    document.addEventListener('dragleave', function(e) {
        if (e.target.closest('.drop-target')) {
            e.target.closest('.drop-target').classList.remove('drag-over');
        }
    });

    document.addEventListener('drop', function(e) {
        e.preventDefault();
        const dropTarget = e.target.closest('.drop-target');
        
        if (dropTarget && draggedElement) {
            dropTarget.classList.remove('drag-over');
            
            const sourceFile = draggedElement.dataset.filename;
            const targetFolder = dropTarget.dataset.filename;
            
            moveFile(sourceFile, targetFolder);
        }
    });
}

// File upload functionality
function initializeFileUpload() {
    const uploadArea = document.querySelector('.upload-area');
    if (!uploadArea) return;

    // Drag and drop file upload
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
        
        const files = Array.from(e.dataTransfer.files);
        handleFileUpload(files);
    });

    // File input handling
    const fileInput = document.getElementById('fileInput');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const files = Array.from(this.files);
            handleFileUpload(files);
        });
    }
}

// Move file to folder
function moveFile(filename, targetFolder) {
    showNotification(`Moving ${filename} to ${targetFolder}...`, 'info');
    
    fetch('api/move-file.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            filename: filename,
            targetFolder: targetFolder
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification(`${filename} moved successfully`, 'success');
            // Remove moved file from display or update location
            const fileElement = document.querySelector(`[data-filename="${filename}"]`);
            if (fileElement) {
                fileElement.remove();
            }
        } else {
            showNotification(data.message || 'Failed to move file', 'error');
        }
    })
    .catch(error => {
        showNotification('Error moving file', 'error');
        console.error('Move error:', error);
    });
}

// Handle file uploads
function handleFileUpload(files) {
    if (files.length === 0) return;

    const formData = new FormData();
    files.forEach(file => {
        formData.append('files[]', file);
    });

    // Show upload progress
    const progressContainer = createProgressIndicator(files.length);
    
    fetch('api/upload-file.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        progressContainer.remove();
        
        if (data.success) {
            const successCount = data.uploaded.length;
            const totalCount = files.length;
            
            showNotification(`${successCount}/${totalCount} files uploaded successfully`, 'success');
            
            // Refresh file list or add new files to display
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Upload failed', 'error');
        }
    })
    .catch(error => {
        progressContainer.remove();
        showNotification('Error uploading files', 'error');
        console.error('Upload error:', error);
    });
}

// Create progress indicator
function createProgressIndicator(fileCount) {
    const container = document.createElement('div');
    container.className = 'upload-progress';
    container.innerHTML = `
        <div class="progress-content">
            <div class="progress-icon">📤</div>
            <div class="progress-text">Uploading ${fileCount} file${fileCount > 1 ? 's' : ''}...</div>
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>
    `;
    
    document.body.appendChild(container);
    
    // Animate progress bar
    const progressFill = container.querySelector('.progress-fill');
    let progress = 0;
    const interval = setInterval(() => {
        progress += Math.random() * 20;
        if (progress > 90) progress = 90;
        progressFill.style.width = progress + '%';
    }, 200);
    
    // Clean up interval when container is removed
    const originalRemove = container.remove;
    container.remove = function() {
        clearInterval(interval);
        originalRemove.call(this);
    };
    
    return container;
}

// File operations
function createFolder(folderName) {
    fetch('api/create-folder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ folderName: folderName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('Folder created successfully', 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showNotification(data.message || 'Failed to create folder', 'error');
        }
    })
    .catch(error => {
        showNotification('Error creating folder', 'error');
        console.error('Create folder error:', error);
    });
}

function renameFile(oldName, newName) {
    fetch('api/rename-file.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            oldName: oldName,
            newName: newName
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('File renamed successfully', 'success');
            // Update file name in display
            const fileElement = document.querySelector(`[data-filename="${oldName}"]`);
            if (fileElement) {
                fileElement.dataset.filename = newName;
                const nameElement = fileElement.querySelector('.file-name');
                if (nameElement) nameElement.textContent = newName;
            }
        } else {
            showNotification(data.message || 'Failed to rename file', 'error');
        }
    })
    .catch(error => {
        showNotification('Error renaming file', 'error');
        console.error('Rename error:', error);
    });
}

// Context menu for files
document.addEventListener('contextmenu', function(e) {
    const fileItem = e.target.closest('.file-item, .folder-item');
    if (fileItem) {
        e.preventDefault();
        showContextMenu(e, fileItem);
    }
});

function showContextMenu(event, fileItem) {
    const filename = fileItem.dataset.filename;
    const isFolder = fileItem.dataset.type === 'folder';
    
    // Remove existing context menu
    const existingMenu = document.querySelector('.context-menu');
    if (existingMenu) existingMenu.remove();
    
    const menu = document.createElement('div');
    menu.className = 'context-menu';
    menu.innerHTML = `
        <div class="context-item" data-action="rename">Rename</div>
        ${!isFolder ? '<div class="context-item" data-action="view">View</div>' : ''}
        <div class="context-item" data-action="delete">Delete</div>
    `;
    
    menu.style.left = event.pageX + 'px';
    menu.style.top = event.pageY + 'px';
    
    document.body.appendChild(menu);
    
    // Handle menu clicks
    menu.addEventListener('click', function(e) {
        const action = e.target.dataset.action;
        
        switch (action) {
            case 'rename':
                promptRename(filename);
                break;
            case 'view':
                window.location.href = `file-viewer.php?file=${encodeURIComponent(filename)}`;
                break;
            case 'delete':
                handleDeleteFile({ dataset: { filename } });
                break;
        }
        
        menu.remove();
    });
    
    // Remove menu on click outside
    setTimeout(() => {
        document.addEventListener('click', function removeMenu() {
            menu.remove();
            document.removeEventListener('click', removeMenu);
        });
    }, 10);
}

function promptRename(currentName) {
    const newName = prompt('Enter new name:', currentName);
    if (newName && newName !== currentName) {
        renameFile(currentName, newName);
    }
}
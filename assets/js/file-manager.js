/**
 * Parker Directory - File Manager JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    initDragAndDrop();
});

/**
 * Initialize drag and drop functionality with real backend
 */
function initDragAndDrop() {
    const draggableItems = document.querySelectorAll('[draggable="true"]');
    const dropTargets = document.querySelectorAll('[data-drop-target="true"]');
    
    if (!draggableItems.length || !dropTargets.length) return;
    
    // Set up drag ghost element
    const dragGhost = document.createElement('div');
    dragGhost.className = 'drag-icon';
    document.body.appendChild(dragGhost);
    
    // Track currently dragged item
    let draggedItem = null;
    
    // Add drag event listeners
    draggableItems.forEach(item => {
        item.addEventListener('dragstart', function(e) {
            draggedItem = item;
            
            // Create custom drag image
            const itemName = item.getAttribute('data-filename') || item.getAttribute('data-folder-name');
            dragGhost.innerHTML = `
                <span aria-hidden="true">📄</span>
                <span>${itemName}</span>
            `;
            
            // Set drag ghost image
            e.dataTransfer.setDragImage(dragGhost, 20, 20);
            e.dataTransfer.effectAllowed = 'move';
            
            // Set data for drag and drop
            const itemType = item.getAttribute('data-item-type');
            e.dataTransfer.setData('text/plain', JSON.stringify({
                name: itemName,
                type: itemType
            }));
            
            // Add dragging class
            setTimeout(() => item.classList.add('dragging'), 0);
            
            // Announce to screen readers
            announceToScreenReaders(`Started dragging ${itemType} ${itemName}`);
        });
        
        item.addEventListener('dragend', function() {
            item.classList.remove('dragging');
            draggedItem = null;
        });
    });
    
    // Add drop event listeners
    dropTargets.forEach(target => {
        target.addEventListener('dragover', function(e) {
            e.preventDefault();
            if (!draggedItem || target === draggedItem.parentElement) return;
            
            e.dataTransfer.dropEffect = 'move';
            target.classList.add('drag-over');
        });
        
        target.addEventListener('dragleave', function() {
            target.classList.remove('drag-over');
        });
        
        target.addEventListener('drop', function(e) {
            e.preventDefault();
            target.classList.remove('drag-over');
            
            if (!draggedItem) return;
            
            // Get the target folder name
            const targetFolder = target.getAttribute('data-folder-name');
            
            // Get the dragged item details
            const itemName = draggedItem.getAttribute('data-filename') || draggedItem.getAttribute('data-folder-name');
            const itemType = draggedItem.getAttribute('data-item-type');
            
            if (!itemName || !targetFolder) return;
            
            // Get current folder from URL query parameter
            const urlParams = new URLSearchParams(window.location.search);
            const currentFolder = urlParams.get('folder') || '';
            const currentPath = currentFolder ? currentFolder + '/' : '';
            
            // Define source and destination paths
            const source = currentPath + itemName;
            const destination = targetFolder + '/' + itemName;
            
            // Call the API to move the file
            moveFile(source, destination, itemType);
        });
    });
}

/**
 * Move a file or folder to a new location
 * @param {string} source - Source path
 * @param {string} destination - Destination path
 * @param {string} type - 'file' or 'folder'
 */
function moveFile(source, destination, type) {
    // Show loading notification
    showNotification(`Moving ${type}...`, 'info');
    
    // Send request to backend
    fetch('/parker/api/move_file.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            source: source,
            destination: destination,
            type: type
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success notification
            showNotification(data.message, 'success');
            
            // Remove the moved item from the UI
            const itemName = source.split('/').pop();
            const selector = type === 'file' ? 
                `[data-filename="${itemName}"]` : 
                `[data-folder-name="${itemName}"]`;
            
            const element = document.querySelector(selector);
            if (element) {
                element.remove();
            }
            
            // Announce to screen readers
            announceToScreenReaders(`${type} ${itemName} moved to ${destination.split('/')[0]}`);
        } else {
            // Show error notification
            showNotification(data.message, 'error');
            
            // Announce to screen readers
            announceToScreenReaders(`Failed to move item: ${data.message}`);
        }
    })
    .catch(error => {
        console.error('Move error:', error);
        showNotification('Connection error when moving item', 'error');
        
        // Announce to screen readers
        announceToScreenReaders('Connection error when moving item');
    });
}

/**
 * Announce message to screen readers using ARIA live region
 * @param {string} message - Message to announce
 */
function announceToScreenReaders(message) {
    // Get or create the ARIA live region
    let liveRegion = document.getElementById('aria-live-announcer');
    
    if (!liveRegion) {
        liveRegion = document.createElement('div');
        liveRegion.id = 'aria-live-announcer';
        liveRegion.setAttribute('aria-live', 'polite');
        liveRegion.setAttribute('aria-atomic', 'true');
        liveRegion.className = 'sr-only';
        document.body.appendChild(liveRegion);
    }
    
    // Set the message
    liveRegion.textContent = message;
    
    // Clear after a while
    setTimeout(() => {
        liveRegion.textContent = '';
    }, 3000);
}

/**
 * Initialize multi-selection functionality
 */
function initMultiSelection() {
    const fileItems = document.querySelectorAll('.file-item, .folder-item');
    const directorySection = document.querySelector('.directory-section');
    
    if (!directorySection) return;
    
    // Create selection toolbar
    const toolbar = document.createElement('div');
    toolbar.className = 'selection-toolbar';
    toolbar.innerHTML = `
        <div class="selection-count">0 items selected</div>
        <div class="selection-actions">
            <button class="btn btn-secondary select-all-btn">Select All</button>
            <button class="btn btn-primary" id="downloadSelectedBtn">Download as ZIP</button>
            <button class="btn btn-danger" id="deleteSelectedBtn">Delete</button>
        </div>
    `;
    directorySection.appendChild(toolbar);
    
    // Track selection state
    let selectedItems = [];
    let lastSelectedItem = null;
    const selectCount = toolbar.querySelector('.selection-count');
    
    // Add event listeners to checkboxes
    fileItems.forEach(item => {
        const checkbox = item.querySelector('.item-checkbox');
        const fileLink = item.querySelector('.file-link');
        
        if (checkbox) {
            // Handle checkbox change
            checkbox.addEventListener('change', function(e) {
                e.stopPropagation();
                updateItemSelection(item, checkbox.checked);
            });
            
            // Prevent navigation when clicking checkbox
            checkbox.addEventListener('click', function(e) {
                e.stopPropagation();
            });
        }
        
        if (fileLink) {
            // Handle file link click with modifier keys
            fileLink.addEventListener('click', function(e) {
                // If Ctrl/Cmd key is pressed, toggle selection instead of navigating
                if (e.ctrlKey || e.metaKey) {
                    e.preventDefault();
                    const checkbox = item.querySelector('.item-checkbox');
                    checkbox.checked = !checkbox.checked;
                    updateItemSelection(item, checkbox.checked);
                    lastSelectedItem = checkbox.checked ? item : null;
                } 
                // If Shift key is pressed, select a range
                else if (e.shiftKey && lastSelectedItem) {
                    e.preventDefault();
                    selectRange(lastSelectedItem, item);
                }
            });
        }
    });
    
    // Select All button
    const selectAllBtn = toolbar.querySelector('.select-all-btn');
    if (selectAllBtn) {
        selectAllBtn.addEventListener('click', function() {
            const allSelected = selectedItems.length === fileItems.length;
            
            fileItems.forEach(item => {
                const checkbox = item.querySelector('.item-checkbox');
                checkbox.checked = !allSelected;
                updateItemSelection(item, !allSelected);
            });
            
            // Update button text
            this.textContent = allSelected ? "Select All" : "Deselect All";
        });
    }
    
    // Handle Download ZIP button
    const downloadBtn = document.getElementById('downloadSelectedBtn');
    if (downloadBtn) {
        downloadBtn.addEventListener('click', function() {
            if (selectedItems.length === 0) return;
            
            // Get file paths from selected items
            const filePaths = selectedItems.map(item => {
                return item.getAttribute('data-filename') || item.getAttribute('data-folder-name');
            }).filter(Boolean);
            
            if (filePaths.length === 0) {
                showNotification('No valid files selected', 'error');
                return;
            }
            
            // Show loading notification
            showNotification(`Creating ZIP archive of ${filePaths.length} files...`, 'info');
            
            // Send request to create ZIP
            fetch('/parker/api/create-zip.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ files: filePaths })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(data.message, 'success');
                    
                    // Initiate download
                    window.location.href = data.downloadUrl;
                } else {
                    showNotification(data.message || 'Failed to create ZIP archive', 'error');
                    console.error('ZIP creation errors:', data.errors);
                }
            })
            .catch(error => {
                showNotification('Error creating ZIP archive', 'error');
                console.error('ZIP error:', error);
            });
        });
    }
    
    // Handle Delete button
    const deleteBtn = document.getElementById('deleteSelectedBtn');
    if (deleteBtn) {
        deleteBtn.addEventListener('click', function() {
            if (selectedItems.length === 0) return;
            
            // Confirm deletion
            const itemText = selectedItems.length === 1 ? "item" : "items";
            const message = `Are you sure you want to delete ${selectedItems.length} ${itemText}? This action cannot be undone.`;
            
            if (confirm(message)) {
                // Here you would implement the deletion logic
                // For each item in selectedItems, send a request to your delete API
                showNotification(`Deleting ${selectedItems.length} ${itemText}...`, 'info');
                
                // Example deletion logic (you would implement this)
                /*
                Promise.all(selectedItems.map(item => {
                    const fileName = item.getAttribute('data-filename') || item.getAttribute('data-folder-name');
                    const itemType = item.getAttribute('data-item-type');
                    
                    return fetch(`/parker/api/delete-item.php`, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ name: fileName, type: itemType })
                    }).then(res => res.json());
                }))
                .then(results => {
                    // Handle results
                    showNotification(`Deleted ${selectedItems.length} ${itemText} successfully`, 'success');
                    selectedItems.forEach(item => item.remove());
                    updateSelectionCount(0);
                })
                .catch(error => {
                    showNotification('Error deleting items: ' + error.message, 'error');
                });
                */
            }
        });
    }
    
    // Function to update item selection state
    function updateItemSelection(item, isSelected) {
        if (isSelected) {
            item.classList.add('selected');
            if (!selectedItems.includes(item)) {
                selectedItems.push(item);
            }
        } else {
            item.classList.remove('selected');
            selectedItems = selectedItems.filter(i => i !== item);
        }
        
        updateSelectionCount(selectedItems.length);
        lastSelectedItem = isSelected ? item : null;
    }
    
    // Function to update selection count and toolbar visibility
    function updateSelectionCount(count) {
        selectCount.textContent = `${count} ${count === 1 ? 'item' : 'items'} selected`;
        
        if (count > 0) {
            toolbar.classList.add('active');
            selectAllBtn.textContent = count === fileItems.length ? "Deselect All" : "Select All";
        } else {
            toolbar.classList.remove('active');
            selectAllBtn.textContent = "Select All";
        }
    }
    
    // Function to select a range of items
    function selectRange(fromItem, toItem) {
        const itemsArray = Array.from(fileItems);
        const fromIndex = itemsArray.indexOf(fromItem);
        const toIndex = itemsArray.indexOf(toItem);
        
        if (fromIndex === -1 || toIndex === -1) return;
        
        const start = Math.min(fromIndex, toIndex);
        const end = Math.max(fromIndex, toIndex);
        
        for (let i = start; i <= end; i++) {
            const item = itemsArray[i];
            const checkbox = item.querySelector('.item-checkbox');
            checkbox.checked = true;
            updateItemSelection(item, true);
        }
    }
    
    // Add keyboard shortcuts for selection
    document.addEventListener('keydown', function(e) {
        // Ctrl+A to select all
        if ((e.ctrlKey || e.metaKey) && e.key === 'a') {
            if (document.activeElement.tagName !== 'INPUT' && 
                document.activeElement.tagName !== 'TEXTAREA') {
                e.preventDefault();
                selectAllBtn.click();
            }
        }
        
        // Escape to deselect all
        if (e.key === 'Escape' && selectedItems.length > 0) {
            selectedItems.forEach(item => {
                const checkbox = item.querySelector('.item-checkbox');
                checkbox.checked = false;
                updateItemSelection(item, false);
            });
        }
    });
}

/**
 * Update item selection state
 * @param {HTMLElement} item - The item element
 * @param {boolean} isSelected - Whether the item is selected
 */
function updateItemSelection(item, isSelected) {
    if (isSelected) {
        item.classList.add('selected');
        if (!selectedItems.includes(item)) {
            selectedItems.push(item);
        }
    } else {
        item.classList.remove('selected');
        selectedItems = selectedItems.filter(i => i !== item);
    }
    
    updateSelectionCount(selectedItems.length);
    lastSelectedItem = isSelected ? item : null;
}
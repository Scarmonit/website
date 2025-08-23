// Main JavaScript for Parker Directory

document.addEventListener('DOMContentLoaded', function() {
    initializeSearch();
    initializeSort();
    initializeModal();
    initializeKeyboardShortcuts();
    initializeFileActions();
    initInlineEditing();
    initializeFileTypeFilter();
    initializeGlobalSearch();
    initializeViewMode();
    
    // Initialize event listeners for filter tags
    const activeFilters = document.getElementById('activeFilters');
    if (activeFilters) {
        activeFilters.addEventListener('click', function(e) {
            const removeBtn = e.target.closest('.filter-tag-remove');
            if (!removeBtn) return;
            
            const filterType = removeBtn.getAttribute('data-filter');
            if (filterType === 'file-type') {
                const fileTypeFilter = document.getElementById('fileTypeFilter');
                if (fileTypeFilter) {
                    fileTypeFilter.value = 'all';
                    applyFilters();
                }
            }
        });
    }
});

// Search functionality
function initializeSearch() {
    const searchInput = document.getElementById('fileSearch');
    if (!searchInput) return;

    searchInput.addEventListener('input', debounce(function() {
        applyFilters();
    }, 300));

    // Focus search on load
    setTimeout(() => searchInput.focus(), 500);
}

// Global search functionality
function initializeGlobalSearch() {
    const globalSearchInput = document.getElementById('globalFileSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const searchResultsCount = document.getElementById('searchResults');
    
    if (!globalSearchInput) return;
    
    // Add scroll effect to search container
    window.addEventListener('scroll', function() {
        const searchContainer = document.querySelector('.global-search-container');
        if (searchContainer) {
            if (window.scrollY > 10) {
                searchContainer.classList.add('scrolled');
            } else {
                searchContainer.classList.remove('scrolled');
            }
        }
    });
    
    // Handle search input
    globalSearchInput.addEventListener('input', debounce(function() {
        const searchTerm = this.value.trim();
        
        // Sync with existing search input if present
        const regularSearchInput = document.getElementById('fileSearch');
        if (regularSearchInput) {
            regularSearchInput.value = searchTerm;
        }
        
        // Filter files
        const itemCount = filterFilesGlobally(searchTerm);
        
        // Update results count
        updateSearchResultsCount(searchTerm, itemCount, searchResultsCount);
        
        // Show/hide clear button
        clearSearchBtn.style.display = searchTerm ? 'flex' : 'none';
    }, 200));
    
    // Handle clear button click
    if (clearSearchBtn) {
        clearSearchBtn.addEventListener('click', function() {
            globalSearchInput.value = '';
            globalSearchInput.focus();
            
            // Clear regular search input as well
            const regularSearchInput = document.getElementById('fileSearch');
            if (regularSearchInput) {
                regularSearchInput.value = '';
            }
            
            // Reset filtering
            filterFilesGlobally('');
            
            // Hide clear button and reset count
            this.style.display = 'none';
            if (searchResultsCount) {
                searchResultsCount.textContent = '';
            }
        });
    }
    
    // Set initial focus on the search box
    setTimeout(() => {
        globalSearchInput.focus();
    }, 300);
}

/**
 * Filter files based on the global search term
 * @param {string} searchTerm - The search term
 * @returns {number} - The number of visible items
 */
function filterFilesGlobally(searchTerm) {
    const fileItems = document.querySelectorAll('.file-item, .folder-item');
    let visibleCount = 0;
    
    searchTerm = searchTerm.toLowerCase();
    
    fileItems.forEach(item => {
        const nameElement = item.querySelector('.file-name');
        if (!nameElement) return;
        
        const fileName = nameElement.textContent.toLowerCase();
        const isVisible = searchTerm === '' || fileName.includes(searchTerm);
        
        // Store original text for highlighting purposes
        if (!nameElement.dataset.originalName) {
            nameElement.dataset.originalName = nameElement.textContent;
        }
        
        // Apply highlighting
        if (searchTerm && isVisible) {
            const originalText = nameElement.dataset.originalName;
            const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
            nameElement.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
        } else {
            nameElement.textContent = nameElement.dataset.originalName;
        }
        
        // Update visibility
        item.style.display = isVisible ? '' : 'none';
        
        // Count visible items
        if (isVisible) {
            visibleCount++;
        }
    });
    
    // Update empty state message
    updateEmptySearchMessage(visibleCount, searchTerm);
    
    return visibleCount;
}

/**
 * Update the search results count display
 * @param {string} searchTerm - The search term
 * @param {number} count - The number of visible items
 * @param {HTMLElement} countElement - The element to display the count
 */
function updateSearchResultsCount(searchTerm, count, countElement) {
    if (!countElement) return;
    
    if (!searchTerm) {
        countElement.textContent = '';
        return;
    }
    
    if (count === 0) {
        countElement.textContent = 'No matches found';
    } else {
        countElement.textContent = `${count} item${count !== 1 ? 's' : ''} found`;
    }
}

/**
 * Update empty search message when no items match search
 * @param {number} visibleCount - Number of visible items
 * @param {string} searchTerm - Current search term
 */
function updateEmptySearchMessage(visibleCount, searchTerm) {
    const fileGrid = document.querySelector('.file-grid');
    const folderGrid = document.querySelector('.folder-grid');
    const grids = [fileGrid, folderGrid].filter(grid => grid !== null);
    
    // Create or update empty message
    grids.forEach(grid => {
        let emptyMessage = grid.querySelector('.empty-search-results');
        
        if (visibleCount === 0 && searchTerm) {
            if (!emptyMessage) {
                emptyMessage = document.createElement('li');
                emptyMessage.className = 'empty-search-results';
                emptyMessage.style.cssText = 'text-align: center; padding: 20px; color: #6c757d; font-style: italic; grid-column: 1 / -1;';
                grid.appendChild(emptyMessage);
            } else {
                emptyMessage.style.display = '';
            }
            
            emptyMessage.innerHTML = `No items found matching "${escapeHtml(searchTerm)}"`;
        } else if (emptyMessage) {
            emptyMessage.style.display = 'none';
        }
    });
}

// File filtering
function applyFilters() {
    const searchInput = document.getElementById('fileSearch');
    const fileTypeFilter = document.getElementById('fileTypeFilter');
    const fileItems = document.querySelectorAll('.file-item, .folder-item');
    const activeFilters = document.getElementById('activeFilters');
    
    // Get filter values
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const fileType = fileTypeFilter ? fileTypeFilter.value : 'all';
    
    // Clear active filter tags
    if (activeFilters) {
        activeFilters.innerHTML = '';
    }
    
    // Add active filter tag if filter is applied
    if (fileType !== 'all' && activeFilters) {
        const filterName = fileType === 'folder' ? 'Folders Only' : 
                          fileType.startsWith('.') ? `Type: ${fileType.substring(1).toUpperCase()}` : fileType;
        
        const tagElement = document.createElement('div');
        tagElement.className = 'filter-tag';
        tagElement.innerHTML = `
            ${filterName}
            <span class="filter-tag-remove" data-filter="file-type">×</span>
        `;
        
        // Add click handler to remove tag
        tagElement.querySelector('.filter-tag-remove').addEventListener('click', function() {
            if (fileTypeFilter) {
                fileTypeFilter.value = 'all';
                applyFilters();
            }
        });
        
        activeFilters.appendChild(tagElement);
    }
    
    // Track visible count
    let visibleCount = 0;
    
    // Apply filters to each item
    fileItems.forEach(item => {
        const fileName = item.querySelector('.file-name').textContent.toLowerCase();
        const isFolder = item.classList.contains('folder-item') || item.getAttribute('data-item-type') === 'folder';
        
        // Get file extension from className or data attribute
        let fileExtension = '';
        if (!isFolder) {
            const fileNameParts = fileName.split('.');
            if (fileNameParts.length > 1) {
                fileExtension = '.' + fileNameParts[fileNameParts.length - 1].toLowerCase();
            }
        }
        
        // Check if item matches search term
        const matchesSearch = searchTerm === '' || fileName.includes(searchTerm);
        
        // Check if item matches file type filter
        let matchesFileType = true;
        if (fileType === 'folder') {
            matchesFileType = isFolder;
        } else if (fileType !== 'all') {
            matchesFileType = !isFolder && fileExtension === fileType;
        }
        
        // Determine visibility
        const isVisible = matchesSearch && matchesFileType;
        
        // Update item visibility
        item.style.display = isVisible ? '' : 'none';
        
        // Count visible items
        if (isVisible) {
            visibleCount++;
            
            // Apply search term highlighting
            const nameElement = item.querySelector('.file-name');
            if (nameElement && searchTerm) {
                const originalText = nameElement.getAttribute('data-original-name') || nameElement.textContent;
                nameElement.setAttribute('data-original-name', originalText);
                
                const regex = new RegExp(`(${escapeRegex(searchTerm)})`, 'gi');
                nameElement.innerHTML = originalText.replace(regex, '<mark>$1</mark>');
            } else if (nameElement && nameElement.getAttribute('data-original-name')) {
                nameElement.textContent = nameElement.getAttribute('data-original-name');
            }
        }
    });
    
    // Show empty message if no items are visible
    updateEmptyMessage(visibleCount, searchTerm, fileType);
}

/**
 * Initialize file type filter
 */
function initializeFileTypeFilter() {
    const fileTypeFilter = document.getElementById('fileTypeFilter');
    if (!fileTypeFilter) return;

    fileTypeFilter.addEventListener('change', function() {
        applyFilters();
    });
}

/**
 * Update empty message when no items match filters
 */
function updateEmptyMessage(visibleCount, searchTerm, fileType) {
    // Find or create message element
    let emptyMessage = document.querySelector('.empty-filter-results');
    const fileGrid = document.querySelector('.file-grid') || document.querySelector('.folder-grid');
    
    if (!fileGrid) return;
    
    if (visibleCount === 0) {
        if (!emptyMessage) {
            emptyMessage = document.createElement('li');
            emptyMessage.className = 'empty-filter-results';
            emptyMessage.style.cssText = 'text-align: center; padding: 20px; color: #6c757d; font-style: italic; grid-column: 1 / -1;';
            fileGrid.appendChild(emptyMessage);
        } else {
            emptyMessage.style.display = '';
        }
        
        // Craft appropriate message based on filters
        let message = 'No items found';
        if (searchTerm && fileType !== 'all') {
            const typeLabel = fileType === 'folder' ? 'folders' : `${fileType.substring(1)} files`;
            message = `No ${typeLabel} found matching "${escapeHtml(searchTerm)}"`;
        } else if (searchTerm) {
            message = `No items found matching "${escapeHtml(searchTerm)}"`;
        } else if (fileType !== 'all') {
            const typeLabel = fileType === 'folder' ? 'folders' : `${fileType.substring(1)} files`;
            message = `No ${typeLabel} found in this directory`;
        }
        
        emptyMessage.innerHTML = message;
    } else if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
}

// Sort functionality
function initializeSort() {
    const sortSelect = document.getElementById('fileSort');
    if (!sortSelect) return;

    sortSelect.addEventListener('change', function() {
        sortFiles(this.value);
    });
}

function sortFiles(sortType) {
    const [criteria, direction] = sortType.split('-');
    const fileGrid = document.querySelector('.file-grid');
    if (!fileGrid) return;

    const items = Array.from(fileGrid.querySelectorAll('.file-item, .folder-item'));
    
    items.sort((a, b) => {
        let aValue, bValue;
        
        if (criteria === 'name') {
            aValue = a.querySelector('.file-name').textContent;
            bValue = b.querySelector('.file-name').textContent;
        } else if (criteria === 'type') {
            aValue = a.querySelector('.file-type')?.textContent || '';
            bValue = b.querySelector('.file-type')?.textContent || '';
        }
        
        const comparison = aValue.localeCompare(bValue);
        return direction === 'desc' ? -comparison : comparison;
    });

    // Re-append sorted items
    items.forEach(item => fileGrid.appendChild(item));
}

// Modal functionality
function initializeModal() {
    const modal = document.getElementById('modal');
    if (!modal) return;

    const closeButtons = modal.querySelectorAll('.modal-close, .modal-cancel, .modal-backdrop');
    closeButtons.forEach(btn => {
        btn.addEventListener('click', closeModal);
    });

    // Close on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && modal.style.display === 'flex') {
            closeModal();
        }
    });
}

function showModal(title, content, onConfirm = null) {
    const modal = document.getElementById('modal');
    const modalTitle = modal.querySelector('.modal-title');
    const modalContent = modal.querySelector('.modal-content');
    const confirmBtn = modal.querySelector('.modal-confirm');

    modalTitle.textContent = title;
    modalContent.innerHTML = content;

    if (onConfirm) {
        confirmBtn.style.display = 'block';
        confirmBtn.onclick = function() {
            onConfirm();
            closeModal();
        };
    } else {
        confirmBtn.style.display = 'none';
    }

    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    
    // Focus management
    modal.querySelector('.modal-close').focus();
}

function closeModal() {
    const modal = document.getElementById('modal');
    modal.style.display = 'none';
    modal.setAttribute('aria-hidden', 'true');
}

// File actions
function initializeFileActions() {
    document.addEventListener('click', function(e) {
        if (e.target.closest('.delete-btn')) {
            handleDeleteFile(e.target.closest('.delete-btn'));
        }
    });
}

function handleDeleteFile(button) {
    const filename = button.dataset.filename;
    showModal(
        'Delete File',
        `Are you sure you want to delete <strong>${escapeHtml(filename)}</strong>? This action cannot be undone.`,
        () => deleteFile(filename)
    );
}

function deleteFile(filename) {
    // Implement file deletion via AJAX
    fetch('api/delete-file.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ filename: filename })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showNotification('File deleted successfully', 'success');
            // Remove file from display
            const fileItem = document.querySelector(`[data-filename="${filename}"]`);
            if (fileItem) fileItem.remove();
        } else {
            showNotification(data.message || 'Failed to delete file', 'error');
        }
    })
    .catch(error => {
        showNotification('Error deleting file', 'error');
        console.error('Delete error:', error);
    });
}

// Keyboard shortcuts
function initializeKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + S to focus search
        if ((e.ctrlKey || e.metaKey) && e.key === 's') {
            const searchInput = document.getElementById('fileSearch');
            if (searchInput) {
                searchInput.focus();
                e.preventDefault();
            }
        }
        
        // Alt + H to go home
        if (e.altKey && e.key === 'h') {
            window.location.href = 'index.php';
            e.preventDefault();
        }
    });
}

// Notification system
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    // Add ARIA attributes properly as HTML attributes
    notification.setAttribute('aria-live', 'polite');
    notification.setAttribute('aria-atomic', 'true');
    
    const icons = { success: '✅', error: '❌', info: 'ℹ️' };
    notification.innerHTML = `
        <div class="notification-content">
            <span class="notification-icon">${icons[type]}</span>
            <span class="notification-message">${escapeHtml(message)}</span>
        </div>
    `;

    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => notification.classList.add('show'), 10);

    // Auto remove
    setTimeout(() => {
        notification.classList.remove('show');
        setTimeout(() => notification.remove(), 300);
    }, type === 'error' ? 4000 : 3000);
}

/**
 * Initialize inline editing functionality for file and folder names
 */
function initInlineEditing() {
    // Find all file and folder names
    const fileNames = document.querySelectorAll('.file-name');
    
    fileNames.forEach(nameElement => {
        // Add double-click event listener
        nameElement.addEventListener('dblclick', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Start editing if not already in edit mode
            if (!nameElement.classList.contains('editing')) {
                startEditing(nameElement);
            }
        });
    });
}

/**
 * Start the inline editing process
 * @param {HTMLElement} nameElement - The element containing the filename
 */
function startEditing(nameElement) {
    // Get current name and create input element
    const currentName = nameElement.textContent;
    const fileItem = nameElement.closest('.file-item, .folder-item');
    const isFolder = fileItem.hasAttribute('data-folder-name') || fileItem.classList.contains('folder-item');
    const itemType = isFolder ? 'folder' : 'file';
    
    // Create input field
    const input = document.createElement('input');
    input.type = 'text';
    input.value = currentName;
    input.className = 'inline-edit-input';
    input.dataset.originalValue = currentName;
    
    // Add to DOM
    nameElement.innerHTML = '';
    nameElement.appendChild(input);
    nameElement.classList.add('editing');
    
    // Focus and select all text
    input.focus();
    input.select();
    
    // Handle events
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            finishEditing(nameElement, input, itemType);
        } else if (e.key === 'Escape') {
            cancelEditing(nameElement, input);
        }
    });
    
    input.addEventListener('blur', function() {
        finishEditing(nameElement, input, itemType);
    });
}

/**
 * Finish editing and save changes
 * @param {HTMLElement} nameElement - The name element
 * @param {HTMLInputElement} input - The input field
 * @param {string} itemType - 'file' or 'folder'
 */
function finishEditing(nameElement, input, itemType) {
    // Prevent multiple executions
    if (!nameElement.contains(input)) return;
    
    const newName = input.value.trim();
    const originalName = input.dataset.originalValue;
    const fileItem = nameElement.closest('.file-item, .folder-item');
    
    // Validate input
    if (newName === '') {
        showInlineError(input, 'Name cannot be empty');
        return;
    }
    
    if (newName === originalName) {
        // No change, just restore the original text
        nameElement.textContent = originalName;
        nameElement.classList.remove('editing');
        return;
    }
    
    // Determine current folder path if any
    let currentFolder = '';
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('folder')) {
        currentFolder = urlParams.get('folder') + '/';
    }
    
    // Send to server
    fetch('/parker/api/rename-item.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            oldName: originalName,
            newName: newName,
            type: itemType,
            currentFolder: currentFolder
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Update UI
            nameElement.textContent = newName;
            nameElement.classList.remove('editing');
            
            // Update data attributes
            if (itemType === 'folder') {
                fileItem.setAttribute('data-folder-name', newName);
            } else {
                fileItem.setAttribute('data-filename', newName);
            }
            fileItem.setAttribute('data-item-name', newName.toLowerCase());
            
            // Update link href for files
            if (itemType === 'file') {
                const fileLink = fileItem.querySelector('.file-link');
                if (fileLink) {
                    const href = fileLink.getAttribute('href');
                    const newHref = href.replace(encodeURIComponent(originalName), encodeURIComponent(newName));
                    fileLink.setAttribute('href', newHref);
                }
            }
            
            // Show success notification
            showNotification(`${itemType === 'folder' ? 'Folder' : 'File'} renamed successfully`, 'success');
        } else {
            // Show error and revert
            showInlineError(input, data.message || 'Failed to rename');
            setTimeout(() => {
                nameElement.textContent = originalName;
                nameElement.classList.remove('editing');
            }, 2000);
        }
    })
    .catch(error => {
        console.error('Rename error:', error);
        showInlineError(input, 'Error connecting to server');
        setTimeout(() => {
            nameElement.textContent = originalName;
            nameElement.classList.remove('editing');
        }, 2000);
    });
}

/**
 * Cancel editing and restore original name
 * @param {HTMLElement} nameElement - The name element
 * @param {HTMLInputElement} input - The input field
 */
function cancelEditing(nameElement, input) {
    nameElement.textContent = input.dataset.originalValue;
    nameElement.classList.remove('editing');
}

/**
 * Show error message for inline editing
 * @param {HTMLElement} input - The input element
 * @param {string} message - Error message
 */
function showInlineError(input, message) {
    input.classList.add('error');
    
    // Create or update error tooltip
    let errorTooltip = document.querySelector('.error-tooltip');
    if (!errorTooltip) {
        errorTooltip = document.createElement('div');
        errorTooltip.className = 'error-tooltip';
        document.body.appendChild(errorTooltip);
    }
    
    // Position and show tooltip
    const rect = input.getBoundingClientRect();
    errorTooltip.textContent = message;
    errorTooltip.style.top = `${rect.bottom + window.scrollY + 5}px`;
    errorTooltip.style.left = `${rect.left + window.scrollX}px`;
    errorTooltip.classList.add('show');
    
    // Hide after delay
    setTimeout(() => {
        errorTooltip.classList.remove('show');
        input.classList.remove('error');
    }, 3000);
}

// View mode functionality
function initializeViewMode() {
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const fileGrids = document.querySelectorAll('.file-grid');
    
    if (!gridViewBtn || !listViewBtn || fileGrids.length === 0) return;
    
    // Get saved view mode from localStorage or default to grid
    const savedViewMode = localStorage.getItem('parker_view_mode') || 'grid';
    
    // Apply the saved view mode
    if (savedViewMode === 'list') {
        applyListView();
    } else {
        applyGridView();
    }
    
    // Grid view button click
    gridViewBtn.addEventListener('click', function() {
        applyGridView();
        saveViewMode('grid');
    });
    
    // List view button click
    listViewBtn.addEventListener('click', function() {
        applyListView();
        saveViewMode('list');
    });
    
    /**
     * Apply grid view styling
     */
    function applyGridView() {
        fileGrids.forEach(grid => grid.classList.remove('list-view'));
        gridViewBtn.classList.add('active');
        listViewBtn.classList.remove('active');
        gridViewBtn.setAttribute('aria-pressed', 'true');
        listViewBtn.setAttribute('aria-pressed', 'false');
    }
    
    /**
     * Apply list view styling
     */
    function applyListView() {
        fileGrids.forEach(grid => grid.classList.add('list-view'));
        listViewBtn.classList.add('active');
        gridViewBtn.classList.remove('active');
        listViewBtn.setAttribute('aria-pressed', 'true');
        gridViewBtn.setAttribute('aria-pressed', 'false');
    }
    
    /**
     * Save view mode preference to localStorage
     * @param {string} mode - 'grid' or 'list'
     */
    function saveViewMode(mode) {
        try {
            localStorage.setItem('parker_view_mode', mode);
        } catch (e) {
            console.warn('Could not save view mode preference:', e);
        }
    }
    
    // Add keyboard shortcut for toggling view mode (Alt+V)
    document.addEventListener('keydown', function(e) {
        if (e.altKey && e.key === 'v') {
            e.preventDefault();
            const currentView = localStorage.getItem('parker_view_mode') || 'grid';
            if (currentView === 'grid') {
                applyListView();
                saveViewMode('list');
            } else {
                applyGridView();
                saveViewMode('grid');
            }
        }
    });
}

// Utility functions
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function escapeRegex(string) {
    return string.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}
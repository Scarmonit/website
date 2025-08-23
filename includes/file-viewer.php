// Replace the problematic JavaScript section in file-viewer.php with this:

document.addEventListener('DOMContentLoaded', function() {
    // Basic syntax highlighting for better readability
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

// Notification system
function showNotification(message, type = 'info') {
    const isMobile = window.innerWidth <= 768;
    
    // Color schemes for different notification types
    const colors = {
        success: { bg: '#d4edda', text: '#155724', border: '#c3e6cb' },
        error: { bg: '#f8d7da', text: '#721c24', border: '#f5c6cb' },
        info: { bg: '#e2e3e5', text: '#383d41', border: '#d6d8db' }
    };
    
    const colorScheme = colors[type] || colors.info;
    
    // Create notification element
    const notification = document.createElement('div');
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colorScheme.bg};
        color: ${colorScheme.text};
        border: 1px solid ${colorScheme.border};
        padding: ${isMobile ? '12px 15px' : '14px 20px'};
        border-radius: 8px;
        font-size: ${isMobile ? '13px' : '14px'};
        z-index: 10000;
        word-wrap: break-word;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transform: translateY(-10px);
        opacity: 0;
        transition: transform 0.3s ease, opacity 0.3s ease;
    `;
    
    // Add appropriate icon based on type
    const icon = type === 'success' ? '✅' : type === 'error' ? '❌' : 'ℹ️';
    notification.innerHTML = `<div style="display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 16px;">${icon}</span>
        <span>${message}</span>
    </div>`;
    
    document.body.appendChild(notification);
    
    // Animate in
    setTimeout(() => {
        notification.style.transform = 'translateY(0)';
        notification.style.opacity = '1';
    }, 10);
    
    // Auto remove after delay
    setTimeout(() => {
        notification.style.transform = 'translateY(-10px)';
        notification.style.opacity = '0';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    }, type === 'error' ? 4000 : 3000);
}

// Line number functionality
function addLineNumbers() {
    const codeBlock = document.querySelector('.code-block');
    if (!codeBlock) return;
    
    const lines = codeBlock.textContent.split('\n');
    
    // Create line numbers container
    const lineNumbers = document.createElement('div');
    lineNumbers.className = 'line-numbers';
    
    // Add numbers for each line
    for (let i = 1; i <= lines.length; i++) {
        const lineNumber = document.createElement('div');
        lineNumber.textContent = i;
        lineNumber.className = 'line-number';
        lineNumbers.appendChild(lineNumber);
    }
    
    // Insert line numbers before code
    const fileContentEl = document.querySelector('.file-content');
    if (fileContentEl) {
        fileContentEl.insertBefore(lineNumbers, codeBlock);
        // Add padding to code block to make room for line numbers
        codeBlock.style.paddingLeft = '50px';
    }
}

// Initialize line numbers if we have code
if (document.querySelector('.code-block')) {
    addLineNumbers();
}

// Add keyboard shortcuts for navigation
document.addEventListener('keydown', function(e) {
    // Alt+H or Option+H to go home
    if ((e.altKey || e.metaKey) && e.key === 'h') {
        window.location.href = 'index.php';
        e.preventDefault();
    }
    
    // Alt+B or Option+B to go back
    if ((e.altKey || e.metaKey) && e.key === 'b') {
        if (document.querySelector('.back-button')) {
            window.location.href = 'file-viewer.php';
            e.preventDefault();
        }
    }
    
    // Escape key to go back
    if (e.key === 'Escape' && document.querySelector('.back-button')) {
        window.location.href = 'file-viewer.php';
    }
    
    // Ctrl+S or Cmd+S to search (in file list view)
    if ((e.ctrlKey || e.metaKey) && e.key === 's' && document.getElementById('fileSearch')) {
        document.getElementById('fileSearch').focus();
        e.preventDefault();
    }
});

// Enhance file search with filtering
const searchInput = document.getElementById('fileSearch');
if (searchInput) {
    searchInput.addEventListener('input', function() {
        filterFiles();
    });
    
    // Focus search input on page load
    setTimeout(() => {
        searchInput.focus();
    }, 500);
}

function filterFiles() {
    const searchInput = document.getElementById('fileSearch');
    if (!searchInput) return;
    
    const searchTerm = searchInput.value.toLowerCase();
    const fileItems = document.querySelectorAll('.file-item');
    
    let visibleCount = 0;
    
    fileItems.forEach(item => {
        const fileName = item.querySelector('.file-name').textContent.toLowerCase();
        
        if (fileName.includes(searchTerm)) {
            item.style.display = '';
            visibleCount++;
            
            // Highlight matching text
            const nameElement = item.querySelector('.file-name');
            const originalText = nameElement.textContent;
            if (searchTerm) {
                const regex = new RegExp(`(${searchTerm})`, 'gi');
                nameElement.innerHTML = originalText.replace(regex, '<mark style="background-color: #fffacd; color: #000; padding: 0 2px; border-radius: 2px;">$1</mark>');
            } else {
                nameElement.textContent = originalText;
            }
        } else {
            item.style.display = 'none';
        }
    });
    
    // Show "no results" message if needed
    const fileGrid = document.querySelector('.file-grid');
    let emptyMessage = document.querySelector('.empty-search-results');
    
    if (visibleCount === 0 && searchTerm && fileGrid) {
        if (!emptyMessage) {
            emptyMessage = document.createElement('li');
            emptyMessage.className = 'empty-search-results';
            emptyMessage.style.cssText = 'text-align: center; padding: 20px; color: #6c757d; font-style: italic; grid-column: 1 / -1;';
            emptyMessage.innerHTML = `No files found matching "${searchTerm}"`;
            fileGrid.appendChild(emptyMessage);
        } else {
            emptyMessage.style.display = '';
            emptyMessage.innerHTML = `No files found matching "${searchTerm}"`;
        }
    } else if (emptyMessage) {
        emptyMessage.style.display = 'none';
    }
}
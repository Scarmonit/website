<?php
// Enforce authentication using original system
require_once 'includes/auth.php';
require_once 'includes/file-operations.php';

// Set flag to indicate this is the authenticated file manager page
$is_file_manager_page = true;

// Include page structure
require_once 'views/header.php';
?>

<!-- Main File Manager Layout -->
<div class="file-manager-layout">
    <!-- Upload Section - Positioned at top for better UX -->
    <?php require_once 'views/upload-form.php'; ?>
    
    <!-- File Management Section -->
    <?php require_once 'views/file-manager.php'; ?>
</div>

<?php require_once 'views/footer.php'; ?>
<?php
/**
 * Configuration settings for Parker Directory
 * 
 * This file contains global configuration settings and constants
 * used throughout the application.
 */

// Error reporting settings
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to users
ini_set('log_errors', 1);     // Log errors instead
ini_set('error_log', __DIR__ . '/../logs/error.log'); // Log path

// Application settings
define('APP_NAME', 'Parker Directory');
define('APP_VERSION', '1.0.0');

// File upload settings
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', [
    // Documents
    'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx',
    // Images
    'jpg', 'jpeg', 'png', 'gif', 'svg', 'webp',
    // Web
    'html', 'css', 'js', 'php', 'json', 'xml',
    // Media
    'mp3', 'mp4', 'wav', 'ogg', 'webm',
    // Archives
    'zip', 'rar', 'tar', 'gz'
]);

// Directory settings
define('BASE_PATH', realpath(__DIR__ . '/..'));
define('UPLOAD_DIR', BASE_PATH . '/uploads');

// Ensure upload directory exists
if (!file_exists(UPLOAD_DIR) && !is_dir(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Debug mode (true for development, false for production)
define('DEBUG_MODE', false);

// Timezone settings
date_default_timezone_set('UTC');

// Session settings
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on');
?>
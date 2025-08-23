<?php
/**
 * Authentication handler for Parker Directory
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check for authentication cookie
if (!isset($_COOKIE['parker_authenticated']) || $_COOKIE['parker_authenticated'] !== 'true') {
    // Check if we're already on the login page to avoid redirect loops
    $currentPage = basename($_SERVER['SCRIPT_NAME']);
    if ($currentPage !== 'login.php') {
        header("Location: /parker/login.php");
        exit;
    }
}

// Set security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');

// Function to check if user is authenticated
function isAuthenticated() {
    return isset($_COOKIE['parker_authenticated']) && $_COOKIE['parker_authenticated'] === 'true';
}

// Function to require authentication
function requireAuth() {
    if (!isAuthenticated()) {
        header("Location: /parker/login.php");
        exit;
    }
}
?>
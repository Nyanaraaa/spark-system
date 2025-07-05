<?php
/**
 * Application Bootstrap
 * Initialize all security components and utilities
 */

// Define project root directory
define('PROJECT_ROOT', dirname(__DIR__));

// Load environment variables first
require_once PROJECT_ROOT . '/includes/env_loader.php';

// Include all necessary components using absolute paths
require_once PROJECT_ROOT . '/includes/error_handler.php';
require_once PROJECT_ROOT . '/security/password_security.php';
require_once PROJECT_ROOT . '/security/csrf_protection.php';
require_once PROJECT_ROOT . '/security/session_manager.php';
require_once PROJECT_ROOT . '/security/auth_middleware.php';
require_once PROJECT_ROOT . '/utils/input_validator.php';
require_once PROJECT_ROOT . '/utils/response_helper.php';
require_once PROJECT_ROOT . '/utils/database_helper.php';
require_once PROJECT_ROOT . '/config/database.php';

// Initialize error handling
ErrorHandler::init();

// Initialize session management
SessionManager::init();

// Set timezone
date_default_timezone_set('Asia/Manila');

// Environment configuration
if (!defined('APP_ENV')) {
    define('APP_ENV', $_ENV['APP_ENV'] ?? 'development');
}

if (!defined('APP_DEBUG')) {
    define('APP_DEBUG', APP_ENV === 'development');
}

// Security headers
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('X-XSS-Protection: 1; mode=block');
header('Referrer-Policy: strict-origin-when-cross-origin');

// Disable display of errors in production
if (APP_ENV === 'production') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
} else {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

/**
 * Helper function to safely output data
 */
function escape($data) {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

/**
 * Helper function to check authentication
 */
function isAuthenticated() {
    return SessionManager::isAuthenticated();
}

/**
 * Helper function to get current user
 */
function getCurrentUser() {
    return SessionManager::getCurrentUser();
}

/**
 * Helper function to require authentication
 */
function requireAuth($redirectUrl = 'index.php') {
    SessionManager::requireAuth($redirectUrl);
}

/**
 * Helper function to require specific role
 */
function requireRole($role, $redirectUrl = 'index.php') {
    SessionManager::requireRole($role, $redirectUrl);
}

/**
 * Helper function to log application events
 */
function logEvent($message, $context = []) {
    ErrorHandler::logError($message, $context);
}

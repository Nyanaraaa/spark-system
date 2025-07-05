<?php
/**
 * Session Manager
 * Secure session handling
 */

class SessionManager 
{
    private static $initialized = false;
    
    /**
     * Initialize secure session
     */
    public static function init() 
    {
        if (self::$initialized) {
            return;
        }
        
        // Configure session security
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', 1); // Set to 1 in production with HTTPS
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Regenerate session ID periodically
        self::regenerateSession();
        
        self::$initialized = true;
    }
    
    /**
     * Regenerate session ID for security
     */
    public static function regenerateSession() 
    {
        if (!isset($_SESSION['last_regeneration'])) {
            $_SESSION['last_regeneration'] = time();
        } elseif ($_SESSION['last_regeneration'] < (time() - 7200)) {
            // Regenerate every 2 hours to avoid CSRF token issues during login
            // Store important session data before regeneration
            $userData = $_SESSION['user'] ?? null;
            $csrfTokens = $_SESSION['csrf_tokens'] ?? [];
            $lastRegen = $_SESSION['last_regeneration'];
            
            session_regenerate_id(true);
            
            // Restore important session data after regeneration
            $_SESSION['last_regeneration'] = time();
            if ($userData) {
                $_SESSION['user'] = $userData;
            }
            if (!empty($csrfTokens)) {
                $_SESSION['csrf_tokens'] = $csrfTokens;
            }
        }
    }
    
    /**
     * Check if user is authenticated
     */
    public static function isAuthenticated() 
    {
        self::init();
        return isset($_SESSION['username']) && isset($_SESSION['role']);
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role) 
    {
        self::init();
        return self::isAuthenticated() && $_SESSION['role'] === $role;
    }
    
    /**
     * Get current user data
     */
    public static function getCurrentUser() 
    {
        self::init();
        if (!self::isAuthenticated()) {
            return null;
        }
        
        return [
            'username' => $_SESSION['username'],
            'role' => $_SESSION['role'],
            'account_id' => $_SESSION['account_id'] ?? null,
            'employee_id' => $_SESSION['employee_id'] ?? null,
            'staff_id' => $_SESSION['staff_id'] ?? null,
            'first_name' => $_SESSION['first_name'] ?? null,
            'last_name' => $_SESSION['last_name'] ?? null,
            'email_address' => $_SESSION['email_address'] ?? null
        ];
    }
    
    /**
     * Set user session data
     */
    public static function setUserSession($userData) 
    {
        self::init();
        
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'];
        $_SESSION['account_id'] = $userData['account_id'];
        $_SESSION['employee_id'] = $userData['employee_id'];
        
        if (isset($userData['staff_id'])) {
            $_SESSION['staff_id'] = $userData['staff_id'];
        }
        
        // Store additional user details if provided
        if (isset($userData['first_name'])) {
            $_SESSION['first_name'] = $userData['first_name'];
        }
        if (isset($userData['last_name'])) {
            $_SESSION['last_name'] = $userData['last_name'];
        }
        if (isset($userData['email_address'])) {
            $_SESSION['email_address'] = $userData['email_address'];
        }
        
        // Set login time
        $_SESSION['login_time'] = time();
        
        // Regenerate session ID after login
        session_regenerate_id(true);
    }
    
    /**
     * Destroy user session (logout)
     */
    public static function destroySession() 
    {
        self::init();
        
        // Clear all session data
        $_SESSION = array();
        
        // Delete session cookie
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        
        // Destroy session
        session_destroy();
    }
    
    /**
     * Check session timeout
     */
    public static function checkTimeout($timeoutMinutes = 60) 
    {
        self::init();
        
        if (isset($_SESSION['login_time'])) {
            $timeoutSeconds = $timeoutMinutes * 60;
            if ((time() - $_SESSION['login_time']) > $timeoutSeconds) {
                self::destroySession();
                return false;
            }
        }
        
        return true;
    }
    
    /**
     * Set flash message
     */
    public static function setFlashMessage($message, $type = 'info') 
    {
        self::init();
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    
    /**
     * Get and clear flash message
     */
    public static function getFlashMessage() 
    {
        self::init();
        
        if (isset($_SESSION['flash_message'])) {
            $message = $_SESSION['flash_message'];
            $type = $_SESSION['flash_type'] ?? 'info';
            
            unset($_SESSION['flash_message']);
            unset($_SESSION['flash_type']);
            
            return ['message' => $message, 'type' => $type];
        }
        
        return null;
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth($redirectUrl = '/') 
    {
        if (!self::isAuthenticated()) {
            if (self::isAjaxRequest()) {
                http_response_code(401);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Authentication required']);
                exit;
            } else {
                header("Location: {$redirectUrl}");
                exit;
            }
        }
    }
    
    /**
     * Require specific role
     */
    public static function requireRole($role, $redirectUrl = '/') 
    {
        self::requireAuth($redirectUrl);
        
        if (!self::hasRole($role)) {
            if (self::isAjaxRequest()) {
                http_response_code(403);
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Access denied']);
                exit;
            } else {
                header("Location: {$redirectUrl}");
                exit;
            }
        }
    }
    
    /**
     * Check if request is AJAX
     */
    private static function isAjaxRequest() 
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
}

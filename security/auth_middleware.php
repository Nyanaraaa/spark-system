<?php
/**
 * Authentication Middleware
 * Provides comprehensive authentication and authorization for protected pages
 */

class AuthMiddleware
{
    private static $instance = null;
    private static $config = [
        'login_url' => '/spark/index.php',
        'session_timeout' => 3600, // 1 hour
        'check_ip' => true,
        'csrf_protection' => true,
        'secure_headers' => true
    ];

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Initialize authentication middleware for a page
     */
    public static function init($requiredRole = null, $options = [])
    {
        $middleware = self::getInstance();
        $middleware->process($requiredRole, $options);
    }

    /**
     * Process authentication and authorization
     */
    private function process($requiredRole = null, $options = [])
    {
        // Merge options with defaults
        $config = array_merge(self::$config, $options);
        
        try {
            // Set security headers
            if ($config['secure_headers']) {
                $this->setSecurityHeaders();
            }

            // Initialize session management
            SessionManager::init();

            // Check if user is authenticated
            if (!SessionManager::isAuthenticated()) {
                $this->redirectToLogin('Authentication required');
                return;
            }

            // Check session validity
            if (!$this->isValidSession($config)) {
                SessionManager::destroySession();
                $this->redirectToLogin('Session expired');
                return;
            }

            // Check role authorization
            if ($requiredRole && !$this->hasRequiredRole($requiredRole)) {
                $this->handleUnauthorized();
                return;
            }

            // Update session activity
            $this->updateSessionActivity();

            // Initialize CSRF protection if enabled
            if ($config['csrf_protection']) {
                $this->initCSRFProtection();
            }

        } catch (Exception $e) {
            ErrorHandler::logError('Auth middleware error: ' . $e->getMessage(), [
                'file' => $_SERVER['PHP_SELF'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            $this->redirectToLogin('Authentication error');
        }
    }

    /**
     * Set security headers
     */
    private function setSecurityHeaders()
    {
        // Prevent clickjacking
        header('X-Frame-Options: DENY');
        
        // Prevent MIME type sniffing
        header('X-Content-Type-Options: nosniff');
        
        // Enable XSS protection
        header('X-XSS-Protection: 1; mode=block');
        
        // Referrer policy
        header('Referrer-Policy: strict-origin-when-cross-origin');
        
        // Content Security Policy (basic)
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://code.jquery.com https://cdnjs.cloudflare.com https://unpkg.com; style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net https://fonts.googleapis.com https://cdnjs.cloudflare.com https://cdn.lineicons.com; font-src 'self' data: https://fonts.gstatic.com https://cdn.jsdelivr.net https://cdn.lineicons.com; img-src 'self' data: https:; connect-src 'self';");
    }

    /**
     * Check if current session is valid
     */
    private function isValidSession($config)
    {
        $userData = SessionManager::getCurrentUser();
        
        if (!$userData) {
            return false;
        }

        // Check session timeout
        if (isset($_SESSION['last_activity'])) {
            $timeSinceActivity = time() - $_SESSION['last_activity'];
            if ($timeSinceActivity > $config['session_timeout']) {
                return false;
            }
        }

        // Check IP address consistency (if enabled)
        if ($config['check_ip'] && isset($_SESSION['ip_address'])) {
            $currentIP = $_SERVER['REMOTE_ADDR'] ?? '';
            if ($_SESSION['ip_address'] !== $currentIP) {
                ErrorHandler::logError('IP address mismatch detected', [
                    'session_ip' => $_SESSION['ip_address'],
                    'current_ip' => $currentIP,
                    'user_id' => $userData['account_id'] ?? 'unknown'
                ]);
                return false;
            }
        }

        // Check if account is still active
        if (!$this->isAccountActive($userData['account_id'])) {
            return false;
        }

        return true;
    }

    /**
     * Check if user has required role
     */
    private function hasRequiredRole($requiredRole)
    {
        $userData = SessionManager::getCurrentUser();
        
        if (!$userData || !isset($userData['role'])) {
            return false;
        }

        $userRole = $userData['role'];

        // Define role hierarchy
        $roleHierarchy = [
            'admin' => ['admin', 'supervisor', 'housekeeping_staff'],
            'supervisor' => ['supervisor'],
            'housekeeping_staff' => ['housekeeping_staff']
        ];

        // Check if user role can access required role
        if (isset($roleHierarchy[$userRole])) {
            return in_array($requiredRole, $roleHierarchy[$userRole]);
        }

        return $userRole === $requiredRole;
    }

    /**
     * Check if account is still active in database
     */
    private function isAccountActive($accountId)
    {
        try {
            $db = Database::getInstance();
            $conn = $db->getConnection();

            $stmt = $conn->prepare("SELECT status FROM account WHERE account_id = ?");
            $stmt->bind_param("i", $accountId);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($account = $result->fetch_assoc()) {
                return $account['status'] === 'active';
            }
            
            return false;
        } catch (Exception $e) {
            ErrorHandler::logError('Account status check failed: ' . $e->getMessage(), [
                'account_id' => $accountId
            ]);
            return false;
        }
    }

    /**
     * Update session activity timestamp
     */
    private function updateSessionActivity()
    {
        $_SESSION['last_activity'] = time();
        
        // Store IP address if not already stored
        if (!isset($_SESSION['ip_address'])) {
            $_SESSION['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? '';
        }
    }

    /**
     * Initialize CSRF protection
     */
    private function initCSRFProtection()
    {
        // Generate a token for the page if it doesn't exist
        if (!isset($_SESSION['csrf_tokens'])) {
            CSRFProtection::generateToken();
        }
    }

    /**
     * Handle unauthorized access
     */
    private function handleUnauthorized()
    {
        $userData = SessionManager::getCurrentUser();
        
        ErrorHandler::logError('Unauthorized access attempt', [
            'user_id' => $userData['account_id'] ?? 'unknown',
            'user_role' => $userData['role'] ?? 'unknown',
            'requested_page' => $_SERVER['PHP_SELF'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);

        http_response_code(403);
        
        // Redirect to appropriate dashboard based on user role
        $userRole = $userData['role'] ?? '';
        switch ($userRole) {
            case 'supervisor':
                header('Location: /spark/modules/supervisor/pages/dashboard.php');
                break;
            case 'housekeeping_staff':
                header('Location: /spark/modules/staff/pages/dashboard.php');
                break;
            default:
                $this->redirectToLogin('Access denied');
        }
        exit;
    }

    /**
     * Redirect to login page
     */
    private function redirectToLogin($message = '')
    {
        if ($message) {
            SessionManager::setFlashMessage($message, 'error');
        }
        
        // Clear any existing session data
        SessionManager::destroySession();
        
        header('Location: ' . self::$config['login_url']);
        exit;
    }

    /**
     * Get current user safely
     */
    public static function getCurrentUser()
    {
        return SessionManager::getCurrentUser();
    }

    /**
     * Check if current user has permission
     */
    public static function hasPermission($permission)
    {
        $userData = self::getCurrentUser();
        
        if (!$userData) {
            return false;
        }

        $role = $userData['role'] ?? '';
        
        // Define permissions by role
        $permissions = [
            'supervisor' => [
                'manage_staff',
                'manage_supplies', 
                'manage_schedules',
                'manage_locations',
                'view_reports',
                'approve_requests',
                'evaluate_staff'
            ],
            'housekeeping_staff' => [
                'submit_reports',
                'request_supplies',
                'view_schedule',
                'view_profile'
            ]
        ];

        return isset($permissions[$role]) && in_array($permission, $permissions[$role]);
    }

    /**
     * Require specific permission
     */
    public static function requirePermission($permission)
    {
        if (!self::hasPermission($permission)) {
            $middleware = self::getInstance();
            $middleware->handleUnauthorized();
        }
    }

    /**
     * Generate secure page token for forms
     */
    public static function getPageToken()
    {
        return CSRFProtection::generateToken();
    }

    /**
     * Get CSRF token field for forms
     */
    public static function getCSRFField()
    {
        return CSRFProtection::getTokenField();
    }

    /**
     * Log page access
     */
    public static function logPageAccess($pageName = null)
    {
        $userData = self::getCurrentUser();
        $pageName = $pageName ?: basename($_SERVER['PHP_SELF'] ?? 'unknown');
        
        ErrorHandler::logError('Page access', [
            'page' => $pageName,
            'user_id' => $userData['account_id'] ?? 'unknown',
            'user_role' => $userData['role'] ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ]);
    }

    /**
     * Create page configuration for easy setup
     */
    public static function configurePage($config = [])
    {
        $defaults = [
            'role' => null,
            'permissions' => [],
            'csrf' => true,
            'log_access' => true,
            'secure_headers' => true
        ];

        $config = array_merge($defaults, $config);

        // Initialize auth middleware
        self::init($config['role'], [
            'csrf_protection' => $config['csrf'],
            'secure_headers' => $config['secure_headers']
        ]);

        // Check additional permissions
        foreach ($config['permissions'] as $permission) {
            self::requirePermission($permission);
        }

        // Log page access
        if ($config['log_access']) {
            self::logPageAccess();
        }

        return self::getCurrentUser();
    }
}

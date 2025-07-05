<?php
/**
 * CSRF Protection
 * Protects against Cross-Site Request Forgery attacks
 */

class CSRFProtection 
{
    private static $tokenName = 'csrf_token';
    private static $sessionKey = 'csrf_tokens';
    
    /**
     * Generate a new CSRF token
     */
    public static function generateToken() 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $token = bin2hex(random_bytes(32));
        
        // Store token in session (keep only last 5 tokens for multiple tabs)
        if (!isset($_SESSION[self::$sessionKey])) {
            $_SESSION[self::$sessionKey] = [];
        }
        
        $_SESSION[self::$sessionKey][] = $token;
        
        // Keep only the last 5 tokens
        if (count($_SESSION[self::$sessionKey]) > 5) {
            array_shift($_SESSION[self::$sessionKey]);
        }
        
        return $token;
    }
    
    /**
     * Verify CSRF token
     */
    public static function verifyToken($token) 
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION[self::$sessionKey]) || !is_array($_SESSION[self::$sessionKey])) {
            return false;
        }
        
        return in_array($token, $_SESSION[self::$sessionKey], true);
    }
    
    /**
     * Get HTML input field for CSRF token
     */
    public static function getTokenField() 
    {
        $token = self::generateToken();
        return '<input type="hidden" name="' . self::$tokenName . '" value="' . htmlspecialchars($token) . '">';
    }
    
    /**
     * Validate request has valid CSRF token
     */
    public static function validateRequest() 
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $token = $_POST[self::$tokenName] ?? '';
            
            if (!self::verifyToken($token)) {
                http_response_code(403);
                if (self::isAjaxRequest()) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'message' => 'Invalid security token. Please refresh the page and try again.'
                    ]);
                } else {
                    echo 'Invalid security token. Please refresh the page and try again.';
                }
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
    
    /**
     * Get token name for forms
     */
    public static function getTokenName() 
    {
        return self::$tokenName;
    }
}

<?php
/**
 * Response Helper
 * Standardizes API responses
 */

class ResponseHelper 
{
    /**
     * Send success response
     */
    public static function success($data = null, $message = 'Success', $code = 200) 
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => true,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Send error response
     */
    public static function error($message = 'An error occurred', $code = 400, $details = null) 
    {
        http_response_code($code);
        header('Content-Type: application/json');
        
        $response = [
            'success' => false,
            'message' => $message,
            'timestamp' => date('Y-m-d H:i:s')
        ];
        
        if ($details !== null) {
            $response['details'] = $details;
        }
        
        // Log error for debugging
        error_log("API Error ({$code}): {$message}" . ($details ? ' - Details: ' . json_encode($details) : ''));
        
        echo json_encode($response);
        exit;
    }
    
    /**
     * Send validation error response
     */
    public static function validationError($errors, $message = 'Validation failed') 
    {
        self::error($message, 422, ['validation_errors' => $errors]);
    }
    
    /**
     * Send unauthorized response
     */
    public static function unauthorized($message = 'Unauthorized access') 
    {
        self::error($message, 401);
    }
    
    /**
     * Send forbidden response
     */
    public static function forbidden($message = 'Access forbidden') 
    {
        self::error($message, 403);
    }
    
    /**
     * Send not found response
     */
    public static function notFound($message = 'Resource not found') 
    {
        self::error($message, 404);
    }
    
    /**
     * Send server error response
     */
    public static function serverError($message = 'Internal server error') 
    {
        self::error($message, 500);
    }
    
    /**
     * Redirect with message
     */
    public static function redirect($url, $message = null, $type = 'info') 
    {
        if ($message) {
            $_SESSION['flash_message'] = $message;
            $_SESSION['flash_type'] = $type;
        }
        
        header("Location: {$url}");
        exit;
    }
    
    /**
     * Check if request is AJAX
     */
    public static function isAjaxRequest() 
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }
    
    /**
     * Handle response based on request type
     */
    public static function handleResponse($isSuccess, $message, $data = null, $redirectUrl = null) 
    {
        if (self::isAjaxRequest()) {
            if ($isSuccess) {
                $response = ['success' => true, 'message' => $message];
                if ($data !== null) $response['data'] = $data;
                if ($redirectUrl !== null) $response['redirect'] = $redirectUrl;
                self::success($response['data'] ?? null, $message);
            } else {
                self::error($message);
            }
        } else {
            // Regular HTTP request
            if ($redirectUrl) {
                self::redirect($redirectUrl, $message, $isSuccess ? 'success' : 'error');
            } else {
                // Fallback to previous page
                $referer = $_SERVER['HTTP_REFERER'] ?? '/';
                self::redirect($referer, $message, $isSuccess ? 'success' : 'error');
            }
        }
    }
}

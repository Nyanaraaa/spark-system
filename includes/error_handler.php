<?php
/**
 * Centralized Error Handler
 * Handles application errors and logging
 */

class ErrorHandler 
{
    private static $logFile = 'logs/error.log';
    
    public static function init() 
    {
        // Set custom error handler
        set_error_handler([self::class, 'handleError']);
        set_exception_handler([self::class, 'handleException']);
        
        // Ensure logs directory exists
        self::ensureLogDirectory();
    }
    
    public static function handleError($severity, $message, $file, $line) 
    {
        $errorTypes = [
            E_ERROR => 'ERROR',
            E_WARNING => 'WARNING', 
            E_NOTICE => 'NOTICE',
            E_USER_ERROR => 'USER_ERROR',
            E_USER_WARNING => 'USER_WARNING',
            E_USER_NOTICE => 'USER_NOTICE'
        ];
        
        $errorType = $errorTypes[$severity] ?? 'UNKNOWN';
        $timestamp = date('Y-m-d H:i:s');
        
        $logMessage = "[{$timestamp}] {$errorType}: {$message} in {$file} on line {$line}\n";
        
        self::writeLog($logMessage);
        
        // Don't execute PHP internal error handler
        return true;
    }
    
    public static function handleException($exception) 
    {
        $timestamp = date('Y-m-d H:i:s');
        $message = $exception->getMessage();
        $file = $exception->getFile();
        $line = $exception->getLine();
        $trace = $exception->getTraceAsString();
        
        $logMessage = "[{$timestamp}] EXCEPTION: {$message} in {$file} on line {$line}\nStack trace:\n{$trace}\n\n";
        
        self::writeLog($logMessage);
        
        // Show user-friendly error page in production
        if (!self::isDevelopment()) {
            self::showErrorPage();
        }
    }
    
    public static function logError($message, $context = []) 
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' Context: ' . json_encode($context) : '';
        $logMessage = "[{$timestamp}] APPLICATION_ERROR: {$message}{$contextStr}\n";
        
        self::writeLog($logMessage);
    }
    
    private static function writeLog($message) 
    {
        file_put_contents(self::$logFile, $message, FILE_APPEND | LOCK_EX);
    }
    
    private static function ensureLogDirectory() 
    {
        $logDir = dirname(self::$logFile);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    private static function isDevelopment() 
    {
        return $_ENV['APP_ENV'] ?? 'development' === 'development';
    }
    
    private static function showErrorPage() 
    {
        http_response_code(500);
        if (headers_sent()) return;
        
        echo json_encode([
            'success' => false,
            'message' => 'An internal server error occurred. Please try again later.',
            'error_id' => uniqid('err_')
        ]);
        exit;
    }
}

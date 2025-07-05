<?php
/**
 * Comprehensive Logging System
 * Handles application logging, error tracking, and audit trails
 */

class Logger
{
    // Log levels
    const EMERGENCY = 0; // System is unusable
    const ALERT = 1;     // Action must be taken immediately
    const CRITICAL = 2;  // Critical conditions
    const ERROR = 3;     // Error conditions
    const WARNING = 4;   // Warning conditions
    const NOTICE = 5;    // Normal but significant condition
    const INFO = 6;      // Informational messages
    const DEBUG = 7;     // Debug-level messages
    
    // Log categories
    const CATEGORY_SECURITY = 'security';
    const CATEGORY_AUTH = 'authentication';
    const CATEGORY_DATABASE = 'database';
    const CATEGORY_APPLICATION = 'application';
    const CATEGORY_PERFORMANCE = 'performance';
    const CATEGORY_API = 'api';
    const CATEGORY_AUDIT = 'audit';
    const CATEGORY_ERROR = 'error';
    
    private static $instance = null;
    private $logPath;
    private $maxFileSize;
    private $maxFiles;
    private $db;
    private $config;
    
    private $levelNames = [
        self::EMERGENCY => 'EMERGENCY',
        self::ALERT => 'ALERT',
        self::CRITICAL => 'CRITICAL',
        self::ERROR => 'ERROR',
        self::WARNING => 'WARNING',
        self::NOTICE => 'NOTICE',
        self::INFO => 'INFO',
        self::DEBUG => 'DEBUG'
    ];
    
    private function __construct()
    {
        $this->logPath = dirname(__DIR__) . '/logs';
        $this->maxFileSize = 10 * 1024 * 1024; // 10MB
        $this->maxFiles = 10;
        $this->db = Database::getInstance();
        
        $this->config = [
            'log_to_file' => true,
            'log_to_database' => true,
            'log_level' => self::DEBUG,
            'include_stack_trace' => true,
            'log_performance' => true,
            'log_sql_queries' => false, // Set to true for debugging
            'anonymize_sensitive_data' => true
        ];
        
        $this->createLogDirectory();
    }
    
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Log a message
     */
    public function log($level, $message, $category = self::CATEGORY_APPLICATION, $context = [])
    {
        if ($level > $this->config['log_level']) {
            return;
        }
        
        $logEntry = $this->formatLogEntry($level, $message, $category, $context);
        
        // Log to file
        if ($this->config['log_to_file']) {
            $this->logToFile($logEntry, $category);
        }
        
        // Log to database
        if ($this->config['log_to_database']) {
            $this->logToDatabase($level, $message, $category, $context);
        }
        
        // Send to monitoring systems for critical errors
        if ($level <= self::CRITICAL) {
            $this->sendToMonitoring($logEntry);
        }
    }
    
    /**
     * Convenience methods for different log levels
     */
    public function emergency($message, $category = self::CATEGORY_ERROR, $context = [])
    {
        $this->log(self::EMERGENCY, $message, $category, $context);
    }
    
    public function alert($message, $category = self::CATEGORY_ERROR, $context = [])
    {
        $this->log(self::ALERT, $message, $category, $context);
    }
    
    public function critical($message, $category = self::CATEGORY_ERROR, $context = [])
    {
        $this->log(self::CRITICAL, $message, $category, $context);
    }
    
    public function error($message, $category = self::CATEGORY_ERROR, $context = [])
    {
        $this->log(self::ERROR, $message, $category, $context);
    }
    
    public function warning($message, $category = self::CATEGORY_APPLICATION, $context = [])
    {
        $this->log(self::WARNING, $message, $category, $context);
    }
    
    public function notice($message, $category = self::CATEGORY_APPLICATION, $context = [])
    {
        $this->log(self::NOTICE, $message, $category, $context);
    }
    
    public function info($message, $category = self::CATEGORY_APPLICATION, $context = [])
    {
        $this->log(self::INFO, $message, $category, $context);
    }
    
    public function debug($message, $category = self::CATEGORY_APPLICATION, $context = [])
    {
        $this->log(self::DEBUG, $message, $category, $context);
    }
    
    /**
     * Security-specific logging methods
     */
    public function logLogin($username, $success, $ip = null, $userAgent = null)
    {
        $context = [
            'username' => $username,
            'success' => $success,
            'ip_address' => $ip ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $userAgent ?: $_SERVER['HTTP_USER_AGENT'] ?? 'unknown',
            'timestamp' => time()
        ];
        
        $message = $success ? "Successful login for user: $username" : "Failed login attempt for user: $username";
        $level = $success ? self::INFO : self::WARNING;
        
        $this->log($level, $message, self::CATEGORY_AUTH, $context);
    }
    
    public function logLogout($username, $ip = null)
    {
        $context = [
            'username' => $username,
            'ip_address' => $ip ?: $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'timestamp' => time()
        ];
        
        $this->log(self::INFO, "User logged out: $username", self::CATEGORY_AUTH, $context);
    }
    
    public function logSecurityEvent($event, $severity = self::WARNING, $context = [])
    {
        $context['security_event'] = $event;
        $context['ip_address'] = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $context['user_agent'] = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
        
        $this->log($severity, "Security event: $event", self::CATEGORY_SECURITY, $context);
    }
    
    public function logDatabaseQuery($query, $executionTime = null, $params = [])
    {
        if (!$this->config['log_sql_queries']) {
            return;
        }
        
        $context = [
            'query' => $this->sanitizeQuery($query),
            'execution_time' => $executionTime,
            'parameters' => $this->sanitizeParams($params)
        ];
        
        $this->log(self::DEBUG, "Database query executed", self::CATEGORY_DATABASE, $context);
    }
    
    public function logAPIRequest($endpoint, $method, $responseCode, $executionTime = null)
    {
        $context = [
            'endpoint' => $endpoint,
            'method' => $method,
            'response_code' => $responseCode,
            'execution_time' => $executionTime,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        ];
        
        $message = "API request: $method $endpoint - $responseCode";
        $level = $responseCode >= 400 ? self::ERROR : self::INFO;
        
        $this->log($level, $message, self::CATEGORY_API, $context);
    }
    
    public function logPerformance($operation, $executionTime, $memoryUsage = null)
    {
        if (!$this->config['log_performance']) {
            return;
        }
        
        $context = [
            'operation' => $operation,
            'execution_time' => $executionTime,
            'memory_usage' => $memoryUsage ?: memory_get_usage(true),
            'peak_memory' => memory_get_peak_usage(true)
        ];
        
        $level = $executionTime > 5.0 ? self::WARNING : self::INFO;
        $this->log($level, "Performance: $operation", self::CATEGORY_PERFORMANCE, $context);
    }
    
    /**
     * Format log entry for file output
     */
    private function formatLogEntry($level, $message, $category, $context)
    {
        $timestamp = date('Y-m-d H:i:s');
        $levelName = $this->levelNames[$level];
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userId = $_SESSION['user_id'] ?? 'anonymous';
        
        $entry = [
            'timestamp' => $timestamp,
            'level' => $levelName,
            'category' => $category,
            'message' => $message,
            'user_id' => $userId,
            'ip_address' => $ip,
            'context' => $context
        ];
        
        if ($this->config['include_stack_trace'] && $level <= self::ERROR) {
            $entry['stack_trace'] = $this->getStackTrace();
        }
        
        return $entry;
    }
    
    /**
     * Log to file
     */
    private function logToFile($logEntry, $category)
    {
        $filename = $this->getLogFilename($category);
        $logLine = json_encode($logEntry) . "\n";
        
        // Check file size and rotate if necessary
        if (file_exists($filename) && filesize($filename) > $this->maxFileSize) {
            $this->rotateLogFile($filename);
        }
        
        file_put_contents($filename, $logLine, FILE_APPEND | LOCK_EX);
    }
    
    /**
     * Log to database
     */
    private function logToDatabase($level, $message, $category, $context)
    {
        try {
            $conn = $this->db->getConnection();
            
            // Check if we have the application_logs table, if not use audit_logs
            $stmt = $conn->prepare("
                INSERT INTO application_logs (level, message, category, context, timestamp, user_id, ip_address, user_agent) 
                VALUES (?, ?, ?, ?, NOW(), ?, ?, ?)
            ");
            
            if (!$stmt) {
                // Fallback to audit_logs table
                $stmt = $conn->prepare("
                    INSERT INTO audit_logs (account_id, action, resource, details, ip_address, user_agent) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                if ($stmt) {
                    $accountId = $_SESSION['account_id'] ?? null;
                    $action = $this->levelNames[$level] . '_' . strtoupper($category);
                    $resource = $category;
                    $details = json_encode([
                        'message' => $message,
                        'context' => $context,
                        'level' => $this->levelNames[$level]
                    ]);
                    $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                    
                    $stmt->bind_param("isssss", $accountId, $action, $resource, $details, $ipAddress, $userAgent);
                    $stmt->execute();
                    $stmt->close();
                }
            } else {
                // Use application_logs table
                $userId = $_SESSION['user_id'] ?? $_SESSION['account_id'] ?? null;
                $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
                $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';
                $contextJson = json_encode($context);
                
                $stmt->bind_param("isssiss", $level, $message, $category, $contextJson, $userId, $ipAddress, $userAgent);
                $stmt->execute();
                $stmt->close();
            }
            
        } catch (Exception $e) {
            // Fallback to file logging if database fails - don't throw exception
            error_log("Failed to log to database: " . $e->getMessage());
            
            // Try to log to file as fallback
            try {
                $logEntry = [
                    'timestamp' => date('Y-m-d H:i:s'),
                    'level' => $this->levelNames[$level] ?? 'UNKNOWN',
                    'message' => $message,
                    'category' => $category,
                    'context' => $context,
                    'db_error' => $e->getMessage()
                ];
                $this->logToFile($logEntry, 'error');
            } catch (Exception $fileError) {
                // Last resort - just log to PHP error log
                error_log("Complete logging failure: DB=" . $e->getMessage() . ", File=" . $fileError->getMessage());
            }
        }
    }
    
    /**
     * Create log directory structure
     */
    private function createLogDirectory()
    {
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
        
        // Create subdirectories for different log types
        $subdirs = ['security', 'application', 'database', 'api', 'performance', 'error'];
        foreach ($subdirs as $subdir) {
            $path = $this->logPath . '/' . $subdir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
        
        // Create .htaccess to protect log files
        $htaccess = $this->logPath . '/.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Order Deny,Allow\nDeny from all\n");
        }
    }
    
    /**
     * Get log filename for category
     */
    private function getLogFilename($category)
    {
        $date = date('Y-m-d');
        return $this->logPath . '/' . $category . '/' . $category . '_' . $date . '.log';
    }
    
    /**
     * Rotate log file when it gets too large
     */
    private function rotateLogFile($filename)
    {
        $dir = dirname($filename);
        $basename = basename($filename, '.log');
        
        // Remove oldest file if we have too many
        for ($i = $this->maxFiles; $i > 1; $i--) {
            $oldFile = $dir . '/' . $basename . '.' . $i . '.log';
            if (file_exists($oldFile)) {
                unlink($oldFile);
            }
        }
        
        // Rotate existing files
        for ($i = $this->maxFiles - 1; $i >= 1; $i--) {
            $currentFile = $i === 1 ? $filename : $dir . '/' . $basename . '.' . $i . '.log';
            $nextFile = $dir . '/' . $basename . '.' . ($i + 1) . '.log';
            
            if (file_exists($currentFile)) {
                rename($currentFile, $nextFile);
            }
        }
    }
    
    /**
     * Get stack trace
     */
    private function getStackTrace()
    {
        $trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $filteredTrace = [];
        
        foreach ($trace as $frame) {
            if (isset($frame['file']) && strpos($frame['file'], __FILE__) === false) {
                $filteredTrace[] = [
                    'file' => $frame['file'] ?? 'unknown',
                    'line' => $frame['line'] ?? 0,
                    'function' => $frame['function'] ?? 'unknown',
                    'class' => $frame['class'] ?? null
                ];
            }
        }
        
        return $filteredTrace;
    }
    
    /**
     * Sanitize database query for logging
     */
    private function sanitizeQuery($query)
    {
        if (!$this->config['anonymize_sensitive_data']) {
            return $query;
        }
        
        // Remove or mask sensitive data patterns
        $patterns = [
            '/password\s*=\s*[\'"][^\'"]+[\'"]/' => "password = '[MASKED]'",
            '/SET\s+password\s*=\s*[\'"][^\'"]+[\'"]/' => "SET password = '[MASKED]'",
            '/VALUES\s*\([^)]*password[^)]*\)/' => 'VALUES ([MASKED])'
        ];
        
        return preg_replace(array_keys($patterns), array_values($patterns), $query);
    }
    
    /**
     * Sanitize parameters for logging
     */
    private function sanitizeParams($params)
    {
        if (!$this->config['anonymize_sensitive_data']) {
            return $params;
        }
        
        $sensitiveKeys = ['password', 'passwd', 'pwd', 'token', 'secret', 'key'];
        $sanitized = [];
        
        foreach ($params as $key => $value) {
            if (in_array(strtolower($key), $sensitiveKeys)) {
                $sanitized[$key] = '[MASKED]';
            } else {
                $sanitized[$key] = $value;
            }
        }
        
        return $sanitized;
    }
    
    /**
     * Send critical errors to monitoring systems
     */
    private function sendToMonitoring($logEntry)
    {
        // This could be expanded to send to external monitoring services
        // like Sentry, New Relic, etc.
        
        // For now, just send email for critical errors
        $this->sendEmailAlert($logEntry);
    }
    
    /**
     * Send email alert for critical errors
     */
    private function sendEmailAlert($logEntry)
    {
        // Basic email alert - could be enhanced with proper email templating
        $subject = "SPARK Critical Error Alert - " . $logEntry['level'];
        $message = "Critical error occurred in SPARK system:\n\n";
        $message .= "Time: " . $logEntry['timestamp'] . "\n";
        $message .= "Level: " . $logEntry['level'] . "\n";
        $message .= "Category: " . $logEntry['category'] . "\n";
        $message .= "Message: " . $logEntry['message'] . "\n";
        $message .= "User: " . $logEntry['user_id'] . "\n";
        $message .= "IP: " . $logEntry['ip_address'] . "\n";
        
        if (isset($logEntry['context'])) {
            $message .= "Context: " . json_encode($logEntry['context'], JSON_PRETTY_PRINT) . "\n";
        }
        
        // Would implement actual email sending here
        error_log("CRITICAL ERROR: " . $message);
    }
    
    /**
     * Get log statistics
     */
    public function getLogStats($category = null, $level = null, $timeframe = '24 hours')
    {
        try {
            $conn = $this->db->getConnection();
            
            $sql = "SELECT 
                        COUNT(*) as total_logs,
                        COUNT(CASE WHEN details LIKE '%ERROR%' THEN 1 END) as error_count,
                        COUNT(CASE WHEN details LIKE '%WARNING%' THEN 1 END) as warning_count,
                        COUNT(CASE WHEN details LIKE '%INFO%' THEN 1 END) as info_count
                    FROM audit_logs 
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL ? HOUR)";
            
            $params = [is_numeric($timeframe) ? $timeframe : 24];
            
            if ($category) {
                $sql .= " AND resource = ?";
                $params[] = $category;
            }
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('s', count($params)), ...$params);
            $stmt->execute();
            
            return $stmt->get_result()->fetch_assoc();
            
        } catch (Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }
    
    /**
     * Clear old logs
     */
    public function clearOldLogs($days = 30)
    {
        try {
            // Clear database logs
            $conn = $this->db->getConnection();
            $stmt = $conn->prepare("DELETE FROM audit_logs WHERE created_at < DATE_SUB(NOW(), INTERVAL ? DAY)");
            $stmt->bind_param("i", $days);
            $stmt->execute();
            
            // Clear file logs
            $this->clearOldLogFiles($days);
            
            return true;
            
        } catch (Exception $e) {
            $this->error("Failed to clear old logs: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Clear old log files
     */
    private function clearOldLogFiles($days)
    {
        $cutoffTime = time() - ($days * 24 * 60 * 60);
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->logPath)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'log') {
                if ($file->getMTime() < $cutoffTime) {
                    unlink($file->getPathname());
                }
            }
        }
    }
}

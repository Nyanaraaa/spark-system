<?php
/**
 * Secure Database Connection
 * Uses environment configuration and proper error handling
 */

// Include MySQLi compatibility wrapper
require_once dirname(__DIR__) . '/utils/mysqli_compatibility.php';

class Database 
{
    private static $instance = null;
    private $connection;
    private $compatibilityWrapper;
    
    private function __construct() 
    {
        $this->connect();
    }
    
    /**
     * Get singleton database instance
     * @return Database
     */
    public static function getInstance() 
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Get database connection (MySQLi compatible)
     * @return MySQLiCompatibility
     */
    public function getConnection() 
    {
        if ($this->compatibilityWrapper === null) {
            throw new Exception('Database connection not initialized');
        }
        return $this->compatibilityWrapper;
    }
    
    /**
     * Get raw PDO connection
     * @return PDO
     */
    public function getPDO() 
    {
        return $this->connection;
    }
    
    /**
     * Establish database connection
     */
    private function connect() 
    {
        try {
            // Load environment variables if .env file exists
            $this->loadEnvironment();
            
            // Check if PDO extension is available
            if (!extension_loaded('pdo') || !extension_loaded('pdo_mysql')) {
                throw new Exception('PDO MySQL extension is not loaded');
            }
            
            // Database configuration with fallbacks
            $servername = $_ENV['DB_HOST'] ?? 'localhost';
            $username = $_ENV['DB_USERNAME'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $dbname = $_ENV['DB_NAME'] ?? 'spark_system';
            $port = $_ENV['DB_PORT'] ?? 3306;
            
            // Create DSN
            $dsn = "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4";
            
            // PDO options
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
            ];
            
            $this->connection = new PDO($dsn, $username, $password, $options);
            
            // Create MySQLi compatibility wrapper
            $this->compatibilityWrapper = new MySQLiCompatibility($this->connection);
            
            // Set timezone
            $timezone = $_ENV['APP_TIMEZONE'] ?? 'Asia/Manila';
            $this->connection->exec("SET time_zone = '+08:00'");
            
            // Set SQL mode for better data integrity
            $this->connection->exec("SET sql_mode = 'STRICT_TRANS_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'");
            
        } catch (Exception $e) {
            // Don't expose database details in production
            if (defined('APP_ENV') && APP_ENV === 'production') {
                die('Database connection failed. Please contact system administrator.');
            } else {
                die('Database connection failed: ' . $e->getMessage());
            }
        }
    }
    
    /**
     * Load environment variables from .env file
     */
    private function loadEnvironment() 
    {
        $envFile = dirname(__DIR__) . '/.env';
        
        if (file_exists($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            
            foreach ($lines as $line) {
                if (strpos(trim($line), '#') === 0) {
                    continue; // Skip comments
                }
                
                if (strpos($line, '=') === false) {
                    continue; // Skip lines without =
                }
                
                list($name, $value) = explode('=', $line, 2);
                $name = trim($name);
                $value = trim($value);
                
                if (!array_key_exists($name, $_ENV)) {
                    $_ENV[$name] = $value;
                }
            }
        }
    }
    
    /**
     * Prepare statement with error handling
     */
    public function prepare($sql) 
    {
        try {
            $stmt = $this->compatibilityWrapper->prepare($sql);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $this->compatibilityWrapper->error);
            }
            return $stmt;
        } catch (Exception $e) {
            ErrorHandler::logError('SQL prepare failed: ' . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Execute query with error handling
     */
    public function query($sql) 
    {
        try {
            $result = $this->compatibilityWrapper->query($sql);
            if (!$result) {
                throw new Exception('Query failed: ' . $this->compatibilityWrapper->error);
            }
            return $result;
        } catch (Exception $e) {
            ErrorHandler::logError('SQL query failed: ' . $e->getMessage(), ['sql' => $sql]);
            throw $e;
        }
    }
    
    /**
     * Get last insert ID
     */
    public function getLastInsertId() 
    {
        return $this->compatibilityWrapper->insert_id();
    }
    
    /**
     * Start transaction
     */
    public function beginTransaction() 
    {
        return $this->compatibilityWrapper->begin_transaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() 
    {
        return $this->compatibilityWrapper->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() 
    {
        return $this->compatibilityWrapper->rollback();
    }
    
    /**
     * Close connection
     */
    public function close() 
    {
        if ($this->compatibilityWrapper) {
            $this->compatibilityWrapper->close();
        }
    }
    
    /**
     * Prevent cloning
     */
    private function __clone() {}
    
    /**
     * Prevent unserialization
     */
    public function __wakeup() 
    {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Create global connection variable for backward compatibility
$db = Database::getInstance();
$conn = $db->getConnection();

// Register shutdown function to close connection
register_shutdown_function(function() use ($db) {
    $db->close();
});
?>
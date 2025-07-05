<?php
/**
 * MySQLi Compatibility Wrapper for PDO
 * Provides MySQLi-like interface while using PDO underneath
 */

class MySQLiStatement 
{
    private $pdoStatement;
    private $connection;
    private $bindParams = [];
    private $bindResults = [];
    private $paramTypes = '';
    private $resultData = null;
    
    public function __construct($pdoStatement, $connection) 
    {
        $this->pdoStatement = $pdoStatement;
        $this->connection = $connection;
    }
    
    /**
     * Bind parameters (MySQLi style)
     */
    public function bind_param($types, ...$params) 
    {
        $this->paramTypes = $types;
        $this->bindParams = $params;
        return true;
    }
    
    /**
     * Execute the statement
     */
    public function execute() 
    {
        try {
            if (!empty($this->bindParams)) {
                return $this->pdoStatement->execute($this->bindParams);
            } else {
                return $this->pdoStatement->execute();
            }
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Bind result variables (MySQLi style)
     */
    public function bind_result(&...$vars) 
    {
        $this->bindResults = $vars;
        return true;
    }
    
    /**
     * Fetch one row and bind to result variables
     */
    public function fetch() 
    {
        try {
            $row = $this->pdoStatement->fetch(PDO::FETCH_NUM);
            if ($row) {
                for ($i = 0; $i < count($this->bindResults); $i++) {
                    if (isset($row[$i])) {
                        $this->bindResults[$i] = $row[$i];
                    }
                }
                return true;
            }
            return false;
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get result set (MySQLi style)
     */
    public function get_result() 
    {
        return new MySQLiResult($this->pdoStatement);
    }
    
    /**
     * Store result (MySQLi style)
     */
    public function store_result() 
    {
        // PDO doesn't need this, but we'll simulate it
        return true;
    }
    
    /**
     * Get number of rows
     */
    public function num_rows() 
    {
        return $this->pdoStatement->rowCount();
    }
    
    /**
     * Close statement
     */
    public function close() 
    {
        $this->pdoStatement = null;
        return true;
    }
    
    /**
     * Get error message
     */
    public function getError() 
    {
        $errorInfo = $this->pdoStatement->errorInfo();
        return $errorInfo[2] ?? '';
    }
    
    /**
     * Magic property access for error
     */
    public function __get($property) 
    {
        if ($property === 'error') {
            return $this->getError();
        }
        return null;
    }
}

class MySQLiResult 
{
    private $pdoStatement;
    private $data = [];
    private $fetched = false;
    
    public function __construct($pdoStatement) 
    {
        $this->pdoStatement = $pdoStatement;
    }
    
    /**
     * Fetch associative array
     */
    public function fetch_assoc() 
    {
        try {
            return $this->pdoStatement->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Fetch all rows
     */
    public function fetch_all($mode = MYSQLI_NUM) 
    {
        try {
            $pdoMode = ($mode === MYSQLI_ASSOC) ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;
            return $this->pdoStatement->fetchAll($pdoMode);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get number of rows
     */
    public function __get($property) 
    {
        if ($property === 'num_rows') {
            return $this->pdoStatement->rowCount();
        }
        return null;
    }
}

class MySQLiCompatibility 
{
    private $pdo;
    
    public function __construct($pdo) 
    {
        $this->pdo = $pdo;
    }
    
    /**
     * Prepare statement (MySQLi style)
     */
    public function prepare($query) 
    {
        try {
            $pdoStatement = $this->pdo->prepare($query);
            return new MySQLiStatement($pdoStatement, $this);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Execute query directly
     */
    public function query($query) 
    {
        try {
            $result = $this->pdo->query($query);
            return new MySQLiResult($result);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Execute statement directly
     */
    public function exec($query) 
    {
        try {
            return $this->pdo->exec($query);
        } catch (PDOException $e) {
            return false;
        }
    }
    
    /**
     * Get last insert ID
     */
    public function insert_id() 
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Get affected rows
     */
    public function affected_rows() 
    {
        return $this->pdo->rowCount();
    }
    
    /**
     * Get error message
     */
    public function getError() 
    {
        $errorInfo = $this->pdo->errorInfo();
        return $errorInfo[2] ?? '';
    }
    
    /**
     * Magic property access
     */
    public function __get($property) 
    {
        if ($property === 'error') {
            return $this->getError();
        }
        if ($property === 'connect_error') {
            return $this->getError();
        }
        return null;
    }
    
    /**
     * Begin transaction
     */
    public function begin_transaction() 
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit transaction
     */
    public function commit() 
    {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback transaction
     */
    public function rollback() 
    {
        return $this->pdo->rollback();
    }
    
    /**
     * Set charset
     */
    public function set_charset($charset) 
    {
        return $this->pdo->exec("SET NAMES $charset");
    }
    
    /**
     * Close connection
     */
    public function close() 
    {
        $this->pdo = null;
        return true;
    }
}

// Define MySQLi constants if not defined
if (!defined('MYSQLI_ASSOC')) {
    define('MYSQLI_ASSOC', 1);
}
if (!defined('MYSQLI_NUM')) {
    define('MYSQLI_NUM', 2);
}
?>

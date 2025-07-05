<?php
/**
 * Database Backup Utility
 * Creates backups of the database before running migrations
 */

class DatabaseBackup
{
    private $db;
    private $backupPath;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->backupPath = __DIR__ . '/backups';
        
        // Create backups directory if it doesn't exist
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0755, true);
        }
    }
    
    /**
     * Create a backup of the database
     */
    public function createBackup($description = '')
    {
        $timestamp = date('Y-m-d_H-i-s');
        $filename = "spark_backup_{$timestamp}.sql";
        $filepath = $this->backupPath . '/' . $filename;
        
        try {
            $conn = $this->db->getConnection();
            
            // Get database name
            $result = $conn->query("SELECT DATABASE() as db_name");
            $dbInfo = $result->fetch_assoc();
            $dbName = $dbInfo['db_name'];
            
            // Start building the SQL dump
            $dump = "-- SPARK Database Backup\n";
            $dump .= "-- Created: " . date('Y-m-d H:i:s') . "\n";
            $dump .= "-- Database: {$dbName}\n";
            if ($description) {
                $dump .= "-- Description: {$description}\n";
            }
            $dump .= "-- =====================================\n\n";
            
            $dump .= "SET FOREIGN_KEY_CHECKS = 0;\n";
            $dump .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
            $dump .= "SET AUTOCOMMIT = 0;\n";
            $dump .= "START TRANSACTION;\n\n";
            
            // Get all tables
            $result = $conn->query("SHOW TABLES");
            $tables = [];
            
            while ($row = $result->fetch_assoc()) {
                $tables[] = current($row);
            }
            
            // Export each table
            foreach ($tables as $table) {
                $dump .= $this->exportTable($table);
            }
            
            $dump .= "\nSET FOREIGN_KEY_CHECKS = 1;\n";
            $dump .= "COMMIT;\n";
            
            // Write to file
            if (file_put_contents($filepath, $dump)) {
                return [
                    'success' => true,
                    'filename' => $filename,
                    'filepath' => $filepath,
                    'size' => filesize($filepath)
                ];
            } else {
                return [
                    'success' => false,
                    'error' => 'Failed to write backup file'
                ];
            }
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Export a single table
     */
    private function exportTable($table)
    {
        $conn = $this->db->getConnection();
        $dump = "-- Table structure for table `{$table}`\n";
        $dump .= "DROP TABLE IF EXISTS `{$table}`;\n";
        
        // Get table structure
        $result = $conn->query("SHOW CREATE TABLE `{$table}`");
        $row = $result->fetch_assoc();
        $dump .= $row['Create Table'] . ";\n\n";
        
        // Get table data
        $result = $conn->query("SELECT * FROM `{$table}`");
        
        if ($result->num_rows > 0) {
            $dump .= "-- Dumping data for table `{$table}`\n";
            $dump .= "LOCK TABLES `{$table}` WRITE;\n";
            
            // Get column names from first row
            $firstRow = $result->fetch_assoc();
            if ($firstRow) {
                $columns = array_keys($firstRow);
                $columnsList = implode('`, `', $columns);
                $dump .= "INSERT INTO `{$table}` (`{$columnsList}`) VALUES\n";
                
                // Reset result and process all rows
                $result = $conn->query("SELECT * FROM `{$table}`");
                $rows = [];
                
                while ($row = $result->fetch_assoc()) {
                    $values = [];
                    foreach ($row as $value) {
                        if ($value === null) {
                            $values[] = 'NULL';
                        } else {
                            // Use prepared statement approach for escaping
                            $escaped = str_replace(
                                ["\\", "\0", "\n", "\r", "\x1a", "'", '"'],
                                ["\\\\", "\\0", "\\n", "\\r", "\\Z", "\'", '\"'],
                                $value
                            );
                            $values[] = "'" . $escaped . "'";
                        }
                    }
                    $rows[] = '(' . implode(', ', $values) . ')';
                }
                
                $dump .= implode(",\n", $rows) . ";\n";
            }
            $dump .= "UNLOCK TABLES;\n\n";
        }
        
        return $dump;
    }
    
    /**
     * List all available backups
     */
    public function listBackups()
    {
        $backups = [];
        
        if (is_dir($this->backupPath)) {
            $files = scandir($this->backupPath);
            
            foreach ($files as $file) {
                if (preg_match('/^spark_backup_(\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2})\.sql$/', $file, $matches)) {
                    $filepath = $this->backupPath . '/' . $file;
                    $backups[] = [
                        'filename' => $file,
                        'timestamp' => $matches[1],
                        'size' => filesize($filepath),
                        'created' => date('Y-m-d H:i:s', filemtime($filepath))
                    ];
                }
            }
        }
        
        // Sort by timestamp descending
        usort($backups, function($a, $b) {
            return strcmp($b['timestamp'], $a['timestamp']);
        });
        
        return $backups;
    }
    
    /**
     * Restore from a backup file
     */
    public function restoreBackup($filename)
    {
        $filepath = $this->backupPath . '/' . $filename;
        
        if (!file_exists($filepath)) {
            return [
                'success' => false,
                'error' => 'Backup file not found'
            ];
        }
        
        try {
            $conn = $this->db->getConnection();
            $sql = file_get_contents($filepath);
            
            // Split SQL into individual statements
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                function($stmt) {
                    return !empty($stmt) && !preg_match('/^--/', $stmt);
                }
            );
            
            // Execute each statement individually
            foreach ($statements as $statement) {
                if (!empty(trim($statement))) {
                    $result = $conn->query($statement);
                    if (!$result) {
                        throw new Exception("Failed to execute statement: " . $statement);
                    }
                }
            }
            
            return [
                'success' => true,
                'message' => 'Database restored successfully'
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete a backup file
     */
    public function deleteBackup($filename)
    {
        $filepath = $this->backupPath . '/' . $filename;
        
        if (file_exists($filepath)) {
            return unlink($filepath);
        }
        
        return false;
    }
}

<?php
/**
 * Database Migration System
 * Handles database schema changes and versioning
 */

class MigrationManager
{
    private $db;
    private $migrationsPath;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrationsPath = dirname(__DIR__) . '/migrations';
        $this->createMigrationsTable();
    }
    
    /**
     * Create migrations tracking table if it doesn't exist
     */
    private function createMigrationsTable()
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS migrations (
                id INT AUTO_INCREMENT PRIMARY KEY,
                version VARCHAR(255) NOT NULL UNIQUE,
                applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_version (version)
            ) ENGINE=InnoDB
        ";
        
        $this->db->query($sql);
    }
    
    /**
     * Run all pending migrations
     */
    public function migrate()
    {
        $appliedMigrations = $this->getAppliedMigrations();
        $migrationFiles = $this->getMigrationFiles();
        
        $pendingMigrations = array_diff($migrationFiles, $appliedMigrations);
        
        if (empty($pendingMigrations)) {
            echo "No pending migrations.\n";
            return true;
        }
        
        foreach ($pendingMigrations as $migration) {
            echo "Applying migration: $migration\n";
            
            if ($this->applyMigration($migration)) {
                $this->recordMigration($migration);
                echo "✓ Migration $migration applied successfully.\n";
            } else {
                echo "✗ Failed to apply migration: $migration\n";
                return false;
            }
        }
        
        echo "All migrations applied successfully.\n";
        return true;
    }
    
    /**
     * Rollback the last migration
     */
    public function rollback()
    {
        $lastMigration = $this->getLastAppliedMigration();
        
        if (!$lastMigration) {
            echo "No migrations to rollback.\n";
            return true;
        }
        
        echo "Rolling back migration: {$lastMigration['version']}\n";
        
        if ($this->rollbackMigration($lastMigration['version'])) {
            $this->removeMigrationRecord($lastMigration['version']);
            echo "✓ Migration {$lastMigration['version']} rolled back successfully.\n";
            return true;
        } else {
            echo "✗ Failed to rollback migration: {$lastMigration['version']}\n";
            return false;
        }
    }
    
    /**
     * Get status of all migrations
     */
    public function status()
    {
        $appliedMigrations = $this->getAppliedMigrations();
        $migrationFiles = $this->getMigrationFiles();
        
        echo "Migration Status:\n";
        echo str_repeat("-", 50) . "\n";
        
        foreach ($migrationFiles as $migration) {
            $status = in_array($migration, $appliedMigrations) ? "Applied" : "Pending";
            echo sprintf("%-40s %s\n", $migration, $status);
        }
    }
    
    /**
     * Get applied migrations from database
     */
    private function getAppliedMigrations()
    {
        $result = $this->db->query("SELECT version FROM migrations ORDER BY version");
        $migrations = [];
        
        while ($row = $result->fetch_assoc()) {
            $migrations[] = $row['version'];
        }
        
        return $migrations;
    }
    
    /**
     * Get migration files from filesystem
     */
    private function getMigrationFiles()
    {
        $files = [];
        
        if (is_dir($this->migrationsPath)) {
            $iterator = new DirectoryIterator($this->migrationsPath);
            
            foreach ($iterator as $file) {
                if ($file->isFile() && $file->getExtension() === 'php') {
                    $files[] = $file->getBasename('.php');
                }
            }
        }
        
        sort($files);
        return $files;
    }
    
    /**
     * Apply a specific migration
     */
    private function applyMigration($migration)
    {
        $migrationFile = $this->migrationsPath . '/' . $migration . '.php';
        
        if (!file_exists($migrationFile)) {
            return false;
        }
        
        try {
            $migrationClass = require $migrationFile;
            
            if (is_object($migrationClass) && method_exists($migrationClass, 'up')) {
                return $migrationClass->up($this->db);
            }
            
            return false;
        } catch (Exception $e) {
            echo "Error applying migration $migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Rollback a specific migration
     */
    private function rollbackMigration($migration)
    {
        $migrationFile = $this->migrationsPath . '/' . $migration . '.php';
        
        if (!file_exists($migrationFile)) {
            return false;
        }
        
        try {
            $migrationClass = require $migrationFile;
            
            if (is_object($migrationClass) && method_exists($migrationClass, 'down')) {
                return $migrationClass->down($this->db);
            }
            
            return false;
        } catch (Exception $e) {
            echo "Error rolling back migration $migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    /**
     * Record a migration as applied
     */
    private function recordMigration($migration)
    {
        $stmt = $this->db->getConnection()->prepare("INSERT INTO migrations (version) VALUES (?)");
        $stmt->bind_param("s", $migration);
        $stmt->execute();
    }
    
    /**
     * Remove migration record
     */
    private function removeMigrationRecord($migration)
    {
        $stmt = $this->db->getConnection()->prepare("DELETE FROM migrations WHERE version = ?");
        $stmt->bind_param("s", $migration);
        $stmt->execute();
    }
    
    /**
     * Get the last applied migration
     */
    private function getLastAppliedMigration()
    {
        $result = $this->db->query("SELECT version, applied_at FROM migrations ORDER BY applied_at DESC LIMIT 1");
        return $result->fetch_assoc();
    }
}

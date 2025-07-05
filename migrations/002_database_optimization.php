<?php
/**
 * Database Optimization Migration
 * Adds additional indexes and constraints for better performance
 */

return new class {
    public function up($db)
    {
        try {
            $conn = $db->getConnection();
            
            // Add composite indexes for better query performance
            $sql = "
                ALTER TABLE staff_schedule 
                ADD INDEX idx_staff_date (staff_id, scheduled_date),
                ADD INDEX idx_location_date (location, scheduled_date)
            ";
            $conn->query($sql);
            
            // Add composite index for progress reports
            $sql = "
                ALTER TABLE progress_reports 
                ADD INDEX idx_staff_date (staff_id, report_date),
                ADD INDEX idx_location_date (location, report_date)
            ";
            $conn->query($sql);
            
            // Add composite index for evaluations
            $sql = "
                ALTER TABLE evaluations 
                ADD INDEX idx_employee_date (employee_id, evaluation_date),
                ADD INDEX idx_evaluator_date (evaluator_id, evaluation_date)
            ";
            $conn->query($sql);
            
            // Add composite index for supplies usage
            $sql = "
                ALTER TABLE supplies_usage 
                ADD INDEX idx_staff_date (staff_id, usage_date),
                ADD INDEX idx_supply_date (supply_name, usage_date)
            ";
            $conn->query($sql);
            
            // Add composite index for supply requests
            $sql = "
                ALTER TABLE supply_requests 
                ADD INDEX idx_staff_status (staff_id, status),
                ADD INDEX idx_date_status (request_date, status)
            ";
            $conn->query($sql);
            
            // Add fulltext index for search functionality
            $sql = "
                ALTER TABLE staff 
                ADD FULLTEXT idx_name_search (first_name, last_name)
            ";
            $conn->query($sql);
            
            // Add fulltext index for location search
            $sql = "
                ALTER TABLE location 
                ADD FULLTEXT idx_location_search (location_name, description)
            ";
            $conn->query($sql);
            
            // Add constraints to ensure data integrity
            $sql = "
                ALTER TABLE evaluations 
                ADD CONSTRAINT chk_rating CHECK (rating BETWEEN 1 AND 5)
            ";
            $conn->query($sql);
            
            $sql = "
                ALTER TABLE supply_requests 
                ADD CONSTRAINT chk_quantity CHECK (quantity > 0)
            ";
            $conn->query($sql);
            
            $sql = "
                ALTER TABLE supplies_usage 
                ADD CONSTRAINT chk_quantity_used CHECK (quantity_used > 0)
            ";
            $conn->query($sql);
            
            return true;
            
        } catch (Exception $e) {
            echo "Error applying optimization migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function down($db)
    {
        try {
            $conn = $db->getConnection();
            
            // Remove composite indexes
            $conn->query("ALTER TABLE staff_schedule DROP INDEX idx_staff_date, DROP INDEX idx_location_date");
            $conn->query("ALTER TABLE progress_reports DROP INDEX idx_staff_date, DROP INDEX idx_location_date");
            $conn->query("ALTER TABLE evaluations DROP INDEX idx_employee_date, DROP INDEX idx_evaluator_date");
            $conn->query("ALTER TABLE supplies_usage DROP INDEX idx_staff_date, DROP INDEX idx_supply_date");
            $conn->query("ALTER TABLE supply_requests DROP INDEX idx_staff_status, DROP INDEX idx_date_status");
            
            // Remove fulltext indexes
            $conn->query("ALTER TABLE staff DROP INDEX idx_name_search");
            $conn->query("ALTER TABLE location DROP INDEX idx_location_search");
            
            // Remove constraints
            $conn->query("ALTER TABLE evaluations DROP CONSTRAINT chk_rating");
            $conn->query("ALTER TABLE supply_requests DROP CONSTRAINT chk_quantity");
            $conn->query("ALTER TABLE supplies_usage DROP CONSTRAINT chk_quantity_used");
            
            return true;
            
        } catch (Exception $e) {
            echo "Error rolling back optimization migration: " . $e->getMessage() . "\n";
            return false;
        }
    }
};

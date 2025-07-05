<?php
/**
 * Initial Database Schema Migration
 * Creates all the core tables needed for the SPARK system
 */

return new class {
    public function up($db)
    {
        try {
            $conn = $db->getConnection();
            
            // Create account table
            $sql = "
                CREATE TABLE IF NOT EXISTS account (
                    account_id INT AUTO_INCREMENT PRIMARY KEY,
                    username VARCHAR(50) NOT NULL UNIQUE,
                    password VARCHAR(255) NOT NULL,
                    email_address VARCHAR(100) NOT NULL,
                    role ENUM('admin', 'supervisor', 'housekeeping_staff') NOT NULL DEFAULT 'housekeeping_staff',
                    employee_id VARCHAR(20) NOT NULL UNIQUE,
                    status ENUM('active', 'inactive', 'locked') NOT NULL DEFAULT 'active',
                    failed_login_attempts INT DEFAULT 0,
                    locked_until TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_username (username),
                    INDEX idx_employee_id (employee_id),
                    INDEX idx_email (email_address),
                    INDEX idx_status (status)
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create staff table
            $sql = "
                CREATE TABLE IF NOT EXISTS staff (
                    staff_id INT AUTO_INCREMENT PRIMARY KEY,
                    first_name VARCHAR(50) NOT NULL,
                    last_name VARCHAR(50) NOT NULL,
                    employee_id VARCHAR(20) NOT NULL UNIQUE,
                    contact_no VARCHAR(20),
                    email_address VARCHAR(100),
                    position VARCHAR(100),
                    profile_picture TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_employee_id (employee_id),
                    INDEX idx_email (email_address),
                    FOREIGN KEY (employee_id) REFERENCES account(employee_id) ON UPDATE CASCADE
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create location table
            $sql = "
                CREATE TABLE IF NOT EXISTS location (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    location_name VARCHAR(100) NOT NULL,
                    building VARCHAR(10) NOT NULL,
                    description TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_location_name (location_name),
                    INDEX idx_building (building)
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create staff_schedule table
            $sql = "
                CREATE TABLE IF NOT EXISTS staff_schedule (
                    schedule_id INT AUTO_INCREMENT PRIMARY KEY,
                    staff_id INT NOT NULL,
                    location VARCHAR(100) NOT NULL,
                    scheduled_date DATE NOT NULL,
                    start_time TIME NOT NULL,
                    end_time TIME NOT NULL,
                    status ENUM('scheduled', 'completed', 'missed', 'cancelled') DEFAULT 'scheduled',
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_staff_id (staff_id),
                    INDEX idx_location (location),
                    INDEX idx_scheduled_date (scheduled_date),
                    INDEX idx_status (status),
                    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create progress_reports table
            $sql = "
                CREATE TABLE IF NOT EXISTS progress_reports (
                    report_id INT AUTO_INCREMENT PRIMARY KEY,
                    staff_id INT NOT NULL,
                    location VARCHAR(100) NOT NULL,
                    description TEXT,
                    image_path VARCHAR(255),
                    report_date DATE NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_staff_id (staff_id),
                    INDEX idx_location (location),
                    INDEX idx_report_date (report_date),
                    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create evaluations table
            $sql = "
                CREATE TABLE IF NOT EXISTS evaluations (
                    evaluation_id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id VARCHAR(20) NOT NULL,
                    evaluator_id VARCHAR(20) NOT NULL,
                    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
                    comments TEXT,
                    evaluation_date DATE NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_employee_id (employee_id),
                    INDEX idx_evaluator_id (evaluator_id),
                    INDEX idx_evaluation_date (evaluation_date),
                    INDEX idx_rating (rating),
                    FOREIGN KEY (employee_id) REFERENCES account(employee_id) ON UPDATE CASCADE,
                    FOREIGN KEY (evaluator_id) REFERENCES account(employee_id) ON UPDATE CASCADE
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create leaderboard_history table
            $sql = "
                CREATE TABLE IF NOT EXISTS leaderboard_history (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    employee_id VARCHAR(20) NOT NULL,
                    full_name VARCHAR(100) NOT NULL,
                    total_rating INT NOT NULL,
                    month VARCHAR(7) NOT NULL, -- Format: YYYY-MM
                    rank_position INT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_employee_id (employee_id),
                    INDEX idx_month (month),
                    INDEX idx_rank (rank_position),
                    FOREIGN KEY (employee_id) REFERENCES account(employee_id) ON UPDATE CASCADE
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create supply_requests table
            $sql = "
                CREATE TABLE IF NOT EXISTS supply_requests (
                    request_id INT AUTO_INCREMENT PRIMARY KEY,
                    staff_id INT NOT NULL,
                    supply_name VARCHAR(100) NOT NULL,
                    quantity INT NOT NULL,
                    description TEXT,
                    status ENUM('pending', 'approved', 'rejected', 'fulfilled') DEFAULT 'pending',
                    request_date DATE NOT NULL,
                    approved_by INT NULL,
                    approved_at TIMESTAMP NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_staff_id (staff_id),
                    INDEX idx_status (status),
                    INDEX idx_request_date (request_date),
                    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE,
                    FOREIGN KEY (approved_by) REFERENCES staff(staff_id) ON SET NULL
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create supplies_usage table
            $sql = "
                CREATE TABLE IF NOT EXISTS supplies_usage (
                    usage_id INT AUTO_INCREMENT PRIMARY KEY,
                    staff_id INT NOT NULL,
                    supply_name VARCHAR(100) NOT NULL,
                    quantity_used INT NOT NULL,
                    location VARCHAR(100),
                    usage_date DATE NOT NULL,
                    notes TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    INDEX idx_staff_id (staff_id),
                    INDEX idx_supply_name (supply_name),
                    INDEX idx_usage_date (usage_date),
                    INDEX idx_location (location),
                    FOREIGN KEY (staff_id) REFERENCES staff(staff_id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create sessions table for session management
            $sql = "
                CREATE TABLE IF NOT EXISTS sessions (
                    session_id VARCHAR(128) PRIMARY KEY,
                    account_id INT NOT NULL,
                    ip_address VARCHAR(45) NOT NULL,
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                    expires_at TIMESTAMP NOT NULL,
                    INDEX idx_account_id (account_id),
                    INDEX idx_expires_at (expires_at),
                    FOREIGN KEY (account_id) REFERENCES account(account_id) ON DELETE CASCADE
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            // Create audit_logs table for security logging
            $sql = "
                CREATE TABLE IF NOT EXISTS audit_logs (
                    log_id INT AUTO_INCREMENT PRIMARY KEY,
                    account_id INT,
                    action VARCHAR(50) NOT NULL,
                    resource VARCHAR(100),
                    details JSON,
                    ip_address VARCHAR(45),
                    user_agent TEXT,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_account_id (account_id),
                    INDEX idx_action (action),
                    INDEX idx_created_at (created_at),
                    FOREIGN KEY (account_id) REFERENCES account(account_id) ON SET NULL
                ) ENGINE=InnoDB
            ";
            $conn->query($sql);
            
            return true;
            
        } catch (Exception $e) {
            echo "Error creating initial schema: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function down($db)
    {
        try {
            $conn = $db->getConnection();
            
            // Drop tables in reverse order to handle foreign key constraints
            $tables = [
                'audit_logs',
                'sessions',
                'supplies_usage',
                'supply_requests',
                'leaderboard_history',
                'evaluations',
                'progress_reports',
                'staff_schedule',
                'location',
                'staff',
                'account'
            ];
            
            foreach ($tables as $table) {
                $conn->query("DROP TABLE IF EXISTS $table");
            }
            
            return true;
            
        } catch (Exception $e) {
            echo "Error rolling back initial schema: " . $e->getMessage() . "\n";
            return false;
        }
    }
};

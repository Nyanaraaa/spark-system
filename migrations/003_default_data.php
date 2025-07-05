<?php
/**
 * Default Data Migration
 * Inserts default admin user and sample data
 */

return new class {
    public function up($db)
    {
        try {
            $conn = $db->getConnection();
            
            // Create default admin account if it doesn't exist
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM account WHERE role = 'admin'");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                // Create default admin account
                $adminPassword = password_hash('admin123', PASSWORD_ARGON2ID);
                $stmt = $conn->prepare("
                    INSERT INTO account (username, password, email_address, role, employee_id) 
                    VALUES (?, ?, ?, 'admin', ?)
                ");
                $username = 'admin';
                $email = 'admin@spark.system';
                $employeeId = 'ADMIN001';
                $stmt->bind_param("ssss", $username, $adminPassword, $email, $employeeId);
                $stmt->execute();
                
                // Get the admin account ID
                $adminAccountId = $conn->insert_id;
                
                // Create corresponding staff record
                $stmt = $conn->prepare("
                    INSERT INTO staff (first_name, last_name, employee_id, email_address, position) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $firstName = 'System';
                $lastName = 'Administrator';
                $position = 'System Administrator';
                $stmt->bind_param("sssss", $firstName, $lastName, $employeeId, $email, $position);
                $stmt->execute();
            }
            
            // Insert default locations if they don't exist
            $locations = [
                ['Western Hall', 'A', 'Main academic building west wing'],
                ['Velasquez Hall Including Maintenance Office', 'B', 'Administrative building with maintenance facility'],
                ['Eastern Hall', 'C', 'Main academic building east wing'],
                ['Library Building', 'D', 'Central library and study areas'],
                ['Cafeteria', 'E', 'Student dining facility'],
                ['Gymnasium', 'F', 'Sports and physical education facility'],
                ['Laboratory Building', 'G', 'Science and computer laboratories']
            ];
            
            foreach ($locations as $location) {
                $stmt = $conn->prepare("
                    INSERT IGNORE INTO location (location_name, building, description) 
                    VALUES (?, ?, ?)
                ");
                $stmt->bind_param("sss", $location[0], $location[1], $location[2]);
                $stmt->execute();
            }
            
            // Create sample supervisor account if it doesn't exist
            $stmt = $conn->prepare("SELECT COUNT(*) as count FROM account WHERE role = 'supervisor'");
            $stmt->execute();
            $result = $stmt->get_result();
            $row = $result->fetch_assoc();
            
            if ($row['count'] == 0) {
                $supervisorPassword = password_hash('supervisor123', PASSWORD_ARGON2ID);
                $stmt = $conn->prepare("
                    INSERT INTO account (username, password, email_address, role, employee_id) 
                    VALUES (?, ?, ?, 'supervisor', ?)
                ");
                $username = 'supervisor';
                $email = 'supervisor@spark.system';
                $employeeId = 'SUP001';
                $stmt->bind_param("ssss", $username, $supervisorPassword, $email, $employeeId);
                $stmt->execute();
                
                // Create corresponding staff record
                $stmt = $conn->prepare("
                    INSERT INTO staff (first_name, last_name, employee_id, email_address, position) 
                    VALUES (?, ?, ?, ?, ?)
                ");
                $firstName = 'Housekeeping';
                $lastName = 'Supervisor';
                $position = 'Housekeeping Supervisor';
                $stmt->bind_param("sssss", $firstName, $lastName, $employeeId, $email, $position);
                $stmt->execute();
            }
            
            return true;
            
        } catch (Exception $e) {
            echo "Error inserting default data: " . $e->getMessage() . "\n";
            return false;
        }
    }
    
    public function down($db)
    {
        try {
            $conn = $db->getConnection();
            
            // Remove default accounts (be careful in production!)
            $conn->query("DELETE FROM staff WHERE employee_id IN ('ADMIN001', 'SUP001')");
            $conn->query("DELETE FROM account WHERE employee_id IN ('ADMIN001', 'SUP001')");
            
            // Remove default locations
            $defaultLocations = [
                'Western Hall',
                'Velasquez Hall Including Maintenance Office',
                'Eastern Hall',
                'Library Building',
                'Cafeteria',
                'Gymnasium',
                'Laboratory Building'
            ];
            
            foreach ($defaultLocations as $location) {
                $stmt = $conn->prepare("DELETE FROM location WHERE location_name = ?");
                $stmt->bind_param("s", $location);
                $stmt->execute();
            }
            
            return true;
            
        } catch (Exception $e) {
            echo "Error rolling back default data: " . $e->getMessage() . "\n";
            return false;
        }
    }
};

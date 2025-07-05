<?php
/**
 * Database Helper
 * Common database operations with security
 */

// Ensure Database class is available
if (!class_exists('Database')) {
    require_once dirname(__DIR__) . '/config/database.php';
}

class DatabaseHelper 
{
    private $db;
    private $conn;
    
    public function __construct() 
    {
        $this->db = Database::getInstance();
        $this->conn = $this->db->getConnection();
    }
    
    /**
     * Secure user authentication
     */
    public function authenticateUser($username, $password, $expectedRole = null) 
    {
        try {
            $stmt = $this->db->prepare("SELECT account_id, username, password, role, employee_id FROM account WHERE username = ? AND status = 'active'");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                logEvent('Authentication failed: User not found', ['username' => $username]);
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            $user = $result->fetch_assoc();
            $stmt->close();
            
            // Verify password using secure method
            if (!PasswordSecurity::verifyPassword($password, $user['password'])) {
                logEvent('Authentication failed: Invalid password', ['username' => $username]);
                return ['success' => false, 'message' => 'Invalid username or password'];
            }
            
            // Check role if specified
            if ($expectedRole && $user['role'] !== $expectedRole) {
                logEvent('Authentication failed: Role mismatch', [
                    'username' => $username, 
                    'expected_role' => $expectedRole,
                    'actual_role' => $user['role']
                ]);
                return ['success' => false, 'message' => 'Access denied for this role'];
            }
            
            // Get staff_id for housekeeping staff
            if ($user['role'] === 'housekeeping_staff') {
                $staffStmt = $this->db->prepare("SELECT staff_id FROM staff WHERE employee_id = ?");
                $staffStmt->bind_param("s", $user['employee_id']);
                $staffStmt->execute();
                $staffResult = $staffStmt->get_result();
                
                if ($staffResult->num_rows > 0) {
                    $staffData = $staffResult->fetch_assoc();
                    $user['staff_id'] = $staffData['staff_id'];
                }
                $staffStmt->close();
            }
            
            // Check if password needs rehashing
            if (PasswordSecurity::needsRehash($user['password'])) {
                $this->updateUserPassword($user['account_id'], $password);
            }
            
            logEvent('Authentication successful', ['username' => $username, 'role' => $user['role']]);
            
            return [
                'success' => true,
                'user' => $user,
                'message' => 'Authentication successful'
            ];
            
        } catch (Exception $e) {
            ErrorHandler::logError('Authentication error: ' . $e->getMessage(), ['username' => $username]);
            return ['success' => false, 'message' => 'Authentication system error'];
        }
    }
    
    /**
     * Create new user account with secure password
     */
    public function createUserAccount($userData) 
    {
        try {
            // Validate input
            $validation = InputValidator::validateFields($userData, [
                'username' => ['type' => 'username'],
                'password' => ['type' => 'text', 'min_length' => 8],
                'employee_id' => ['type' => 'employee_id'],
                'email_address' => ['type' => 'email']
            ]);
            
            if (!$validation['valid']) {
                return ['success' => false, 'message' => 'Validation failed', 'errors' => $validation['fields']];
            }
            
            // Check password strength
            $passwordCheck = PasswordSecurity::validatePasswordStrength($userData['password']);
            if (!$passwordCheck['valid']) {
                return ['success' => false, 'message' => 'Password requirements not met', 'errors' => $passwordCheck['errors']];
            }
            
            $this->db->beginTransaction();
            
            // Check if username already exists
            $stmt = $this->db->prepare("SELECT account_id FROM account WHERE username = ?");
            $stmt->bind_param("s", $validation['fields']['username']['value']);
            $stmt->execute();
            
            if ($stmt->get_result()->num_rows > 0) {
                $stmt->close();
                $this->db->rollback();
                return ['success' => false, 'message' => 'Username already exists'];
            }
            $stmt->close();
            
            // Check if employee exists
            $stmt = $this->db->prepare("SELECT employee_id, email_address FROM staff WHERE employee_id = ?");
            $stmt->bind_param("s", $validation['fields']['employee_id']['value']);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                $stmt->close();
                $this->db->rollback();
                return ['success' => false, 'message' => 'Invalid employee ID'];
            }
            
            $employeeData = $result->fetch_assoc();
            $stmt->close();
            
            // Determine role based on employee_id
            $role = ($userData['employee_id'] === 'supervisor') ? 'supervisor' : 'housekeeping_staff';
            
            // Hash password securely
            $hashedPassword = PasswordSecurity::hashPassword($userData['password']);
            
            // Insert new account
            $stmt = $this->db->prepare("INSERT INTO account (username, password, role, employee_id, email_address, status, created_at) VALUES (?, ?, ?, ?, ?, 'active', NOW())");
            $stmt->bind_param("sssss", 
                $validation['fields']['username']['value'],
                $hashedPassword,
                $role,
                $validation['fields']['employee_id']['value'],
                $employeeData['email_address']
            );
            
            $stmt->execute();
            $accountId = $this->db->getLastInsertId();
            $stmt->close();
            
            $this->db->commit();
            
            logEvent('User account created successfully', [
                'account_id' => $accountId,
                'username' => $validation['fields']['username']['value'],
                'role' => $role
            ]);
            
            return ['success' => true, 'message' => 'Account created successfully', 'account_id' => $accountId];
            
        } catch (Exception $e) {
            $this->db->rollback();
            ErrorHandler::logError('Account creation error: ' . $e->getMessage(), $userData);
            return ['success' => false, 'message' => 'Account creation failed'];
        }
    }
    
    /**
     * Update user password securely
     */
    public function updateUserPassword($accountId, $newPassword) 
    {
        try {
            $passwordCheck = PasswordSecurity::validatePasswordStrength($newPassword);
            if (!$passwordCheck['valid']) {
                return ['success' => false, 'message' => 'Password requirements not met', 'errors' => $passwordCheck['errors']];
            }
            
            $hashedPassword = PasswordSecurity::hashPassword($newPassword);
            
            $stmt = $this->db->prepare("UPDATE account SET password = ?, updated_at = NOW() WHERE account_id = ?");
            $stmt->bind_param("si", $hashedPassword, $accountId);
            $stmt->execute();
            $stmt->close();
            
            logEvent('Password updated successfully', ['account_id' => $accountId]);
            
            return ['success' => true, 'message' => 'Password updated successfully'];
            
        } catch (Exception $e) {
            ErrorHandler::logError('Password update error: ' . $e->getMessage(), ['account_id' => $accountId]);
            return ['success' => false, 'message' => 'Password update failed'];
        }
    }
    
    /**
     * Secure data retrieval with pagination
     */
    public function getSecureData($table, $conditions = [], $limit = 50, $offset = 0, $orderBy = 'id DESC') 
    {
        try {
            $sql = "SELECT * FROM " . $this->escapeIdentifier($table);
            $params = [];
            $types = '';
            
            if (!empty($conditions)) {
                $whereClauses = [];
                foreach ($conditions as $field => $value) {
                    $whereClauses[] = $this->escapeIdentifier($field) . " = ?";
                    $params[] = $value;
                    $types .= 's'; // Assuming string type, modify as needed
                }
                $sql .= " WHERE " . implode(" AND ", $whereClauses);
            }
            
            $sql .= " ORDER BY " . $orderBy . " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            $types .= 'ii';
            
            $stmt = $this->db->prepare($sql);
            if (!empty($params)) {
                $stmt->bind_param($types, ...$params);
            }
            
            $stmt->execute();
            $result = $stmt->get_result();
            $data = $result->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
            
            return ['success' => true, 'data' => $data];
            
        } catch (Exception $e) {
            ErrorHandler::logError('Data retrieval error: ' . $e->getMessage(), [
                'table' => $table,
                'conditions' => $conditions
            ]);
            return ['success' => false, 'message' => 'Data retrieval failed'];
        }
    }
    
    /**
     * Escape SQL identifiers (table/column names)
     */
    private function escapeIdentifier($identifier) 
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }
    
    /**
     * Get connection for backward compatibility
     */
    public function getConnection() 
    {
        return $this->conn;
    }
}

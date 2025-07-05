<?php
/**
 * Secure Login Handler - Example Implementation
 * Replace your existing login.php with this enhanced version
 */

// Include security components
require_once '../../includes/bootstrap.php';

// Initialize CSRF protection
CSRFProtection::validateRequest();

// Set JSON response headers
header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] !== "POST") {
        ResponseHelper::error('Invalid request method', 405);
    }
    
    // Validate input
    $validation = InputValidator::validateFields($_POST, [
        'username' => ['type' => 'username'],
        'password' => ['type' => 'text', 'min_length' => 1],
        'modalType' => ['type' => 'text']
    ]);
    
    if (!$validation['valid']) {
        $errors = [];
        foreach ($validation['fields'] as $field => $result) {
            if (!$result['valid']) {
                $errors[$field] = $result['message'];
            }
        }
        ResponseHelper::validationError($errors, 'Please check your input');
    }
    
    $username = $validation['fields']['username']['value'];
    $password = $_POST['password']; // Don't sanitize password
    $modalType = $validation['fields']['modalType']['value'];
    
    // Determine expected role based on modal type
    $expectedRole = null;
    if ($modalType === 'supervisor') {
        $expectedRole = 'supervisor';
    } elseif ($modalType === 'staff') {
        $expectedRole = 'housekeeping_staff';
    }
    
    // Use secure authentication
    $dbHelper = new DatabaseHelper();
    $authResult = $dbHelper->authenticateUser($username, $password, $expectedRole);
    
    if (!$authResult['success']) {
        // Add small delay to prevent brute force attacks
        usleep(500000); // 0.5 second delay
        ResponseHelper::error($authResult['message'], 401);
    }
    
    $user = $authResult['user'];
    
    // Set secure session
    SessionManager::setUserSession([
        'username' => $user['username'],
        'role' => $user['role'],
        'account_id' => $user['account_id'],
        'employee_id' => $user['employee_id'],
        'staff_id' => $user['staff_id'] ?? null
    ]);
    
    // Update last login time
    $stmt = Database::getInstance()->prepare("UPDATE account SET last_login = NOW(), failed_login_attempts = 0 WHERE account_id = ?");
    $stmt->bind_param("i", $user['account_id']);
    $stmt->execute();
    $stmt->close();
    
    // Determine redirect URL
    if ($user['role'] === 'supervisor') {
        $redirectUrl = 'modules/supervisor/pages/staff_list.php';
    } else {
        $redirectUrl = 'modules/staff/pages/dashboard.php';
    }
    
    logEvent('User logged in successfully', [
        'username' => $username,
        'role' => $user['role'],
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    ResponseHelper::success(['redirect' => $redirectUrl], 'Login successful');
    
} catch (Exception $e) {
    ErrorHandler::logError('Login system error: ' . $e->getMessage(), [
        'username' => $_POST['username'] ?? 'unknown',
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
    ]);
    
    ResponseHelper::serverError('Login system temporarily unavailable');
}
?>

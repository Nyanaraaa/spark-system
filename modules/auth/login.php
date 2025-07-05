<?php
/**
 * Secure Login Handler
 * Uses new security components and password verification
 */

// Start output buffering to catch any unexpected output
ob_start();

// Include bootstrap for security components
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';

// Handle CORS for localhost/www.localhost issues
$allowedOrigins = [
    'http://localhost',
    'http://www.localhost',
    'http://127.0.0.1'
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
}
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

// Function to send JSON response and exit
function sendJsonResponse($data) {
    // Clear any buffered output
    ob_clean();
    echo json_encode($data);
    exit;
}

try {
    // Initialize variables
    $username = '';
    
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Validate CSRF token
        CSRFProtection::validateRequest();
        
        // Get and validate input
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        $modalType = $_POST['modalType'] ?? '';

        // Validate username
        $usernameValidation = InputValidator::validateUsername($username);
        if (!$usernameValidation['valid']) {
            sendJsonResponse([
                'success' => false, 
                'message' => $usernameValidation['message']
            ]);
        }

        // Validate password (basic check)
        if (empty($password) || strlen($password) < 6) {
            sendJsonResponse([
                'success' => false, 
                'message' => 'Password must be at least 6 characters long'
            ]);
        }

        // Validate modal type
        if (!in_array($modalType, ['supervisor', 'staff'])) {
            sendJsonResponse([
                'success' => false, 
                'message' => 'Invalid login type'
            ]);
        }

        $username = $usernameValidation['value'];
        $password = trim($password);

        // Get database connection
        $db = Database::getInstance();
        /** @var MySQLiCompatibility $conn */
        $conn = $db->getConnection();
        
        if (!$conn) {
            throw new Exception('Database connection failed');
        }

        // Check for account with username
        $stmt = $conn->prepare("SELECT account_id, username, password, role, employee_id, status, failed_login_attempts, locked_until, email_address FROM account WHERE username = ?");
        if (!$stmt) {
            throw new Exception('Database prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("s", $username);
        if (!$stmt->execute()) {
            throw new Exception('Database execute failed: ' . $stmt->error);
        }
        
        $result = $stmt->get_result();
        $account = $result->fetch_assoc();
        $stmt->close();

        if ($account) {
            // Check if account is locked
            if ($account['status'] === 'locked' || 
                ($account['locked_until'] && strtotime($account['locked_until']) > time())) {
                sendJsonResponse(['success' => false, 'message' => 'Account is locked. Please contact administrator.']);
            }
            
            // Check if account is inactive
            if ($account['status'] === 'inactive') {
                sendJsonResponse(['success' => false, 'message' => 'Account is inactive. Please contact administrator.']);
            }

            // Verify password
            if (PasswordSecurity::verifyPassword($password, $account['password'])) {
                // Check role authorization for modal type
                if (($modalType == 'supervisor' && $account['role'] != 'supervisor') || 
                    ($modalType == 'staff' && $account['role'] != 'housekeeping_staff')) {
                    
                    // Increment failed attempts for wrong role
                    incrementFailedAttempts($conn, $account['account_id']);
                    sendJsonResponse(['success' => false, 'message' => 'Invalid username or password!']);
                }

                // Reset failed login attempts on successful login
                $resetStmt = $conn->prepare("UPDATE account SET failed_login_attempts = 0, locked_until = NULL, last_login = NOW() WHERE account_id = ?");
                $resetStmt->bind_param("i", $account['account_id']);
                $resetStmt->execute();
                $resetStmt->close();

                // Get complete user details for session
                $userSessionData = [
                    'account_id' => $account['account_id'],
                    'username' => $account['username'],
                    'role' => $account['role'],
                    'employee_id' => $account['employee_id'],
                    'email_address' => $account['email_address'] ?? ''
                ];

                // Get staff details for all users (staff records may exist for supervisors too)
                $staff_stmt = $conn->prepare("SELECT staff_id, first_name, last_name FROM staff WHERE employee_id = ?");
                $staff_stmt->bind_param("s", $account['employee_id']);
                $staff_stmt->execute();
                $staff_result = $staff_stmt->get_result();
                $staff_data = $staff_result->fetch_assoc();
                
                if ($staff_data) {
                    $userSessionData['staff_id'] = $staff_data['staff_id'];
                    $userSessionData['first_name'] = $staff_data['first_name'];
                    $userSessionData['last_name'] = $staff_data['last_name'];
                } else {
                    // For users without staff records (like some supervisors), use username as fallback
                    $userSessionData['first_name'] = $account['username'];
                    $userSessionData['last_name'] = '';
                }
                $staff_stmt->close();

                // Create session using SessionManager
                SessionManager::setUserSession($userSessionData);
                
                // Determine redirect URL - use relative paths to avoid domain issues
                if ($account['role'] == 'supervisor') {
                    sendJsonResponse(['success' => true, 'redirect' => 'modules/supervisor/pages/staff_list.php']);
                } else {
                    sendJsonResponse(['success' => true, 'redirect' => 'modules/staff/pages/dashboard.php']);
                }

            } else {
                // Invalid password - increment failed attempts
                incrementFailedAttempts($conn, $account['account_id']);
                sendJsonResponse(['success' => false, 'message' => 'Invalid username or password!']);
            }
        } else {
            // User not found
            sendJsonResponse(['success' => false, 'message' => 'Invalid username or password!']);
        }
        
    } else {
        sendJsonResponse(['success' => false, 'message' => 'Invalid request method!']);
    }

} catch (Exception $e) {
    // Log the error for debugging
    error_log('Login error: ' . $e->getMessage());
    error_log('Stack trace: ' . $e->getTraceAsString());
    
    ErrorHandler::logError('Login error: ' . $e->getMessage(), [
        'username' => $username ?? 'unknown',
        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
        'post_data' => $_POST ?? []
    ]);
    sendJsonResponse(['success' => false, 'message' => 'Server error occurred. Please try again.']);
}

/**
 * Helper function to increment failed login attempts
 * @param MySQLiCompatibility $conn
 * @param int $accountId
 */
function incrementFailedAttempts($conn, $accountId) {
    $stmt = $conn->prepare("UPDATE account SET failed_login_attempts = failed_login_attempts + 1 WHERE account_id = ?");
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $stmt->close();
    
    // Check if we should lock the account (after 5 failed attempts)
    $checkStmt = $conn->prepare("SELECT failed_login_attempts FROM account WHERE account_id = ?");
    $checkStmt->bind_param("i", $accountId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    $accountData = $checkResult->fetch_assoc();
    $checkStmt->close();
    
    if ($accountData && $accountData['failed_login_attempts'] >= 5) {
        // Lock account for 30 minutes
        $lockStmt = $conn->prepare("UPDATE account SET status = 'locked', locked_until = DATE_ADD(NOW(), INTERVAL 30 MINUTE) WHERE account_id = ?");
        $lockStmt->bind_param("i", $accountId);
        $lockStmt->execute();
        $lockStmt->close();
    }
}
?>

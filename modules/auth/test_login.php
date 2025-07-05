<?php
/**
 * Test Login - Simple version to verify password hashing works
 */

// Include bootstrap for security components
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';

header('Content-Type: application/json');

try {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';
        $modalType = trim($_POST['modalType'] ?? '');

        if (empty($username) || empty($password)) {
            echo json_encode(['success' => false, 'message' => 'Please fill in all fields!']);
            exit;
        }

        // Get database connection
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Check for account with username
        $stmt = $conn->prepare("SELECT account_id, username, password, role, employee_id FROM account WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $account = $result->fetch_assoc();

        if ($account) {
            // Verify password using new hashing
            if (PasswordSecurity::verifyPassword($password, $account['password'])) {
                // Check role authorization for modal type
                if (($modalType == 'supervisor' && $account['role'] != 'supervisor') || 
                    ($modalType == 'staff' && $account['role'] != 'housekeeping_staff')) {
                    echo json_encode(['success' => false, 'message' => 'Invalid username or password!']);
                    exit;
                }

                // Create session using SessionManager
                SessionManager::init();
                SessionManager::setUserSession([
                    'username' => $account['username'],
                    'role' => $account['role'],
                    'account_id' => $account['account_id'],
                    'employee_id' => $account['employee_id']
                ]);

                // Get staff_id for housekeeping staff
                if ($account['role'] == 'housekeeping_staff') {
                    $staff_stmt = $db->prepare("SELECT staff_id FROM staff WHERE employee_id = ?");
                    $staff_stmt->bind_param("s", $account['employee_id']);
                    $staff_stmt->execute();
                    $staff_result = $staff_stmt->get_result();
                    $staff_data = $staff_result->fetch_assoc();
                    
                    if ($staff_data) {
                        // Add staff_id to session
                        $_SESSION['staff_id'] = $staff_data['staff_id'];
                    }
                    $staff_stmt->close();
                }

                // Determine redirect URL
                if ($account['role'] == 'supervisor') {
                    echo json_encode(['success' => true, 'redirect' => 'modules/supervisor/pages/staff_list.php']);
                } else {
                    echo json_encode(['success' => true, 'redirect' => 'modules/staff/pages/dashboard.php']);
                }

            } else {
                echo json_encode(['success' => false, 'message' => 'Invalid username or password!']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid username or password!']);
        }
        
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method!']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}
?>

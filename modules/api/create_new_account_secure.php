<?php
/**
 * Create New Account API
 * Uses new security components and authentication
 */

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate CSRF token
        CSRFProtection::validateRequest();

        // Define validation rules
        $validationRules = [
            'username' => 'required|string|username',
            'password' => 'required|string|password',
            'employee_id' => 'required|string|employee_id'
        ];

        // Validate input
        $validatedData = InputValidator::validate($_POST, $validationRules);
        
        if (!$validatedData) {
            echo InputValidator::getFirstError();
            exit;
        }

        $username = $validatedData['username'];
        $password = $validatedData['password'];
        $employee_id = $validatedData['employee_id'];
        $role = ($employee_id == 'supervisor') ? 'supervisor' : 'housekeeping_staff';

        // Get database connection
        $db = Database::getInstance();
        /** @var MySQLiCompatibility $conn */
        $conn = $db->getConnection();

        $checkEmployeeQuery = "SELECT * FROM staff WHERE employee_id = ?";
        $stmt = $conn->prepare($checkEmployeeQuery);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            echo "Invalid employee ID!";
            exit;
        }

        $staffData = $result->fetch_assoc();
        $email_address = $staffData['email_address'];

        $checkUsernameQuery = "SELECT * FROM account WHERE username = ?";
        $usernameStmt = $conn->prepare($checkUsernameQuery);
        $usernameStmt->bind_param("s", $username);
        $usernameStmt->execute();
        $usernameResult = $usernameStmt->get_result();

        if ($usernameResult->num_rows > 0) {
            echo "Username already exists!";
            exit;
        }

        $checkAccountQuery = "SELECT * FROM account WHERE employee_id = ?";
        $checkStmt = $conn->prepare($checkAccountQuery);
        $checkStmt->bind_param("s", $employee_id);
        $checkStmt->execute();
        $accountResult = $checkStmt->get_result();

        if ($accountResult->num_rows > 0) {
            echo "Employee ID already exists!";
            exit;
        }

        // Hash password
        $hashedPassword = PasswordSecurity::hashPassword($password);

        $insertQuery = "INSERT INTO account (username, password, role, employee_id, email_address) VALUES (?, ?, ?, ?, ?)";
        $insertStmt = $conn->prepare($insertQuery);
        $insertStmt->bind_param("sssss", $username, $hashedPassword, $role, $employee_id, $email_address);

        if ($insertStmt->execute()) {
            echo "Account created successfully!";
        } else {
            echo "Error creating account!";
        }

        $insertStmt->close();
        $checkStmt->close();
        $usernameStmt->close();
        $stmt->close();

    } catch (Exception $e) {
        ErrorHandler::logError('Create account error: ' . $e->getMessage(), [
            'employee_id' => $employee_id ?? 'unknown',
            'username' => $username ?? 'unknown'
        ]);
        echo "An error occurred. Please try again.";
    }
}

<?php
/**
 * Supervisor API - Save Staff Schedule
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Set JSON response headers
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');

try {
    // Check authentication and authorization
    SessionManager::requireAuth();
    SessionManager::requireRole('supervisor');

    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed.');
    }

    // Validate CSRF token (for AJAX requests, token should be in header or POST data)
    if (!empty($_SERVER['HTTP_X_CSRF_TOKEN'])) {
        if (!CSRFProtection::verifyToken($_SERVER['HTTP_X_CSRF_TOKEN'])) {
            throw new Exception('Invalid CSRF token.');
        }
    } else {
        CSRFProtection::validateRequest();
    }

    // Get JSON input
    $rawInput = file_get_contents('php://input');
    if (!$rawInput) {
        throw new Exception('No input data provided.');
    }

    $data = json_decode($rawInput, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data.');
    }

    // Define validation rules
    $validationRules = [
        'staff_id' => 'required|integer|min:1',
        'days' => 'required|string|min:1|max:255',
        'shift_time' => 'required|string|min:1|max:50',
        'location' => 'required|string|min:1|max:255',
        'break_time' => 'required|string|min:1|max:50'
    ];

    // Validate input
    $validatedData = InputValidator::validate($data, $validationRules);
    
    if (!$validatedData) {
        echo json_encode([
            'success' => false,
            'error' => InputValidator::getFirstError()
        ]);
        exit;
    }

    $staff_id = $validatedData['staff_id'];
    $days = $validatedData['days'];
    $shift_time = $validatedData['shift_time'];
    $location = $validatedData['location'];
    $break_time = $validatedData['break_time'];

    // Get database connection
    $db = Database::getInstance();
    /** @var MySQLiCompatibility $conn */
    $conn = $db->getConnection();

    // Check if staff exists
    $staffCheckStmt = $conn->prepare("SELECT staff_id FROM staff WHERE staff_id = ?");
    $staffCheckStmt->bind_param("i", $staff_id);
    $staffCheckStmt->execute();
    $staffCheckResult = $staffCheckStmt->get_result();

    if ($staffCheckResult->num_rows === 0) {
        echo json_encode([
            'success' => false,
            'error' => 'Staff member not found.'
        ]);
        exit;
    }

    $staffCheckStmt->close();

    // Check for existing schedule
    $sql_check = "SELECT * FROM staff_schedule WHERE staff_id = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bind_param("i", $staff_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $existing_schedule = $result_check->fetch_assoc();
        echo json_encode([
            'success' => false,
            'error' => 'There is already an existing schedule for this staff.',
            'schedule' => $existing_schedule
        ]);
    } else {
        // Insert new schedule
        $sql_insert = "INSERT INTO staff_schedule (staff_id, days, shift_time, location, break_time) VALUES (?, ?, ?, ?, ?)";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->bind_param("issss", $staff_id, $days, $shift_time, $location, $break_time);
        
        if ($stmt_insert->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Schedule saved successfully.'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'Failed to save schedule.'
            ]);
        }
        $stmt_insert->close();
    }

    $stmt_check->close();

} catch (Exception $e) {
    ErrorHandler::logError('Save schedule error: ' . $e->getMessage(), [
        'staff_id' => $staff_id ?? 'unknown',
        'user_data' => SessionManager::getCurrentUser()
    ]);
    
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred while saving the schedule.'
    ]);
}
?>
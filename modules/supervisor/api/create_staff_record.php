<?php
/**
 * Supervisor API - Create Staff Record
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication and authorization
SessionManager::requireAuth();
SessionManager::requireRole('supervisor');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate CSRF token
        CSRFProtection::validateRequest();

        // Define validation rules
        $validationRules = [
            'first_name' => 'required|string|min:2|max:50|alpha',
            'last_name' => 'required|string|min:2|max:50|alpha',
            'contact_no' => 'required|string|phone',
            'email_address' => 'required|email|max:255',
            'employee_id' => 'required|string|employee_id',
            'position' => 'required|string|min:2|max:100'
        ];

        // Validate input
        $validatedData = InputValidator::validate($_POST, $validationRules);
        
        if (!$validatedData) {
            SessionManager::setFlashMessage(InputValidator::getFirstError(), 'error');
            header("Location: ../pages/staff_record.php");
            exit();
        }

        $first_name = $validatedData['first_name'];
        $last_name = $validatedData['last_name'];
        $contact_no = $validatedData['contact_no'];
        $email_address = $validatedData['email_address'];
        $employee_id = $validatedData['employee_id'];
        $job_position = $validatedData['position'];

        // Get database connection
        $db = Database::getInstance();
        /** @var MySQLiCompatibility $conn */
        $conn = $db->getConnection();

        // Check if employee_id already exists
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE employee_id = ?");
        $checkStmt->bind_param("s", $employee_id);
        $checkStmt->execute();
        $checkStmt->bind_result($count);
        $checkStmt->fetch();
        $checkStmt->close();

        if ($count > 0) {
            SessionManager::setFlashMessage('Employee ID already exists', 'error');
            header("Location: ../pages/staff_record.php");
            exit();
        }

        // Check if email already exists
        $checkEmailStmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE email_address = ?");
        $checkEmailStmt->bind_param("s", $email_address);
        $checkEmailStmt->execute();
        $checkEmailStmt->bind_result($emailCount);
        $checkEmailStmt->fetch();
        $checkEmailStmt->close();

        if ($emailCount > 0) {
            SessionManager::setFlashMessage('Email address already exists', 'error');
            header("Location: ../pages/staff_record.php");
            exit();
        }

        // Insert new staff record
        $stmt = $conn->prepare("INSERT INTO staff (first_name, last_name, contact_no, email_address, employee_id, position) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $first_name, $last_name, $contact_no, $email_address, $employee_id, $job_position);

        if ($stmt->execute()) {
            SessionManager::setFlashMessage('Staff successfully added', 'success');
        } else {
            SessionManager::setFlashMessage('Error adding staff record', 'error');
        }

        $stmt->close();

    } catch (Exception $e) {
        ErrorHandler::logError('Create staff record error: ' . $e->getMessage(), [
            'employee_id' => $employee_id ?? 'unknown',
            'email' => $email_address ?? 'unknown'
        ]);
        SessionManager::setFlashMessage('An error occurred while creating the staff record', 'error');
    }

    header("Location: ../pages/staff_record.php");
    exit();
} else {
    header("Location: ../pages/staff_record.php");
    exit();
}
?>
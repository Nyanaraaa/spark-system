<?php
/**
 * Submit Supply Request API
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication
SessionManager::requireAuth();
SessionManager::requireRole('housekeeping_staff');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate CSRF token
        CSRFProtection::validateRequest();

        // Define validation rules (array format for validateFields)
        $validationRules = [
            'supplies_id' => ['type' => 'integer', 'min' => 1, 'required' => true],
            'quantity' => ['type' => 'integer', 'min' => 1, 'max' => 999, 'required' => true]
        ];

        // Validate input
        $validatedData = InputValidator::validateFields($_POST, $validationRules);
        if (!$validatedData || !$validatedData['valid']) {
            SessionManager::setFlashMessage(InputValidator::getFirstError(), 'error');
            header("Location: ../pages/request.php");
            exit();
        }

        $supplies_id = $validatedData['fields']['supplies_id']['value'];
        $quantity = $validatedData['fields']['quantity']['value'];

        // Get current user data
        $userData = SessionManager::getCurrentUser();
        if (!$userData || !isset($userData['staff_id'])) {
            SessionManager::setFlashMessage('Session expired. Please log in again.', 'error');
            header("Location: ../pages/request.php");
            exit();
        }

        $staff_id = $userData['staff_id'];
        $employee_id = $userData['employee_id'];
        $full_name = $userData['first_name'] . ' ' . $userData['last_name'];

        // Debug logging
        error_log("=== Supply Request Debug Start ===");
        error_log("POST data: " . print_r($_POST, true));
        error_log("User data: " . print_r($userData, true));
        error_log("Parsed - supplies_id: {$supplies_id}, quantity: {$quantity}, staff_id: {$staff_id}");

        // Get database connection
        $db = Database::getInstance();
        /** @var MySQLiCompatibility $conn */
        $conn = $db->getConnection();


        // Debug supply query
        error_log("Checking supply - ID: {$supplies_id}");

        // Check supply availability
        $supply_stmt = $conn->prepare("SELECT supplies, stocks FROM supplies WHERE supplies_id = ?");
        $supply_stmt->bind_param("i", $supplies_id);
        $supply_stmt->execute();
        $supply_stmt->bind_result($supply_name, $current_stocks);

        if (!$supply_stmt->fetch()) {
            error_log("Supply not found for ID: {$supplies_id}");
            SessionManager::setFlashMessage('Supply not found.', 'error');
            $supply_stmt->close();
            header("Location: ../pages/request.php");
            exit();
        }
        $supply_stmt->close();

        error_log("Supply found - Name: {$supply_name}, Current stock: {$current_stocks}, Requested: {$quantity}");

        // Check if enough stock is available
        if ($current_stocks < $quantity) {
            error_log("Insufficient stock - Available: {$current_stocks}, Requested: {$quantity}");
            SessionManager::setFlashMessage('Not enough stock available. Current stock: ' . $current_stocks, 'error');
            header("Location: ../pages/request.php");
            exit();
        }

        error_log("About to insert request - Staff ID: {$staff_id}, Supply ID: {$supplies_id}, Quantity: {$quantity}");

        // Insert the supply request
        $stmt = $conn->prepare("INSERT INTO requests (staff_id, supplies_id, quantity, status, request_date) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("iii", $staff_id, $supplies_id, $quantity);
        
        if ($stmt->execute()) {
            error_log("Request insert successful - Request inserted for Staff ID: {$staff_id}");
            SessionManager::setFlashMessage('Request submitted successfully and is pending supervisor approval.', 'success');
            
            // Log the successful request
            error_log("Supply request submitted - Staff ID: {$staff_id}, Supply ID: {$supplies_id}, Quantity: {$quantity}");
        } else {
            throw new Exception('Failed to execute request insert: ' . $stmt->error);
        }
        $stmt->close();

    } catch (Exception $e) {
        // Log error details
        error_log("Supply request submission error: " . $e->getMessage());
        error_log("POST data: " . print_r($_POST, true));
        error_log("User data: " . print_r($userData ?? 'not set', true));
        
        SessionManager::setFlashMessage('Failed to submit request. Please try again.', 'error');
    }

    header("Location: ../pages/request.php");
    exit();
}

// Invalid request method
SessionManager::setFlashMessage('Invalid request method.', 'error');
header("Location: ../pages/request.php");
exit();

<?php
/**
 * Supervisor API - Approve/Reject Supply Request
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication and authorization
SessionManager::requireAuth();
SessionManager::requireRole('supervisor');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed.');
    }

    // Validate CSRF token
    CSRFProtection::validateRequest();

    // Define validation rules (array format for validateFields)
    $validationRules = [
        'request_id' => ['type' => 'integer', 'min' => 1, 'required' => true],
        'action' => ['type' => 'text', 'required' => true, 'allowed_values' => ['approve', 'reject']]
    ];

    // Validate input
    $validatedData = InputValidator::validateFields($_POST, $validationRules);
    if (!$validatedData || !$validatedData['valid']) {
        throw new Exception(InputValidator::getFirstError());
    }

    $request_id = $validatedData['fields']['request_id']['value'];
    $action = $validatedData['fields']['action']['value'];

    // Get database connection
    $db = Database::getInstance();
    /** @var MySQLiCompatibility $conn */
    $conn = $db->getConnection();

    $conn->begin_transaction();

    try {
        if ($action === 'approve') {
            // Get request details
            $stmt = $conn->prepare("SELECT staff_id, supplies_id, quantity FROM requests WHERE request_id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$requestData = $result->fetch_assoc()) {
                throw new Exception('Request not found.');
            }
            
            $stmt->close();
            
            $staff_id = $requestData['staff_id'];
            $supplies_id = $requestData['supplies_id'];
            $quantity = $requestData['quantity'];

            // Get staff details
            $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name, employee_id FROM staff WHERE staff_id = ?");
            $stmt->bind_param("i", $staff_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$staffData = $result->fetch_assoc()) {
                throw new Exception('Staff member not found.');
            }
            
            $stmt->close();
            
            $full_name = $staffData['full_name'];
            $employee_id = $staffData['employee_id'];

            // Get supply details
            $stmt = $conn->prepare("SELECT supplies, stocks FROM supplies WHERE supplies_id = ?");
            $stmt->bind_param("i", $supplies_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if (!$supplyData = $result->fetch_assoc()) {
                throw new Exception('Supply item not found.');
            }
            
            $stmt->close();
            
            $supply_name = $supplyData['supplies'];
            $current_stocks = $supplyData['stocks'];

            // Check if enough stock is available
            if ($current_stocks < $quantity) {
                throw new Exception('Not enough stock available to fulfill this request.');
            }

            // Update stock
            $new_stocks = $current_stocks - $quantity;
            $stmt = $conn->prepare("UPDATE supplies SET stocks = ? WHERE supplies_id = ?");
            $stmt->bind_param("ii", $new_stocks, $supplies_id);
            $stmt->execute();
            $stmt->close();

            // Record usage history
            $transaction_date = date('Y-m-d H:i:s');
            $stmt = $conn->prepare(
                "INSERT INTO supplies_usage_history (full_name, employee_id, supplies, quantity, transaction_date, supplies_id) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("sssisi", $full_name, $employee_id, $supply_name, $quantity, $transaction_date, $supplies_id);
            $stmt->execute();
            $stmt->close();

            // Update request status
            $stmt = $conn->prepare("UPDATE requests SET status = 'approved' WHERE request_id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            
            SessionManager::setFlashMessage('Request approved, stock updated, and usage history recorded.', 'success');
            
        } else { // reject
            // Update request status to rejected
            $stmt = $conn->prepare("UPDATE requests SET status = 'rejected' WHERE request_id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            
            SessionManager::setFlashMessage('Request rejected.', 'info');
        }

        // Return success response - redirect to request page
        header("Location: ../pages/request.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    ErrorHandler::logError('Supervisor approve request error: ' . $e->getMessage(), [
        'request_id' => $request_id ?? 'unknown',
        'action' => $action ?? 'unknown',
        'user' => SessionManager::getCurrentUser()['username'] ?? 'unknown'
    ]);
    
    SessionManager::setFlashMessage($e->getMessage(), 'error');
    header("Location: ../pages/request.php");
    exit();
}
?>
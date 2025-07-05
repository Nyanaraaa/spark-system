<?php
/**
 * Supervisor API - Get Pending Supply Requests
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication and authorization
SessionManager::requireAuth();
SessionManager::requireRole('supervisor');

// Set JSON response headers
header('Content-Type: application/json; charset=utf-8');

try {
    // Get database connection
    $db = Database::getInstance();
    /** @var MySQLiCompatibility $conn */
    $conn = $db->getConnection();

    // Get pending requests with staff and supply details
    $stmt = $conn->prepare("
        SELECT r.request_id, r.staff_id, s.first_name, s.last_name, sup.supplies AS supply_name, r.quantity, r.request_date 
        FROM requests r 
        JOIN staff s ON r.staff_id = s.staff_id
        JOIN supplies sup ON r.supplies_id = sup.supplies_id
        WHERE r.status = 'pending'
        ORDER BY r.request_date DESC
    ");
    $stmt->execute();
    $result = $stmt->get_result();

    $requests = [];
    while ($row = $result->fetch_assoc()) {
        $requests[] = [
            'request_id' => (int)$row['request_id'],
            'staff_id' => (int)$row['staff_id'],
            'first_name' => $row['first_name'],
            'last_name' => $row['last_name'],
            'supply_name' => $row['supply_name'],
            'quantity' => (int)$row['quantity'],
            'request_date' => $row['request_date']
        ];
    }

    $stmt->close();

    // Return successful response
    echo json_encode([
        'success' => true,
        'data' => $requests,
        'count' => count($requests)
    ]);

} catch (Exception $e) {
    ErrorHandler::logError('Get pending requests error: ' . $e->getMessage(), [
        'user' => SessionManager::getCurrentUser()['username'] ?? 'unknown'
    ]);
    
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to retrieve pending requests.'
    ]);
}
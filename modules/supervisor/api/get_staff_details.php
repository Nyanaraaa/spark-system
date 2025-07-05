<?php
require_once dirname(__DIR__, 2) . '/includes/bootstrap.php';

header('Content-Type: application/json');

try {
    if (!isset($_GET['staff_id'])) {
        ResponseHelper::error('Staff ID is required');
        exit;
    }

    $staff_id = InputValidator::sanitizeInt($_GET['staff_id']);
    if (!$staff_id) {
        ResponseHelper::error('Invalid staff ID');
        exit;
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();

    $sql = "SELECT * FROM staff WHERE staff_id = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        ResponseHelper::success($staff);
    } else {
        ResponseHelper::error('Staff not found', 404);
    }
    
    $stmt->close();
} catch (Exception $e) {
    ErrorHandler::logError('Error fetching staff details: ' . $e->getMessage(), [
        'staff_id' => $_GET['staff_id'] ?? 'unknown'
    ]);
    ResponseHelper::error('An error occurred while fetching staff details', 500);
}
?>
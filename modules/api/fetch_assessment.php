<?php
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';

header('Content-Type: application/json');

try {
    $report_id = $_GET['report_id'] ?? '';
    
    if (!$report_id) {
        echo json_encode(['error' => 'No report_id provided']);
        exit;
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();

    $stmt = $conn->prepare("SELECT rating, remark, task_completion, attention_to_detail, trash_management, floor_care, organization, created_at AS evaluation_created_at FROM evaluations WHERE report_id = ?");
    $stmt->bind_param("s", $report_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    echo json_encode($data ?: ['error' => 'No assessment found']);
    
} catch (Exception $e) {
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
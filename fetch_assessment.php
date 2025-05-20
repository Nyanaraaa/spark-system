<?php
require 'database.php';

$report_id = $_GET['report_id'] ?? '';
header('Content-Type: application/json');

if (!$report_id) {
    echo json_encode(['error' => 'No report_id provided']);
    exit;
}

// Add created_at to the SELECT to include the evaluation timestamp
$stmt = $conn->prepare("SELECT rating, remark, task_completion, attention_to_detail, trash_management, floor_care, organization, created_at AS evaluation_created_at FROM evaluations WHERE report_id = ?");
$stmt->bind_param("s", $report_id);
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_assoc();

echo json_encode($data ?: []);
?>
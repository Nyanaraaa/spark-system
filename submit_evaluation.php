<?php
require 'database.php';

$employee_id = $_POST['employee_id'] ?? '';
$report_id = $_POST['report_id'] ?? '';
$task_completion = $_POST['task_completion'] ?? '';
$attention_to_detail = $_POST['attention_to_detail'] ?? '';
$trash_management = $_POST['trash_management'] ?? '';
$floor_care = $_POST['floor_care'] ?? '';
$organization = $_POST['organization'] ?? '';
$remark = $_POST['remark'] ?? '';
$total_score = $_POST['total_score'] ?? '';
$rating = $total_score; // Or calculate as needed

// Check if an evaluation already exists for this report
$stmt = $conn->prepare("SELECT evaluation_id FROM evaluations WHERE report_id = ?");
$stmt->bind_param("s", $report_id);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    // Update existing evaluation
    $stmt->close();
    $update = $conn->prepare("UPDATE evaluations SET 
        employee_id=?, 
        task_completion=?, 
        attention_to_detail=?, 
        trash_management=?, 
        floor_care=?, 
        organization=?, 
        remark=?, 
        rating=?, 
        created_at=NOW() 
        WHERE report_id=?");
    $update->bind_param("sssssssss", $employee_id, $task_completion, $attention_to_detail, $trash_management, $floor_care, $organization, $remark, $rating, $report_id);
    $update->execute();
    $update->close();
} else {
    // Insert new evaluation
    $stmt->close();
    $insert = $conn->prepare("INSERT INTO evaluations 
        (employee_id, report_id, task_completion, attention_to_detail, trash_management, floor_care, organization, remark, rating, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
    $insert->bind_param("sssssssss", $employee_id, $report_id, $task_completion, $attention_to_detail, $trash_management, $floor_care, $organization, $remark, $rating);
    $insert->execute();
    $insert->close();
}

// Mark the report as evaluated
$update_report = $conn->prepare("UPDATE progress_reports SET is_evaluated=1 WHERE report_id=?");
$update_report->bind_param("s", $report_id);
$update_report->execute();
$update_report->close();

$conn->close();

session_start();
$_SESSION['message'] = "Evaluation submitted successfully!";
$_SESSION['msg_type'] = "success";
header("Location: supervisorassessment.php");
exit;
?>
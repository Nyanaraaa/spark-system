<?php
require '../../../config/database.php';

$data = json_decode(file_get_contents('php://input'), true);
$staff_id = $data['staff_id'];
$days = $data['days'];
$shift_time = $data['shift_time'];
$location = $data['location'];
$break_time = $data['break_time']; 


$sql_check = "SELECT * FROM staff_schedule WHERE staff_id = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $staff_id);
$stmt_check->execute();
$result_check = $stmt_check->get_result();

if ($result_check->num_rows > 0) {
    $existing_schedule = $result_check->fetch_assoc();
    echo json_encode([
        'error' => 'There is already an existing schedule for this staff.',
        'schedule' => $existing_schedule
    ]);
} else {
    $sql_insert = "INSERT INTO staff_schedule (staff_id, days, shift_time, location, break_time) VALUES (?, ?, ?, ?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("issss", $staff_id, $days, $shift_time, $location, $break_time);
    if ($stmt_insert->execute()) {
        echo json_encode(['success' => 'Schedule saved successfully.']);
    } else {
        echo json_encode(['error' => 'Failed to save schedule.']);
    }
}

$stmt_check->close();
$stmt_insert->close();
$conn->close();
?>
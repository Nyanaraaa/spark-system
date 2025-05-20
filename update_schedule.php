<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $schedule_id = $_POST['schedule_id'];
    $days = $_POST['days'];
    $shift_time = $_POST['shift_time'];
    $location = $_POST['location'];

    
    list($start_time_str, $end_time_str) = explode(" - ", $shift_time);
    $start_time = strtotime($start_time_str); 
    $end_time = strtotime($end_time_str); 

    
    $staffQuery = "SELECT staff_id FROM staff_schedule WHERE schedule_id = ?";
    $stmt = $conn->prepare($staffQuery);
    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();
    $staff_result = $stmt->get_result();
    $staff_id = $staff_result->fetch_assoc()['staff_id'];
    $stmt->close();

    
    $conflictQuery = "SELECT * FROM staff_schedule WHERE staff_id = ? AND days = ? AND schedule_id != ?";
    $stmt = $conn->prepare($conflictQuery);
    $stmt->bind_param("isi", $staff_id, $days, $schedule_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        
        list($existing_start_str, $existing_end_str) = explode(" - ", $row['shift_time']);
        $existing_start = strtotime($existing_start_str); 
        $existing_end = strtotime($existing_end_str); 

        if (($start_time < $existing_end) && ($end_time > $existing_start)) {
            echo json_encode(['success' => false, 'message' => 'Conflict detected: The schedule overlaps']);
            $stmt->close();
            $conn->close();
            exit;
        }
    }

    
    $updateQuery = "UPDATE staff_schedule SET days = ?, shift_time = ?, location = ? WHERE schedule_id = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("sssi", $days, $shift_time, $location, $schedule_id);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Schedule updated successfully!']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update schedule.']);
    }

    $stmt->close();
    $conn->close();
}
?>

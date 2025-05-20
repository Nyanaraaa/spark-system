<?php
include 'database.php';

// Get the staff_id from the query parameter
$staff_id = isset($_GET['staff_id']) ? $_GET['staff_id'] : null;

if ($staff_id) {
    $sql = "SELECT s.schedule_id, s.staff_id, s.days, s.shift_time, s.location, s.break_time, l.building 
            FROM staff_schedule s
            JOIN location l ON s.location = l.location_name
            WHERE s.staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
} else {
    $sql = "SELECT s.schedule_id, s.staff_id, s.days, s.shift_time, s.location, s.break_time, l.building 
            FROM staff_schedule s
            JOIN location l ON s.location = l.location_name";
    $stmt = $conn->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {
    // Add each schedule to the array, including break_time and building
    $schedules[] = [
        'schedule_id' => $row['schedule_id'],
        'staff_id' => $row['staff_id'],
        'days' => $row['days'],
        'shift_time' => $row['shift_time'],
        'location' => $row['location'],
        'break_time' => $row['break_time'], // Include break_time
        'building' => $row['building'] // Include building from the location table
    ];
}

echo json_encode($schedules);

$stmt->close();
$conn->close();
?>

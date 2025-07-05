<?php
require_once '../../../includes/bootstrap.php';

$db = Database::getInstance();

$staff_id = isset($_GET['staff_id']) ? $_GET['staff_id'] : null;

if ($staff_id) {
    $sql = "SELECT s.schedule_id, s.staff_id, s.days, s.shift_time, s.location, s.break_time, l.building 
            FROM staff_schedule s
            JOIN location l ON s.location = l.location_name
            WHERE s.staff_id = ?";
    $stmt = $db->prepare($sql);
    $stmt->bind_param("i", $staff_id);
} else {
    $sql = "SELECT s.schedule_id, s.staff_id, s.days, s.shift_time, s.location, s.break_time, l.building 
            FROM staff_schedule s
            JOIN location l ON s.location = l.location_name";
    $stmt = $db->prepare($sql);
}

$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
while ($row = $result->fetch_assoc()) {

    $schedules[] = [
        'schedule_id' => $row['schedule_id'],
        'staff_id' => $row['staff_id'],
        'days' => $row['days'],
        'shift_time' => $row['shift_time'],
        'location' => $row['location'],
        'break_time' => $row['break_time'],
        'building' => $row['building']
    ];
}

echo json_encode($schedules);

$stmt->close();
$db->close();
?>
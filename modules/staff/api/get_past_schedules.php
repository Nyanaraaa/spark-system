<?php
require '../../../config/database.php';

if (!isset($_GET['staff_id'])) {
    echo json_encode([]);
    exit();
}

$staff_id = $_GET['staff_id'];

$stmt = $conn->prepare("
    SELECT 
        staff_schedule.days, 
        staff_schedule.shift_time, 
        staff_schedule.location, 
        staff_schedule.break_time,
        staff_schedule.created_at,
        location.building
    FROM 
        staff_schedule
    INNER JOIN 
        location 
    ON 
        staff_schedule.location = location.location_name
    WHERE 
        staff_schedule.staff_id = ?
");

$stmt->bind_param("i", $staff_id);
$stmt->execute();
$result = $stmt->get_result();

$schedules = [];
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = $row;
    }
}

echo json_encode($schedules);
$stmt->close();
$conn->close();
?>
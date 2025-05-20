<?php
require 'database.php';

if (!isset($_GET['date']) || !isset($_GET['staff_id'])) {
    echo json_encode([]);
    exit();
}

$date = $_GET['date'];
$staff_id = $_GET['staff_id'];

// Format the provided date (e.g., '2024-12-27') to match the format of the `created_at` field (e.g., '2024-12-27 00:00:00')
$formattedDate = $date . ' 00:00:00';

// Query to fetch schedules for the given staff_id and the created_at date, including the building from the location table
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
        staff_schedule.staff_id = ? AND DATE(staff_schedule.created_at) = DATE(?)
");

$stmt->bind_param("is", $staff_id, $formattedDate);
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

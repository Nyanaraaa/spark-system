<?php
require 'database.php';

// SQL query to fetch all schedules, including position, building, and break_time
$sql = "SELECT staff.first_name, staff.last_name, staff.position, 
               staff_schedule.days, staff_schedule.shift_time, 
               staff_schedule.break_time, staff_schedule.location, 
               location.building 
        FROM staff_schedule
        JOIN staff ON staff_schedule.staff_id = staff.staff_id
        JOIN location ON staff_schedule.location = location.location_name";

$result = $conn->query($sql);

// Initialize an empty array to store schedules
$schedules = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $schedules[] = [
            'staff_name' => $row['first_name'] . ' ' . $row['last_name'],
            'position' => $row['position'], // Include position
            'days' => $row['days'],
            'shift_time' => $row['shift_time'],
            'break_time' => $row['break_time'], // Include break_time
            'location' => $row['location'],
            'building' => $row['building'], // Include building
        ];
    }
}

$conn->close();

// Return the data as a JSON response
echo json_encode($schedules);
?>

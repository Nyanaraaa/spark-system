<?php
header('Content-Type: application/json');
require 'database.php';

try {
    // Query to fetch locations along with the building and assigned staff
    $sql = "
        SELECT 
            location.id AS location_id,
            location.location_name AS location_name,
            location.building AS building,
            GROUP_CONCAT(CONCAT(staff.first_name, ' ', staff.last_name) SEPARATOR ', ') AS assigned_staff
        FROM 
            location
        LEFT JOIN 
            staff_schedule ON location.location_name = staff_schedule.location
        LEFT JOIN 
            staff ON staff.staff_id = staff_schedule.staff_id
        GROUP BY 
            location.id, location.location_name, location.building
    ";

    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $locations = [];

        while ($row = $result->fetch_assoc()) {
            $locations[] = [
                'location_name' => $row['location_name'],
                'building' => $row['building'],
                'assigned_staff' => $row['assigned_staff'] ? explode(', ', $row['assigned_staff']) : [] // Split staff names into an array
            ];
        }

        echo json_encode(['status' => 'success', 'locations' => $locations]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'No locations found.']);
    }
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => 'An error occurred while fetching data.']);
}

$conn->close();
?>

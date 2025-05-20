<?php
include 'database.php';

$sql = "
    SELECT 
        location.id AS location_id, 
        location.location_name AS location_name, 
        location.building AS building,
        COUNT(staff_schedule.location) AS staff_count 
    FROM 
        location
    LEFT JOIN 
        staff_schedule 
    ON 
        location.location_name = staff_schedule.location 
    GROUP BY 
        location.id, location.location_name, location.building
";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $locations = [];

    while ($row = $result->fetch_assoc()) {
        $locations[] = [
            'location_name' => $row['location_name'],
            'building' => $row['building'], // Include the building
            'id' => $row['location_id'], 
            'staff_count' => (int)$row['staff_count']
        ];
    }

    echo json_encode(['status' => 'success', 'locations' => $locations]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No locations found.']);
}

$conn->close(); 
?>

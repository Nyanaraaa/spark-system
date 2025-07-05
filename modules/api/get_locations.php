<?php
// Set content type to JSON
header('Content-Type: application/json');

// Capture any output that might interfere with JSON
ob_start();

try {
    require_once '../../includes/bootstrap.php';
    
    // Initialize session for authentication
    SessionManager::init();

    $db = Database::getInstance();

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

    $result = $db->query($sql);

    if ($result && $result->num_rows > 0) {
        $locations = [];

        while ($row = $result->fetch_assoc()) {
            $locations[] = [
                'location_name' => $row['location_name'],
                'building' => $row['building'],
                'id' => $row['location_id'],
                'staff_count' => (int) $row['staff_count']
            ];
        }

        // Clear any output buffer before sending JSON
        ob_clean();
        echo json_encode(['status' => 'success', 'locations' => $locations]);
    } else {
        // Clear any output buffer before sending JSON
        ob_clean();
        echo json_encode(['status' => 'error', 'message' => 'No locations found.']);
    }
} catch (Exception $e) {
    // Clear any output buffer before sending JSON
    ob_clean();
    echo json_encode(['status' => 'error', 'message' => 'Database error: ' . $e->getMessage()]);
}

// Database connection is managed by the Database singleton
?>
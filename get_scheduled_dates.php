<?php
// Database connection
include 'database.php'; // Replace with your database connection file

// Fetch the staff ID from the query parameters
$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;

// Check if staff_id is valid
if ($staff_id <= 0) {
    echo json_encode([]);
    exit;
}

// Query to get distinct dates with schedules for the specified staff member
$query = "SELECT DISTINCT DATE_FORMAT(created_at, '%Y-%m-%d') AS scheduled_date 
          FROM staff_schedule 
          WHERE staff_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $staff_id);
$stmt->execute();
$result = $stmt->get_result();

// Fetch the results into an array
$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['scheduled_date'];
}

// Output the results as JSON
echo json_encode($dates);

// Close the database connection
$stmt->close();
$conn->close();
?>

<?php
// get_location_id.php
include 'database.php';  // Include your database connection

if (isset($_GET['location'])) {
    $locationName = $_GET['location'];

    // Query to get the location ID based on the location name
    $query = "SELECT id FROM location WHERE location_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $locationName);
    $stmt->execute();
    $stmt->bind_result($locationId);
    $stmt->fetch();

    // Return the location ID
    echo $locationId;

    $stmt->close();
}
?>

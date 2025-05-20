<?php
header('Content-Type: application/json');

include 'database.php';

// Fetch locations
$sql = "SELECT location_name FROM location";
$result = $conn->query($sql);

if ($result && $result->num_rows > 0) {
    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
    echo json_encode($locations);
} else {
    echo json_encode([]);
}

$conn->close();
?>

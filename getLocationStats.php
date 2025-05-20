<?php
header('Content-Type: application/json');
require 'database.php';

$query = "SELECT location, COUNT(*) AS staff_count FROM staff_schedule GROUP BY location";
$result = $conn->query($query);

$locations = [];
while ($row = $result->fetch_assoc()) {
    $locations[] = $row;
}

echo json_encode($locations);
?>

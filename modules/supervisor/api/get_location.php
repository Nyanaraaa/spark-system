<?php
require_once '../../../includes/bootstrap.php';

$db = Database::getInstance();

header('Content-Type: application/json');

$sql = "SELECT location_name FROM location";
$result = $db->query($sql);

if ($result && $result->num_rows > 0) {
    $locations = [];
    while ($row = $result->fetch_assoc()) {
        $locations[] = $row;
    }
    echo json_encode($locations);
} else {
    echo json_encode([]);
}

$db->close();
?>
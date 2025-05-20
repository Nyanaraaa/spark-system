<?php

require_once 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location']);
    $building = trim($_POST['building']);

    if (empty($location)) {
        echo json_encode(['status' => 'error', 'message' => 'Location name cannot be empty.']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO location (location_name, building) VALUES (?, ?)");
    $stmt->bind_param("ss", $location, $building);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Location added successfully.', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add location.']);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>

<?php
require_once '../../../includes/bootstrap.php';

$db = Database::getInstance();

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRFProtection::verifyToken($_POST['csrf_token'] ?? '');
}




if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $location = trim($_POST['location']);
    $building = trim($_POST['building']);

    if (empty($location)) {
        echo json_encode(['status' => 'error', 'message' => 'Location name cannot be empty.']);
        exit;
    }

    $stmt = $db->prepare("INSERT INTO location (location_name, building) VALUES (?, ?)");
    $stmt->bind_param("ss", $location, $building);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Location added successfully.', 'id' => $stmt->insert_id]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to add location.']);
    }

    $stmt->close();
    $db->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}
?>
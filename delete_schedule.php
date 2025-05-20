<?php
require 'database.php';

if (isset($_GET['schedule_id']) && is_numeric($_GET['schedule_id'])) {
    $schedule_id = (int) $_GET['schedule_id'];

    $sql = "DELETE FROM staff_schedule WHERE schedule_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt === false) {
        die('Error preparing the statement: ' . htmlspecialchars($conn->error));
    }

    $stmt->bind_param("i", $schedule_id);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Schedule deleted successfully.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'No schedule found with the provided ID.']);
    }

    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid schedule ID provided.']);
}

$conn->close();
?>

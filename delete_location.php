<?php

require 'database.php';

if (isset($_POST['location_id'])) {
    $locationId = $_POST['location_id'];

    // Begin a transaction
    $conn->begin_transaction();

    try {
        // Fetch the location name
        $stmtFetch = $conn->prepare("SELECT location_name FROM location WHERE id = ?");
        $stmtFetch->bind_param("i", $locationId);
        $stmtFetch->execute();
        $stmtFetch->bind_result($locationName);
        $stmtFetch->fetch();
        $stmtFetch->close();

        if ($locationName) {
            // Delete related records in staff_schedule
            $stmtSchedule = $conn->prepare("DELETE FROM staff_schedule WHERE location = ?");
            $stmtSchedule->bind_param("s", $locationName);
            $stmtSchedule->execute();
            $stmtSchedule->close();

            // Delete the location
            $stmtLocation = $conn->prepare("DELETE FROM location WHERE id = ?");
            $stmtLocation->bind_param("i", $locationId);
            $stmtLocation->execute();
            $stmtLocation->close();

            // Commit the transaction
            $conn->commit();

            echo json_encode(['status' => 'success', 'message' => 'Location and related schedules deleted successfully.']);
        } else {
            throw new Exception('Location not found.');
        }
    } catch (Exception $e) {
        // Rollback transaction in case of error
        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Location ID not provided.']);
}

$conn->close();
?>

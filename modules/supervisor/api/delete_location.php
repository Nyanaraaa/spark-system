<?php
require_once '../../../includes/bootstrap.php';


$db = Database::getInstance();
$conn = $db->getConnection();

// Verify CSRF token for POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    CSRFProtection::verifyToken($_POST['csrf_token'] ?? '');
}


if (isset($_POST['location_id'])) {
    $locationId = $_POST['location_id'];


    $conn->begin_transaction();

    try {

        $stmtFetch = $conn->prepare("SELECT location_name FROM location WHERE id = ?");
        $stmtFetch->bind_param("i", $locationId);
        $stmtFetch->execute();
        $stmtFetch->bind_result($locationName);
        $stmtFetch->fetch();
        $stmtFetch->close();

        if ($locationName) {
            $stmtSchedule = $conn->prepare("DELETE FROM staff_schedule WHERE location = ?");
            $stmtSchedule->bind_param("s", $locationName);
            $stmtSchedule->execute();
            $stmtSchedule->close();

            $stmtLocation = $conn->prepare("DELETE FROM location WHERE id = ?");
            $stmtLocation->bind_param("i", $locationId);
            $stmtLocation->execute();
            $stmtLocation->close();

            $conn->commit();
            echo json_encode(['status' => 'success', 'message' => 'Location and related schedules deleted successfully.']);
        } else {
            throw new Exception('Location not found.');
        }
    } catch (Exception $e) {

        $conn->rollback();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Location ID not provided.']);
}

$conn->close();
?>
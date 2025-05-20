<?php
session_start();
require 'database.php';

if (isset($_GET['supplies_id']) && is_numeric($_GET['supplies_id'])) {
    $supplies_id = intval($_GET['supplies_id']);

    
    $sql = "DELETE FROM supplies WHERE supplies_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $supplies_id);
        if ($stmt->execute()) {
            $_SESSION['success'] = "Supply deleted successfully.";
        } else {
            $_SESSION['error'] = "Failed to delete the supply. Please try again.";
        }
        $stmt->close();
    } else {
        $_SESSION['error'] = "Error preparing the query. Please try again.";
    }
} else {
    $_SESSION['error'] = "Invalid request. No supply ID provided.";
}


$conn->close();

header("Location: supervisorupdate.php");
exit();

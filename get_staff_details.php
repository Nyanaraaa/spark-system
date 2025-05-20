<?php
require 'database.php';

if (isset($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];
    
    // Query to fetch staff details
    $sql = "SELECT * FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $staff = $result->fetch_assoc();
        echo json_encode($staff); // Return data as JSON
    } else {
        echo json_encode(["error" => "Staff not found"]);
    }
    $stmt->close();
    $conn->close();
}
?>

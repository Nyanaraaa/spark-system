<?php
session_start();
require 'database.php';

// Ensure the user is a supervisor
if ($_SESSION['role'] != 'supervisor') {
    header("Location: index.php");
    exit();
}

// Fetch pending requests with supply names
$stmt = $conn->prepare("
    SELECT r.request_id, r.staff_id, s.first_name, s.last_name, sup.supplies AS supply_name, r.quantity, r.request_date 
    FROM requests r 
    JOIN staff s ON r.staff_id = s.staff_id
    JOIN supplies sup ON r.supplies_id = sup.supplies_id
    WHERE r.status = 'pending'
");
$stmt->execute();
$result = $stmt->get_result();

$requests = [];
while ($row = $result->fetch_assoc()) {
    $requests[] = $row;
}

// Return the data as JSON
echo json_encode($requests);
?>

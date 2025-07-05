<?php
require_once '../../../includes/bootstrap.php';

$db = Database::getInstance();


$staff_id = isset($_GET['staff_id']) ? intval($_GET['staff_id']) : 0;


if ($staff_id <= 0) {
    echo json_encode([]);
    exit;
}

$query = "SELECT days AS scheduled_date 
          FROM staff_schedule 
          WHERE staff_id = ?";
$stmt = $db->prepare($query);
$stmt->bind_param('i', $staff_id);
$stmt->execute();
$result = $stmt->get_result();


$dates = [];
while ($row = $result->fetch_assoc()) {
    $dates[] = $row['scheduled_date'];
}


echo json_encode($dates);


$stmt->close();
$db->close();
?>
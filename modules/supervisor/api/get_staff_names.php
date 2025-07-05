<?php
require_once '../../../includes/bootstrap.php';

$db = Database::getInstance();

header('Content-Type: application/json');

$sql = "SELECT staff_id, first_name, last_name FROM staff";
$result = $db->query($sql);

$staff = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $staff[] = [
            'staff_id' => $row['staff_id'],
            'name' => $row['first_name'] . ' ' . $row['last_name'],
        ];
    }
}

echo json_encode($staff);

$db->close();
?>
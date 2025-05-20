<?php
header('Content-Type: application/json');

include 'database.php';

$sql = "SELECT staff_id, first_name, last_name FROM staff";
$result = $conn->query($sql);

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

$conn->close();
?>

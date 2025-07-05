<?php

require '../../../config/database.php';


if (isset($_GET['staff_id']) && is_numeric($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];


    $sql = "SELECT first_name, last_name, profile_picture, email_address, contact_no, employee_id FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $staff = $result->fetch_assoc();


        if (empty($staff['profile_picture']) || !file_exists('../../../assets/images/uploads/' . $staff['profile_picture'])) {
            $staff['profile_picture'] = 'default.png';
        }


        echo json_encode($staff);
    } else {

        echo json_encode(['error' => 'No staff found']);
    }


    $stmt->close();
    $conn->close();
} else {

    echo json_encode(['error' => 'Invalid staff ID']);
}
?>
<?php
// Include database connection file
require 'database.php';

// Check if staff_id is set and valid
if (isset($_GET['staff_id']) && is_numeric($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];

    // Query to fetch staff profile details
    $sql = "SELECT first_name, last_name, profile_picture, email_address, contact_no, employee_id FROM staff WHERE staff_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Fetch the staff data
        $staff = $result->fetch_assoc();

        // Check if profile_picture is empty or the file doesn't exist
        if (empty($staff['profile_picture']) || !file_exists('uploads/' . $staff['profile_picture'])) {
            $staff['profile_picture'] = 'default.png';
        }

        // Send the data as a JSON response
        echo json_encode($staff);
    } else {
        // Send an error if no staff found
        echo json_encode(['error' => 'No staff found']);
    }

    // Close the database connection
    $stmt->close();
    $conn->close();
} else {
    // Send an error if no staff_id is provided
    echo json_encode(['error' => 'Invalid staff ID']);
}
?>

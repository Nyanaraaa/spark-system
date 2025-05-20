<?php

require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Retrieve form inputs
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_no = $_POST['contact_no'];
    $email_address = $_POST['email_address'];
    $employee_id = $_POST['employee_id'];
    $job_position = $_POST['position']; // New field

    // Check if the employee ID already exists
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE employee_id = ?");
    $checkStmt->bind_param("s", $employee_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        // Redirect with an error message if the employee ID already exists
        header("Location: staff.php?message=Employee ID already exists&status=danger");
        exit();
    }

    // Prepare and bind the SQL statement for inserting
    $stmt = $conn->prepare("INSERT INTO staff (first_name, last_name, contact_no, email_address, employee_id, position) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $contact_no, $email_address, $employee_id, $job_position);

    // Execute the query
    if ($stmt->execute()) {
        // Redirect with success message
        header("Location: staff.php?message=Staff successfully added&status=success");
        exit();
    } else {
        // Redirect with error message
        header("Location: staff.php?message=Error: " . urlencode($stmt->error) . "&status=danger");
        exit();
    }

    // Close the statement and connection
    $stmt->close();
    $conn->close();
} else {
    // Redirect if accessed without POST method
    header("Location: staff.php");
    exit();
}
?>

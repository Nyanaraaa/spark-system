<?php

require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_no = $_POST['contact_no'];
    $email_address = $_POST['email_address'];
    $employee_id = $_POST['employee_id'];
    $job_position = $_POST['position']; 

    
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM staff WHERE employee_id = ?");
    $checkStmt->bind_param("s", $employee_id);
    $checkStmt->execute();
    $checkStmt->bind_result($count);
    $checkStmt->fetch();
    $checkStmt->close();

    if ($count > 0) {
        
        header("Location: ../pages/staff_record.php?message=Employee ID already exists&status=danger");
        exit();
    }

    
    $stmt = $conn->prepare("INSERT INTO staff (first_name, last_name, contact_no, email_address, employee_id, position) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $first_name, $last_name, $contact_no, $email_address, $employee_id, $job_position);

    
    if ($stmt->execute()) {
        
        header("Location: ../pages/staff_record.php?message=Staff successfully added&status=success");
        $stmt->close();
        $conn->close();
        exit();
    } else {
        
        header("Location: ../pages/staff_record.php?message=Error: " . urlencode($stmt->error) . "&status=danger");
        $stmt->close();
        $conn->close();
        exit();
    }
} else {
    
    header("Location: ../pages/staff_record.php");
    exit();
}
?>
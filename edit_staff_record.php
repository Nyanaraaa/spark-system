<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $staff_id = $_POST['staff_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_no = $_POST['contact_no'];
    $email_address = $_POST['email_address'];
    $employee_id = $_POST['employee_id'];
    $job_position = $_POST['position']; // New field
  
    
    $sql1 = "UPDATE staff SET first_name = ?, last_name = ?, contact_no = ?, email_address = ?, employee_id = ?, position = ? WHERE staff_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("ssssssi", $first_name, $last_name, $contact_no, $email_address, $employee_id, $job_position, $staff_id);

    
    $sql2 = "UPDATE account SET email_address = ?, employee_id = ? WHERE employee_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("ssi", $email_address, $employee_id, $employee_id);
    
   
    if ($stmt1->execute() && $stmt2->execute()) {
        header("Location: staff.php?message=Record updated successfully&status=success");
    } else {
        header("Location: staff.php?message=Error updating record&status=danger");
    }
    
   
    $stmt1->close();
    $stmt2->close();
    $conn->close();
}
?>

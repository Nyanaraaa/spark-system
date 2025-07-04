<?php
require '../../../config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $staff_id = $_POST['staff_id'];
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $contact_no = $_POST['contact_no'];
    $email_address = $_POST['email_address'];
    $employee_id = $_POST['employee_id'];
    $job_position = $_POST['position']; // New field
  
    // Get the original employee_id to update the account table
    $getOriginalId = "SELECT employee_id FROM staff WHERE staff_id = ?";
    $stmtOriginal = $conn->prepare($getOriginalId);
    $stmtOriginal->bind_param("i", $staff_id);
    $stmtOriginal->execute();
    $stmtOriginal->bind_result($original_employee_id);
    $stmtOriginal->fetch();
    $stmtOriginal->close();
    
    // Update staff table
    $sql1 = "UPDATE staff SET first_name = ?, last_name = ?, contact_no = ?, email_address = ?, employee_id = ?, position = ? WHERE staff_id = ?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("ssssssi", $first_name, $last_name, $contact_no, $email_address, $employee_id, $job_position, $staff_id);

    // Update account table using the original employee_id
    $sql2 = "UPDATE account SET email_address = ?, employee_id = ? WHERE employee_id = ?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sss", $email_address, $employee_id, $original_employee_id);
    
   
    if ($stmt1->execute() && $stmt2->execute()) {
        header("Location: ../pages/staff_record.php?message=Record updated successfully&status=success");
    } else {
        header("Location: ../pages/staff_record.php?message=Error updating record&status=danger");
    }
    
   
    $stmt1->close();
    $stmt2->close();
    $conn->close();
}
?>

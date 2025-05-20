<?php
require 'database.php';

if (isset($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];

    
    $conn->begin_transaction();

    try {
        
        $getEmployeeId = "SELECT employee_id FROM staff WHERE staff_id = ?";
        $stmt = $conn->prepare($getEmployeeId);
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $stmt->bind_result($employee_id);
        $stmt->fetch();
        $stmt->close();

        if ($employee_id) {
            
            $deleteStaff = "DELETE FROM staff WHERE staff_id = ?";
            $stmtStaff = $conn->prepare($deleteStaff);
            $stmtStaff->bind_param('i', $staff_id);
            $stmtStaff->execute();

            
            $deleteAccount = "DELETE FROM account WHERE employee_id = ?";
            $stmtAccount = $conn->prepare($deleteAccount);
            $stmtAccount->bind_param('s', $employee_id); 
            $stmtAccount->execute();

           
            $conn->commit();

            
            header("Location: staff.php?message=Staff+and+account+deleted+successfully");
            exit();
        } else {
            throw new Exception("Employee ID not found for staff_id: $staff_id");
        }

    } catch (Exception $e) {
       
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }

    $conn->close();
} else {
    echo "Invalid Request";
}
?>

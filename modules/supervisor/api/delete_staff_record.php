<?php
require_once '../../../includes/bootstrap.php';

$db = Database::getInstance();

if (isset($_GET['staff_id'])) {
    $staff_id = $_GET['staff_id'];


    $db->begin_transaction();

    try {

        $getEmployeeId = "SELECT employee_id FROM staff WHERE staff_id = ?";
        $stmt = $db->prepare($getEmployeeId);
        $stmt->bind_param('i', $staff_id);
        $stmt->execute();
        $stmt->bind_result($employee_id);
        $stmt->fetch();
        $stmt->close();

        if ($employee_id) {

            $deleteStaff = "DELETE FROM staff WHERE staff_id = ?";
            $stmtStaff = $db->prepare($deleteStaff);
            $stmtStaff->bind_param('i', $staff_id);
            $stmtStaff->execute();


            $deleteAccount = "DELETE FROM account WHERE employee_id = ?";
            $stmtAccount = $db->prepare($deleteAccount);
            $stmtAccount->bind_param('s', $employee_id);
            $stmtAccount->execute();


            $db->commit();


            header("Location: ../pages/staff_record.php?message=Staff+and+account+deleted+successfully");
            exit();
        } else {
            throw new Exception("Employee ID not found for staff_id: $staff_id");
        }

    } catch (Exception $e) {

        $db->rollback();
        echo "Error: " . $e->getMessage();
    }

    $db->close();
} else {
    echo "Invalid Request";
}
?>
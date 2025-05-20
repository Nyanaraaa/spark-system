<?php
session_start();
require 'database.php';

// Set the desired time zone (for example, UTC+8 or your specific time zone)
date_default_timezone_set('Asia/Manila'); // Update to your desired time zone

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $request_id = $_POST['request_id'];
    $action = $_POST['action'];

    if ($action == 'approve') {
        // Fetch request details
        $stmt = $conn->prepare("SELECT staff_id, supplies_id, quantity FROM requests WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->bind_result($staff_id, $supplies_id, $quantity);
        $stmt->fetch();
        $stmt->close();

        // Fetch staff full name and employee ID
        $stmt = $conn->prepare("SELECT CONCAT(first_name, ' ', last_name) AS full_name, employee_id FROM staff WHERE staff_id = ?");
        $stmt->bind_param("i", $staff_id);
        $stmt->execute();
        $stmt->bind_result($full_name, $employee_id);
        $stmt->fetch();
        $stmt->close();

        // Fetch the supply name using supplies_id
        $stmt = $conn->prepare("SELECT supplies FROM supplies WHERE supplies_id = ?");
        $stmt->bind_param("i", $supplies_id);
        $stmt->execute();
        $stmt->bind_result($supply_name);
        $stmt->fetch();
        $stmt->close();

        // Check current stock
        $stmt = $conn->prepare("SELECT stocks FROM supplies WHERE supplies_id = ?");
        $stmt->bind_param("i", $supplies_id);
        $stmt->execute();
        $stmt->bind_result($current_stocks);
        $stmt->fetch();
        $stmt->close();

        if ($current_stocks >= $quantity) {
            // Deduct stock
            $new_stocks = $current_stocks - $quantity;
            $update_stmt = $conn->prepare("UPDATE supplies SET stocks = ? WHERE supplies_id = ?");
            $update_stmt->bind_param("ii", $new_stocks, $supplies_id);
            $update_stmt->execute();
            $update_stmt->close();

            // Insert into supplies_usage_history with the approval timestamp
            $transaction_date = date('Y-m-d H:i:s'); // Current date and time (approval time) in the correct time zone
            $history_stmt = $conn->prepare(
                "INSERT INTO supplies_usage_history (full_name, employee_id, supplies, quantity, transaction_date, supplies_id) 
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $history_stmt->bind_param("sssisi", $full_name, $employee_id, $supply_name, $quantity, $transaction_date, $supplies_id);
            $history_stmt->execute();
            $history_stmt->close();

            // Update request status to 'approved'
            $stmt = $conn->prepare("UPDATE requests SET status = 'approved' WHERE request_id = ?");
            $stmt->bind_param("i", $request_id);
            $stmt->execute();
            $stmt->close();

            $_SESSION['message'] = 'Request approved, stock updated, and usage history recorded.';
            $_SESSION['alert_type'] = 'success';
        } else {
            $_SESSION['message'] = 'Not enough stock available to fulfill this request.';
            $_SESSION['alert_type'] = 'danger';
        }
    } else {
        // Update request status to 'rejected'
        $stmt = $conn->prepare("UPDATE requests SET status = 'rejected' WHERE request_id = ?");
        $stmt->bind_param("i", $request_id);
        $stmt->execute();
        $stmt->close();

        $_SESSION['message'] = 'Request rejected.';
        $_SESSION['alert_type'] = 'danger';
    }

    header("Location: supervisor_request.php");
    exit();
}
?>

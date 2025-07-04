<?php
session_start();
require 'config/database.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    $supplies_id = $_POST['supplies_id'];
    $quantity = $_POST['quantity'];
    $staff_id = $_SESSION['staff_id'] ?? null; 

    
    if (!$staff_id) {
        $_SESSION['message'] = 'User not logged in.';
        $_SESSION['alert_type'] = 'danger';
        header("Location: housekeeping_request.php");
        exit();
    }

    
    $stmt = $conn->prepare("SELECT first_name, last_name, employee_id FROM staff WHERE staff_id = ?");
    $stmt->bind_param("i", $staff_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name, $employee_id);
    
    if (!$stmt->fetch()) {
        $_SESSION['message'] = 'User not found.';
        $_SESSION['alert_type'] = 'danger';
        $stmt->close();
        header("Location: housekeeping_request.php");
        exit();
    }
    $stmt->close();

    $full_name = $first_name . ' ' . $last_name;

    
    $supply_stmt = $conn->prepare("SELECT supplies, stocks FROM supplies WHERE supplies_id = ?");
    $supply_stmt->bind_param("i", $supplies_id);
    $supply_stmt->execute();
    $supply_stmt->bind_result($supply_name, $current_stocks);
    
    if (!$supply_stmt->fetch()) {
        $_SESSION['message'] = 'Supply not found.';
        $_SESSION['alert_type'] = 'danger';
        $supply_stmt->close();
        header("Location: housekeeping_request.php");
        exit();
    }
    $supply_stmt->close();

   
    if ($current_stocks >= $quantity) {
        
        $stmt = $conn->prepare("INSERT INTO requests (staff_id, supplies_id, quantity, status, request_date) VALUES (?, ?, ?, 'pending', NOW())");
        $stmt->bind_param("iii", $staff_id, $supplies_id, $quantity);
        if ($stmt->execute()) {
            $_SESSION['message'] = 'Request is pending supervisor approval.';
            $_SESSION['alert_type'] = 'warning';
        } else {
            $_SESSION['message'] = 'Failed to submit request for approval.';
            $_SESSION['alert_type'] = 'danger';
        }
        $stmt->close();
    } else {
        $_SESSION['message'] = 'Not enough stock available.';
        $_SESSION['alert_type'] = 'danger';
    }

    $conn->close();

    
    header("Location: housekeeping_request.php");
    exit();
}

<?php
require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $supply_name = $_POST['supply_name'];
    $category = $_POST['category'];
    $classification = $_POST['classification'];
    $initial_stock = $_POST['initial_stock'];
    $stock_limit = $_POST['stock_limit']; // Get user-defined limit

    $sql = "INSERT INTO supplies (supplies, brand, classification, stocks, stock_limit) VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssii", $supply_name, $category, $classification, $initial_stock, $stock_limit);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Supply added successfully!";
    } else {
        $_SESSION['error'] = "Error adding supply.";
    }

    $stmt->close();
    $conn->close();

    header("Location: supervisorupdate.php");
    exit();
}
?>


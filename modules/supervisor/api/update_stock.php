<?php

require '../../../config/database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();

    if (isset($_POST['supplies_id']) && isset($_POST['stocks']) && isset($_POST['stock_limit'])) {

        $supplies_id = intval($_POST['supplies_id']);
        $stocks = intval($_POST['stocks']);
        $stock_limit = intval($_POST['stock_limit']);

        
        $fetch_sql = "SELECT stocks FROM supplies WHERE supplies_id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param('i', $supplies_id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_stock = intval($row['stocks']);

            
            if ($current_stock + $stocks > $stock_limit) {
                $_SESSION['error'] = "Stock update failed. Total stock cannot exceed the limit of $stock_limit.";
            } else {
                
                $update_sql = "UPDATE supplies SET stocks = stocks + ?, stock_limit = ? WHERE supplies_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('iii', $stocks, $stock_limit, $supplies_id);

                if ($update_stmt->execute()) {
                    $_SESSION['success'] = "Stock and limit updated successfully.";
                } else {
                    $_SESSION['error'] = "Error: " . $update_stmt->error;
                }

                $update_stmt->close();
            }
        } else {
            $_SESSION['error'] = "Invalid supplies ID.";
        }

        $fetch_stmt->close();
    } else {
        $_SESSION['error'] = "Required fields are missing.";
    }
}

$conn->close();
header("Location: ../pages/manage_supplies.php");
exit();

?>
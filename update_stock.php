<?php

require 'database.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    session_start();

    if (isset($_POST['supplies_id']) && isset($_POST['stocks']) && isset($_POST['stock_limit'])) {
        
        $supplies_id = intval($_POST['supplies_id']);
        $stocks = intval($_POST['stocks']);
        $stock_limit = intval($_POST['stock_limit']);

        // Fetch current stock and stock limit from the database
        $fetch_sql = "SELECT stocks FROM supplies WHERE supplies_id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param('i', $supplies_id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_stock = intval($row['stocks']);

            // Check if new stock exceeds the updated stock limit
            if ($current_stock + $stocks > $stock_limit) {
                $_SESSION['error'] = "Stock update failed. Total stock cannot exceed the limit of $stock_limit.";
            } else {
                // Update the stock and stock limit
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
header("Location: supervisorupdate.php");
exit();

?>

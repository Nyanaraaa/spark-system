<?php
/**
 * Supervisor API - Update Supply Stock
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication and authorization
SessionManager::requireAuth();
SessionManager::requireRole('supervisor');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Validate CSRF token
        CSRFProtection::validateRequest();

        if (isset($_POST['supplies_id']) && isset($_POST['stocks']) && isset($_POST['stock_limit'])) {

            $supplies_id = intval($_POST['supplies_id']);
            $stocks = intval($_POST['stocks']);
            $stock_limit = intval($_POST['stock_limit']);

            // Get database connection
            $db = Database::getInstance();
            /** @var MySQLiCompatibility $conn */
            $conn = $db->getConnection();

        
        $fetch_sql = "SELECT stocks FROM supplies WHERE supplies_id = ?";
        $fetch_stmt = $conn->prepare($fetch_sql);
        $fetch_stmt->bind_param('i', $supplies_id);
        $fetch_stmt->execute();
        $result = $fetch_stmt->get_result();

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $current_stock = intval($row['stocks']);

            
            if ($current_stock + $stocks > $stock_limit) {
                SessionManager::setFlashMessage("Stock update failed. Total stock cannot exceed the limit of $stock_limit.", 'error');
            } else {
                
                $update_sql = "UPDATE supplies SET stocks = stocks + ?, stock_limit = ? WHERE supplies_id = ?";
                $update_stmt = $conn->prepare($update_sql);
                $update_stmt->bind_param('iii', $stocks, $stock_limit, $supplies_id);

                if ($update_stmt->execute()) {
                    SessionManager::setFlashMessage("Stock and limit updated successfully.", 'success');
                } else {
                    SessionManager::setFlashMessage("Error: " . $update_stmt->error, 'error');
                }

                $update_stmt->close();
            }
        } else {
            SessionManager::setFlashMessage("Invalid supplies ID.", 'error');
        }

        $fetch_stmt->close();
    } else {
        SessionManager::setFlashMessage("Required fields are missing.", 'error');
    }

    } catch (Exception $e) {
        ErrorHandler::logError('Update stock error: ' . $e->getMessage(), [
            'user' => SessionManager::getCurrentUser()['username'] ?? 'unknown'
        ]);
        SessionManager::setFlashMessage('An error occurred while updating stock.', 'error');
    }
}

header("Location: ../pages/manage_supplies.php");
exit();
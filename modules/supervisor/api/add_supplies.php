<?php
/**
 * Supervisor API - Add New Supply
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

        // Define validation rules
        $validationRules = [
            'supply_name' => 'required|string|min:2|max:100',
            'category' => 'required|string|min:2|max:50',
            'classification' => 'required|string|min:2|max:50',
            'initial_stock' => 'required|integer|min:0|max:9999',
            'stock_limit' => 'required|integer|min:0|max:9999'
        ];

        // Validate input
        $validatedData = InputValidator::validate($_POST, $validationRules);
        
        if (!$validatedData) {
            SessionManager::setFlashMessage(InputValidator::getFirstError(), 'error');
            header("Location: ../pages/manage_supplies.php");
            exit();
        }

        $supply_name = $validatedData['supply_name'];
        $category = $validatedData['category'];
        $classification = $validatedData['classification'];
        $initial_stock = $validatedData['initial_stock'];
        $stock_limit = $validatedData['stock_limit'];

        // Business logic validation
        if ($stock_limit > 0 && $initial_stock > $stock_limit) {
            SessionManager::setFlashMessage('Initial stock cannot exceed stock limit.', 'error');
            header("Location: ../pages/manage_supplies.php");
            exit();
        }

        // Get database connection
        $db = Database::getInstance();
        /** @var MySQLiCompatibility $conn */
        $conn = $db->getConnection();

        $sql = "INSERT INTO supplies (supplies, brand, classification, stocks, stock_limit) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $supply_name, $category, $classification, $initial_stock, $stock_limit);

        if ($stmt->execute()) {
            SessionManager::setFlashMessage("Supply added successfully!", 'success');
        } else {
            SessionManager::setFlashMessage("Error adding supply.", 'error');
        }

        $stmt->close();

    } catch (Exception $e) {
        ErrorHandler::logError('Add supply error: ' . $e->getMessage(), [
            'user' => SessionManager::getCurrentUser()['username'] ?? 'unknown'
        ]);
        SessionManager::setFlashMessage('An error occurred while adding supply.', 'error');
    }

    header("Location: ../pages/manage_supplies.php");
    exit();
}
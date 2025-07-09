<?php
// Start output buffering to catch any unexpected output
ob_start();

require_once '../includes/bootstrap.php';

header('Content-Type: application/json');

try {
    // Generate and return fresh CSRF token
    $token = CSRFProtection::generateToken();
    
    // Clear any buffered output
    ob_clean();
    
    echo json_encode(['token' => $token]);
} catch (Exception $e) {
    // Clear any buffered output
    ob_clean();
    
    // Log the error
    error_log('CSRF token generation error: ' . $e->getMessage());
    
    echo json_encode(['error' => 'Failed to generate token']);
}
?>
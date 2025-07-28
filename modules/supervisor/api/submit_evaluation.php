<?php
/**
 * Supervisor API - Submit Staff Evaluation
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication and authorization
SessionManager::requireAuth();
SessionManager::requireRole('supervisor');

try {
    // Validate request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Only POST method allowed.');
    }

    // Validate CSRF token
    CSRFProtection::validateRequest();

    // Map string ratings to integers for criteria fields
    $criteriaMap = [
        'Excellent' => 4,
        'Good' => 3,
        'Needs Improvement' => 2,
        'Unsatisfactory' => 1
    ];
    $criteriaFields = ['task_completion', 'attention_to_detail', 'trash_management', 'floor_care', 'organization'];
    foreach ($criteriaFields as $field) {
        if (isset($_POST[$field]) && !is_numeric($_POST[$field])) {
            $val = trim($_POST[$field]);
            if (isset($criteriaMap[$val])) {
                $_POST[$field] = $criteriaMap[$val];
            }
        }
    }

    // Define validation rules
    $validationRules = [
        'employee_id' => ['type' => 'employee_id', 'required' => true],
        'report_id' => ['type' => 'text', 'required' => true, 'min_length' => 1, 'max_length' => 50],
        'task_completion' => ['type' => 'integer', 'required' => true, 'min' => 1, 'max' => 5],
        'attention_to_detail' => ['type' => 'integer', 'required' => true, 'min' => 1, 'max' => 5],
        'trash_management' => ['type' => 'integer', 'required' => true, 'min' => 1, 'max' => 5],
        'floor_care' => ['type' => 'integer', 'required' => true, 'min' => 1, 'max' => 5],
        'organization' => ['type' => 'integer', 'required' => true, 'min' => 1, 'max' => 5],
        'remark' => ['type' => 'text', 'required' => false, 'max_length' => 500],
        // 'total_score' removed; will be calculated on the server
    ];

    // Validate input
    $validationResult = InputValidator::validateFields($_POST, $validationRules);
    if (!$validationResult['valid']) {
        // Get first error message
        $firstError = 'Invalid input.';
        foreach ($validationResult['fields'] as $field => $result) {
            if (!$result['valid']) {
                $firstError = $result['message'];
                break;
            }
        }
        throw new Exception($firstError);
    }

    $fields = $validationResult['fields'];
    $employee_id = $fields['employee_id']['value'];
    $report_id = (int)$fields['report_id']['value'];
    $task_completion = (int)$fields['task_completion']['value'];
    $attention_to_detail = (int)$fields['attention_to_detail']['value'];
    $trash_management = (int)$fields['trash_management']['value'];
    $floor_care = (int)$fields['floor_care']['value'];
    $organization = (int)$fields['organization']['value'];
    $remark = $fields['remark']['value'] ?? '';

    // Calculate total_score as a percentage (out of 20, then * 100)
    $raw_score = (int)$task_completion + (int)$attention_to_detail + (int)$trash_management + (int)$floor_care + (int)$organization;
    $max_score = 20;
    $total_score = round(($raw_score / $max_score) * 100);

    // Get database connection
    $db = Database::getInstance();
    /** @var MySQLiCompatibility $conn */
    $conn = $db->getConnection();

    $conn->begin_transaction();

    try {
        // Check if evaluation already exists for this report
        $stmt = $conn->prepare("SELECT evaluation_id FROM evaluations WHERE report_id = ?");
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $exists = $result->num_rows > 0;
        $stmt->close();

        if ($exists) {
            // Update existing evaluation
            $stmt = $conn->prepare("UPDATE evaluations SET 
                employee_id = ?, 
                task_completion = ?, 
                attention_to_detail = ?, 
                trash_management = ?, 
                floor_care = ?, 
                organization = ?, 
                remark = ?, 
                rating = ?, 
                created_at = NOW() 
                WHERE report_id = ?");
            $stmt->bind_param("siiiiisisi", $employee_id, $task_completion, $attention_to_detail, 
                $trash_management, $floor_care, $organization, $remark, $total_score, $report_id);
        } else {
            // Insert new evaluation
            $stmt = $conn->prepare("INSERT INTO evaluations 
                (employee_id, report_id, task_completion, attention_to_detail, trash_management, floor_care, organization, remark, rating, report_image, description, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $emptyImage = '';
            $emptyDescription = '';
            $stmt->bind_param("siiiiisisss", $employee_id, $report_id, $task_completion, $attention_to_detail, 
                $trash_management, $floor_care, $organization, $remark, $total_score, $emptyImage, $emptyDescription);
        }

        if (!$stmt->execute()) {
            $sqlError = $stmt->error;
            $stmt->close();
            throw new Exception('Failed to save evaluation: ' . $sqlError);
        }
        $stmt->close();

        // Mark report as evaluated
        $stmt = $conn->prepare("UPDATE progress_reports SET is_evaluated = 1 WHERE report_id = ?");
        $stmt->bind_param("i", $report_id);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        
        SessionManager::setFlashMessage('Evaluation submitted successfully!', 'success');
        
        header("Location: ../pages/assessment.php");
        exit();

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    ErrorHandler::logError('Supervisor submit evaluation error: ' . $e->getMessage(), [
        'employee_id' => $employee_id ?? 'unknown',
        'report_id' => $report_id ?? 'unknown',
        'user' => SessionManager::getCurrentUser()['username'] ?? 'unknown'
    ]);
    
    SessionManager::setFlashMessage($e->getMessage(), 'error');
    header("Location: ../pages/assessment.php");
    exit();
}
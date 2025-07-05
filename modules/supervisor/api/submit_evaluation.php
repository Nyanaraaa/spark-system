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

    // Define validation rules
    $validationRules = [
        'employee_id' => 'required|string|employee_id',
        'report_id' => 'required|string|min:1|max:50',
        'task_completion' => 'required|integer|min:1|max:5',
        'attention_to_detail' => 'required|integer|min:1|max:5',
        'trash_management' => 'required|integer|min:1|max:5',
        'floor_care' => 'required|integer|min:1|max:5',
        'organization' => 'required|integer|min:1|max:5',
        'remark' => 'string|max:500',
        'total_score' => 'required|integer|min:5|max:25'
    ];

    // Validate input
    $validatedData = InputValidator::validate($_POST, $validationRules);
    
    if (!$validatedData) {
        throw new Exception(InputValidator::getFirstError());
    }

    $employee_id = $validatedData['employee_id'];
    $report_id = $validatedData['report_id'];
    $task_completion = $validatedData['task_completion'];
    $attention_to_detail = $validatedData['attention_to_detail'];
    $trash_management = $validatedData['trash_management'];
    $floor_care = $validatedData['floor_care'];
    $organization = $validatedData['organization'];
    $remark = $validatedData['remark'] ?? '';
    $total_score = $validatedData['total_score'];

    // Verify total score calculation
    $calculated_total = $task_completion + $attention_to_detail + $trash_management + $floor_care + $organization;
    if ($total_score !== $calculated_total) {
        throw new Exception('Total score calculation mismatch.');
    }

    // Get database connection
    $db = Database::getInstance();
    /** @var MySQLiCompatibility $conn */
    $conn = $db->getConnection();

    $conn->begin_transaction();

    try {
        // Check if evaluation already exists for this report
        $stmt = $conn->prepare("SELECT evaluation_id FROM evaluations WHERE report_id = ?");
        $stmt->bind_param("s", $report_id);
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
            $stmt->bind_param("siiiiisis", $employee_id, $task_completion, $attention_to_detail, 
                $trash_management, $floor_care, $organization, $remark, $total_score, $report_id);
        } else {
            // Insert new evaluation
            $stmt = $conn->prepare("INSERT INTO evaluations 
                (employee_id, report_id, task_completion, attention_to_detail, trash_management, floor_care, organization, remark, rating, created_at) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())");
            $stmt->bind_param("siiiiiisi", $employee_id, $report_id, $task_completion, $attention_to_detail, 
                $trash_management, $floor_care, $organization, $remark, $total_score);
        }

        $stmt->execute();
        $stmt->close();

        // Mark report as evaluated
        $stmt = $conn->prepare("UPDATE progress_reports SET is_evaluated = 1 WHERE report_id = ?");
        $stmt->bind_param("s", $report_id);
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
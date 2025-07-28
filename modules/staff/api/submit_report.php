<?php
/**
 * Submit Progress Report API
 * Handles staff progress report submissions with enhanced security
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Initialize session management
        SessionManager::init();

        // Check authentication
        if (!SessionManager::isAuthenticated()) {
            header("Location: ../pages/progress_report.php?message=Error: Please log in to submit a report.&status=danger");
            exit;
        }

        // Validate CSRF token
        CSRFProtection::validateRequest();


        // Define validation rules (array format for validateFields)
        $validationRules = [
            'reportDescription' => ['type' => 'text', 'min_length' => 10, 'max_length' => 1000, 'required' => true],
            'location' => ['type' => 'text', 'min_length' => 3, 'max_length' => 255, 'required' => true]
        ];

        // Validate input
        $validatedData = InputValidator::validateFields($_POST, $validationRules);
        if (!$validatedData || !$validatedData['valid']) {
            // Extract first error message from validation result
            $firstError = 'Invalid input.';
            if (isset($validatedData['fields']) && is_array($validatedData['fields'])) {
                foreach ($validatedData['fields'] as $field) {
                    if (isset($field['valid']) && !$field['valid'] && !empty($field['message'])) {
                        $firstError = $field['message'];
                        break;
                    }
                }
            }
            header("Location: ../pages/progress_report.php?message=" . urlencode($firstError) . "&status=danger");
            exit;
        }

        // Validate file upload
        if (empty($_FILES['reportImage']['name'])) {
            header("Location: ../pages/progress_report.php?message=Please upload an image.&status=danger");
            exit;
        }

        $fileValidation = InputValidator::validateFileUpload(
            $_FILES['reportImage'],
            ['jpg', 'jpeg', 'png', 'gif'],
            5242880 // 5MB
        );

        if (!$fileValidation['valid']) {
            header("Location: ../pages/progress_report.php?message=" . urlencode($fileValidation['message']) . "&status=danger");
            exit;
        }

        // Get current user
        $currentUser = SessionManager::getCurrentUser();
        $account_id = $currentUser['account_id'];

        $description = $validatedData['fields']['reportDescription']['value'];
        $location = $validatedData['fields']['location']['value'];
        $reportImage = $_FILES['reportImage'];

        // Validate image file
        $imageInfo = getimagesize($reportImage['tmp_name']);
        if (!$imageInfo) {
            header("Location: ../pages/progress_report.php?message=Invalid image file. Please take a photo using your camera.&status=danger");
            exit;
        }

        // Check file size (minimum 10KB to ensure it's not a tiny/fake image)
        if ($reportImage['size'] < 10000) {
            header("Location: ../pages/progress_report.php?message=Image file is too small. Please take a proper photo using your camera.&status=danger");
            exit;
        }

        // Check file age (prevent old photos)
        $uploadedTime = time();
        $fileModifiedTime = filemtime($reportImage['tmp_name']);
        $timeDifference = $uploadedTime - $fileModifiedTime;

        if ($timeDifference > 10 * 60) { // 10 minutes
            header("Location: ../pages/progress_report.php?message=Please take a fresh photo using your camera. Old photos are not allowed.&status=danger");
            exit;
        }

        // Get database connection
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Get employee details
        $stmt = $conn->prepare("SELECT employee_id FROM account WHERE account_id = ?");
        $stmt->bind_param("i", $account_id);
        $stmt->execute();
        $stmt->bind_result($employee_id);
        $stmt->fetch();
        $stmt->close();

        if (!$employee_id) {
            header("Location: ../pages/progress_report.php?message=Error: Employee ID not found for the account.&status=danger");
            exit;
        }

        // Get staff details
        $stmt = $conn->prepare("SELECT first_name, last_name FROM staff WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $stmt->bind_result($first_name, $last_name);
        $stmt->fetch();
        $stmt->close();

        if (!$first_name || !$last_name) {
            header("Location: ../pages/progress_report.php?message=Error: Full name not found for the employee.&status=danger");
            exit;
        }

        $full_name = $first_name . ' ' . $last_name;

        // Check if user already submitted a report today
        $currentDate = date("Y-m-d");
        $stmt = $conn->prepare("SELECT COUNT(*) FROM progress_reports WHERE employee_id = ? AND DATE(created_at) = ?");
        $stmt->bind_param("ss", $employee_id, $currentDate);
        $stmt->execute();
        $stmt->bind_result($reportCount);
        $stmt->fetch();
        $stmt->close();

        if ($reportCount > 0) {
            header("Location: ../pages/progress_report.php?message=You have already submitted a report today.&status=danger");
            exit;
        }

        // Secure file upload
        $uploadDir = dirname(dirname(dirname(__DIR__))) . '/assets/images/uploads/';
        $fileExtension = strtolower(pathinfo($reportImage['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        
        if (!in_array($fileExtension, $allowedExtensions)) {
            header("Location: ../pages/progress_report.php?message=Invalid file type. Only JPG, JPEG, PNG, and GIF files are allowed.&status=danger");
            exit;
        }

        // Generate unique filename to prevent conflicts
        $uniqueFilename = uniqid() . '.' . $fileExtension;
        $uploadFile = $uploadDir . $uniqueFilename;
        $dbFilePath = 'assets/images/uploads/' . $uniqueFilename;

        if (move_uploaded_file($reportImage['tmp_name'], $uploadFile)) {
            // Insert report into database
            $stmt = $conn->prepare("INSERT INTO progress_reports (employee_id, report_image, description, full_name, location) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $employee_id, $dbFilePath, $description, $full_name, $location);

            if ($stmt->execute()) {
                // Log successful submission
                ErrorHandler::logError("Progress report submitted successfully", [
                    'employee_id' => $employee_id,
                    'location' => $location,
                    'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                ]);
                
                header("Location: ../pages/progress_report.php?message=Report submitted successfully!&status=success");
                $stmt->close();
                exit;
            } else {
                // Clean up uploaded file on database error
                if (file_exists($uploadFile)) {
                    unlink($uploadFile);
                }
                header("Location: ../pages/progress_report.php?message=Error: " . $stmt->error . "&status=danger");
                $stmt->close();
                exit;
            }
        } else {
            header("Location: ../pages/progress_report.php?message=File upload failed.&status=danger");
            exit;
        }

    } catch (Exception $e) {
        // Log error securely
        ErrorHandler::logError('Report submission error: ' . $e->getMessage(), [
            'account_id' => $account_id ?? 'unknown',
            'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
        ]);
        
        header("Location: ../pages/progress_report.php?message=An error occurred while submitting your report. Please try again.&status=danger");
        exit;
    }
}
?>
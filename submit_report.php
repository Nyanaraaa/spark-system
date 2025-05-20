<?php
require 'database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    // Check if account ID exists in session
    if (!isset($_SESSION['account_id'])) {
        header("Location: progressreport.php?message=Error: Account ID not found in session.&status=danger");
        exit;
    }

    // Validate required fields
    if (empty($_POST['reportDescription']) || empty($_FILES['reportImage']['name']) || empty($_POST['location'])) {
        header("Location: progressreport.php?message=Please fill in all required fields.&status=danger");
        exit;
    }

    $description = $_POST['reportDescription'];
    $reportImage = $_FILES['reportImage'];
    $location = $_POST['location'];
    $account_id = $_SESSION['account_id'];

    // Fetch employee ID
    $stmt = $conn->prepare("SELECT employee_id FROM account WHERE account_id = ?");
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $stmt->bind_result($employee_id);
    $stmt->fetch();
    $stmt->close();

    if (!$employee_id) {
        header("Location: progressreport.php?message=Error: Employee ID not found for the account.&status=danger");
        exit;
    }

    // Fetch employee name
    $stmt = $conn->prepare("SELECT first_name, last_name FROM staff WHERE employee_id = ?");
    $stmt->bind_param("s", $employee_id);
    $stmt->execute();
    $stmt->bind_result($first_name, $last_name);
    $stmt->fetch();
    $stmt->close();

    if (!$first_name || !$last_name) {
        header("Location: progressreport.php?message=Error: Full name not found for the employee.&status=danger");
        exit;
    }

    $full_name = $first_name . ' ' . $last_name;

    // Check if a report has already been submitted today
    $currentDate = date("Y-m-d");
    $stmt = $conn->prepare("SELECT COUNT(*) FROM progress_reports WHERE employee_id = ? AND DATE(created_at) = ?");
    $stmt->bind_param("ss", $employee_id, $currentDate);
    $stmt->execute();
    $stmt->bind_result($reportCount);
    $stmt->fetch();
    $stmt->close();

    if ($reportCount > 0) {
        header("Location: progressreport.php?message=You have already submitted a report today.&status=danger");
        exit;
    }

    // Handle file upload
    if ($reportImage['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'uploads/';
        $uploadFile = $uploadDir . basename($reportImage['name']);

        if (move_uploaded_file($reportImage['tmp_name'], $uploadFile)) {
            // Insert new progress report
            $stmt = $conn->prepare("INSERT INTO progress_reports (employee_id, report_image, description, full_name, location) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $employee_id, $uploadFile, $description, $full_name, $location);

            if ($stmt->execute()) {
                header("Location: progressreport.php?message=Report submitted successfully!&status=success");
                exit;
            } else {
                header("Location: progressreport.php?message=Error: " . $stmt->error . "&status=danger");
                exit;
            }

            $stmt->close();
        } else {
            header("Location: progressreport.php?message=File upload failed.&status=danger");
            exit;
        }
    } else {
        header("Location: progressreport.php?message=Error uploading file: " . $reportImage['error'] . "&status=danger");
        exit;
    }
}
?>

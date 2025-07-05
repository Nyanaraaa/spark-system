<?php
/**
 * Password Reset Code Handler
 * Handles password reset requests and updates with enhanced security
 */

// Include bootstrap for security components
require_once dirname(dirname(__DIR__)) . '/includes/bootstrap.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require '../../vendor/autoload.php';

// Initialize session management
SessionManager::init();

/**
 * Send password reset email
 */
function send_password_reset($get_email, $token)
{
    $mail = new PHPMailer(true);
    
    try {
        // Server settings
        $mail->isSMTP();
        $mail->SMTPAuth = true;
        $mail->Host = $_ENV['MAIL_HOST'] ?? "smtp.gmail.com";
        $mail->Username = $_ENV['MAIL_USERNAME'] ?? "sparksystem00@gmail.com";
        $mail->Password = $_ENV['MAIL_PASSWORD'] ?? "datu olqh prmn mnbv";
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = $_ENV['MAIL_PORT'] ?? 587;

        // Recipients
        $mail->setFrom($_ENV['MAIL_FROM_ADDRESS'] ?? "lanzcyrille.002@gmail.com", $_ENV['MAIL_FROM_NAME'] ?? 'SPARK');
        $mail->addAddress($get_email);
        
        // Content
        $mail->isHTML(true);
        $mail->Subject = "Reset Password/Username Notification";

        $email_template = "
        <h2>Hello</h2>
        <h3>You are receiving this email because we received a password/username reset request for your account.</h3>
        <br/>
        <a href='http://localhost/spark/modules/auth/password-reset.php?token={$token}&email={$get_email}'>Click here to reset your password</a>
        <br/><br/>
        <p>If you did not request this reset, please ignore this email.</p>
        ";
        $mail->Body = $email_template;

        $mail->send();
        return true;
    } catch (Exception $e) {
        ErrorHandler::logError('Password reset email failed: ' . $mail->ErrorInfo, [
            'email' => $get_email,
            'token' => substr($token, 0, 8) . '...'
        ]);
        return false;
    }
}

// Handle password reset link request
if (isset($_POST['password_reset_link'])) {
    try {
        // Validate CSRF token
        CSRFProtection::validateRequest();
        
        // Define validation rules
        $validationRules = [
            'employee_id' => 'required|string|employee_id',
            'email' => 'required|email|max:255'
        ];

        // Validate input
        $validatedData = InputValidator::validate($_POST, $validationRules);
        
        if (!$validatedData) {
            SessionManager::setFlashMessage(InputValidator::getFirstError(), 'error');
            header('Location: ../../index.php');
            exit();
        }

        $employee_id = $validatedData['employee_id'];
        $email = $validatedData['email'];
        
        // Generate secure token
        $token = bin2hex(random_bytes(32));

        // Get database connection
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Check if account exists with employee_id and email
        $query = "SELECT * FROM account WHERE employee_id = ? AND email_address = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ss", $employee_id, $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Update token in database
            $update_token = "UPDATE account SET verify_token = ? WHERE email_address = ? LIMIT 1";
            $stmt_update = $conn->prepare($update_token);
            $stmt_update->bind_param("ss", $token, $email);
            
            if ($stmt_update->execute()) {
                // Send password reset email
                if (send_password_reset($email, $token)) {
                    SessionManager::setFlashMessage('We e-mailed you a Username/Password reset link', 'success');
                } else {
                    SessionManager::setFlashMessage('Failed to send reset email. Please try again.', 'error');
                }
            } else {
                SessionManager::setFlashMessage('Something went wrong while processing your request.', 'error');
            }
            
            $stmt_update->close();
        } else {
            SessionManager::setFlashMessage('Invalid Employee ID or Email address', 'error');
        }

        $stmt->close();
        
    } catch (Exception $e) {
        ErrorHandler::logError('Password reset request error: ' . $e->getMessage(), [
            'employee_id' => $employee_id ?? 'unknown',
            'email' => $email ?? 'unknown'
        ]);
        SessionManager::setFlashMessage('An error occurred. Please try again.', 'error');
    }

    header('Location: ../../index.php');
    exit();
}

// Handle password update
if (isset($_POST['password_update'])) {
    try {
        // Validate CSRF token
        CSRFProtection::validateRequest();
        
        // Define validation rules
        $validationRules = [
            'email_address' => 'required|email|max:255',
            'new_username' => 'required|string|username',
            'new_password' => 'required|string|password',
            'confirm_password' => 'required|string|min:6'
        ];

        // Validate input
        $validatedData = InputValidator::validate($_POST, $validationRules);
        
        if (!$validatedData) {
            SessionManager::setFlashMessage(InputValidator::getFirstError(), 'error');
            header('Location: ../../index.php');
            exit();
        }

        $email = $validatedData['email_address'];
        $new_username = $validatedData['new_username'];
        $new_password = $validatedData['new_password'];
        $confirm_password = $validatedData['confirm_password'];

        // Check if passwords match
        if ($new_password !== $confirm_password) {
            SessionManager::setFlashMessage('Passwords do not match!', 'error');
            header('Location: ../../index.php');
            exit();
        }
        $token = trim($_POST['password_token'] ?? '');

        if (empty($token)) {
            SessionManager::setFlashMessage('No token available!', 'error');
            header("location: password-reset.php");
            exit(0);
        }

        if (empty($email) || empty($new_password) || empty($confirm_password)) {
            SessionManager::setFlashMessage('All fields are mandatory.', 'error');
            header("location: password-reset.php?token=$token&email=$email");
            exit(0);
        }

        // Validate email format
        $emailValidation = InputValidator::validateEmail($email);
        if (!$emailValidation['valid']) {
            SessionManager::setFlashMessage('Please enter a valid email address.', 'error');
            header("location: password-reset.php?token=$token&email=$email");
            exit(0);
        }

        // Validate username
        $usernameValidation = InputValidator::validateUsername($new_username);
        if (!$usernameValidation['valid']) {
            SessionManager::setFlashMessage($usernameValidation['message'], 'error');
            header("location: password-reset.php?token=$token&email=$email");
            exit(0);
        }

        // Validate password
        $passwordValidation = InputValidator::validatePassword($new_password);
        if (!$passwordValidation['valid']) {
            SessionManager::setFlashMessage($passwordValidation['message'], 'error');
            header("location: password-reset.php?token=$token&email=$email");
            exit(0);
        }

        if ($new_password !== $confirm_password) {
            SessionManager::setFlashMessage('Password and confirm password do not match.', 'error');
            header("location: password-reset.php?token=$token&email=$email");
            exit(0);
        }

        // Get database connection
        $db = Database::getInstance();
        $conn = $db->getConnection();

        // Check if token is valid
        $check_token_query = "SELECT verify_token FROM account WHERE verify_token = ? LIMIT 1";
        $stmt = $conn->prepare($check_token_query);
        $stmt->bind_param("s", $token);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            // Check if username already exists (excluding current user)
            $check_username_query = "SELECT username FROM account WHERE username = ? AND verify_token != ? LIMIT 1";
            $username_stmt = $conn->prepare($check_username_query);
            $username_stmt->bind_param("ss", $new_username, $token);
            $username_stmt->execute();
            $username_result = $username_stmt->get_result();

            if ($username_result->num_rows > 0) {
                $username_stmt->close();
                $stmt->close();
                SessionManager::setFlashMessage('Username already exists!', 'error');
                header("location: password-reset.php?token=$token&email=$email");
                exit(0);
            }

            // Hash the new password securely
            $hashed_password = PasswordSecurity::hashPassword($new_password);

            // Update password and username
            $update_query = "UPDATE account SET password = ?, username = ? WHERE verify_token = ? LIMIT 1";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("sss", $hashed_password, $new_username, $token);

            if ($update_stmt->execute()) {
                // Invalidate the token by generating a new one
                $new_token = bin2hex(random_bytes(32));
                $update_token_query = "UPDATE account SET verify_token = ? WHERE verify_token = ? LIMIT 1";
                $token_stmt = $conn->prepare($update_token_query);
                $token_stmt->bind_param("ss", $new_token, $token);
                $token_stmt->execute();
                $token_stmt->close();

                // Log successful password reset
                ErrorHandler::logError('Password reset completed successfully', [
                    'email' => $email,
                    'username' => $new_username
                ]);

                SessionManager::setFlashMessage('New password and username successfully updated!', 'success');
                header("location: ../../index.php");
                exit(0);
            } else {
                $update_stmt->close();
                $username_stmt->close();
                $stmt->close();
                SessionManager::setFlashMessage('Failed to update password and username. Please try again.', 'error');
                header("location: password-reset.php?token=$token&email=$email");
                exit(0);
            }
        } else {
            $stmt->close();
            SessionManager::setFlashMessage('This link has expired!', 'error');
            header("location: password-reset.php?token=$token&email=$email");
            exit(0);
        }

    } catch (Exception $e) {
        ErrorHandler::logError('Password update error: ' . $e->getMessage(), [
            'email' => $email ?? 'unknown',
            'token' => substr($token ?? '', 0, 8) . '...'
        ]);
        SessionManager::setFlashMessage('An error occurred. Please try again.', 'error');
        header("location: password-reset.php?token=" . ($token ?? '') . "&email=" . ($email ?? ''));
        exit(0);
    }
}
?>

<?php
session_start(); 
require 'database.php'; 

use PHPMailer\PHPMailer\PHPMailer; 
use PHPMailer\PHPMailer\SMTP; 
use PHPMailer\PHPMailer\Exception; 


require 'vendor/autoload.php';


function send_password_reset($get_email, $token)
{
    $mail = new PHPMailer(true); 
    $mail->isSMTP();
    $mail->SMTPAuth   = true; 
    $mail->Host = "smtp.gmail.com"; 
    $mail->Username   = "sparksystem00@gmail.com"; 
    $mail->Password   = "datu olqh prmn mnbv"; 
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
    $mail->Port       = 587; 

 
    $mail->setFrom("lanzcyrille.002@gmail.com", 'SPARK'); 
    $mail->addAddress($get_email); 
    $mail->isHTML(true); 
    $mail->Subject = "Reset Password/Username Notification"; 

   
    $email_template = "
    <h2> Hello</h2>
    <h3>You are receiving this email because we received a password/username reset request for your account.</h3>
    <br/>
    <a href=https://www.sparksystem.online/password-reset.php?token=$token&email=$get_email'> Reset Password </a>
    ";
    $mail->Body = $email_template; 

    $mail->SMTPDebug = 2; 
    $mail->send(); 
}


if (isset($_POST['password_reset_link'])) {
    $employee_id = mysqli_real_escape_string($conn, $_POST['employee_id']); 
    $email = mysqli_real_escape_string($conn, $_POST['email']); 
    $token = md5(rand()); 

    
    $query = "SELECT * FROM account WHERE employee_id = ? AND email_address = ?";
    $stmt = $conn->prepare($query); 
    $stmt->bind_param("ss", $employee_id, $email); 
    $stmt->execute(); 
    $result = $stmt->get_result(); 

   
    if ($result->num_rows > 0) {
       
        $check_email = "SELECT email_address FROM account WHERE email_address = ? LIMIT 1";
        $stmt_email = $conn->prepare($check_email); 
        $stmt_email->bind_param("s", $email); 
        $stmt_email->execute(); 
        $email_result = $stmt_email->get_result(); 

      
        if ($email_result->num_rows > 0) {
            $row = $email_result->fetch_assoc(); 
            $get_email = $row['email_address']; 

          
            $update_token = "UPDATE account SET verify_token = ? WHERE email_address = ? LIMIT 1";
            $stmt_update = $conn->prepare($update_token); 
            $stmt_update->bind_param("ss", $token, $get_email); 
            $stmt_update->execute(); 

          
            if ($stmt_update->affected_rows > 0) {
                send_password_reset($get_email, $token); 
                $_SESSION['session'] = 'We e-mailed you a Username/Password reset link'; 
            } else {
                $_SESSION['session'] = 'Something went wrong while updating the token.'; 
            }
        } else {
            $_SESSION['session'] = 'No Email Found!'; 
        }

        
        $stmt_email->close();
        $stmt_update->close();
    } else {
        $_SESSION['session'] = 'Invalid Employee ID or Email address'; 
    }

   
    $stmt->close();
    $conn->close();

    header('Location: index.php');
    exit();
}


if (isset($_POST['password_update'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email_address']);
    $new_username = mysqli_real_escape_string($conn, $_POST['new_username']);
    $new_password = mysqli_real_escape_string($conn, $_POST['new_password']);
    $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);
    $token = mysqli_real_escape_string($conn, $_POST['password_token']);

    if (!empty($token)) {
        if (!empty($email) && !empty($new_password) && !empty($confirm_password)) {
            // Check if token exists
            $check_token_query = "SELECT verify_token FROM account WHERE verify_token = ? LIMIT 1";
            $stmt = $conn->prepare($check_token_query);
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                if ($new_password === $confirm_password) {
                    // Check for duplicate username
                    $check_username_query = "SELECT username FROM account WHERE username = ? LIMIT 1";
                    $username_stmt = $conn->prepare($check_username_query);
                    $username_stmt->bind_param("s", $new_username);
                    $username_stmt->execute();
                    $username_result = $username_stmt->get_result();

                    if ($username_result->num_rows > 0) {
                        $_SESSION['status'] = "Username already exists!";
                        $_SESSION['session'] = "New password and username successfully updated!";
                        header("location: password-reset.php?token=$token&email=$email");
                        exit(0);
                    } else {
                        // Update password and username
                        $update_query = "UPDATE account SET password = ?, username = ? WHERE verify_token = ? LIMIT 1";
                        $update_stmt = $conn->prepare($update_query);
                        $update_stmt->bind_param("sss", $new_password, $new_username, $token);

                        if ($update_stmt->execute()) {
                            // Generate new token
                            $new_token = md5(rand()) . "spark";
                            $update_token_query = "UPDATE account SET verify_token = ? WHERE verify_token = ? LIMIT 1";
                            $token_stmt = $conn->prepare($update_token_query);
                            $token_stmt->bind_param("ss", $new_token, $token);
                            $token_stmt->execute();

                            $_SESSION['session'] = "New password and username successfully updated!";
                            header("location: index.php");
                            exit(0);
                        } else {
                            $_SESSION['status'] = "Failed to update password and username. Please try again.";
                            header("location: password-reset.php?token=$token&email=$email");
                            exit(0);
                        }
                    }
                } else {
                    $_SESSION['status'] = "Password and confirm password do not match.";
                    header("location: password-reset.php?token=$token&email=$email");
                    exit(0);
                }
            } else {
                $_SESSION['status'] = "This link has expired!";
                header("location: password-reset.php?token=$token&email=$email");
                exit(0);
            }
        } else {
            $_SESSION['status'] = "All fields are mandatory.";
            header("location: password-reset.php?token=$token&email=$email");
            exit(0);
        }
    } else {
        $_SESSION['status'] = "No token available!";
        header("location: password-reset.php");
        exit(0);
    }
}

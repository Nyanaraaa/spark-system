<?php

require 'database.php'; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $employee_id = $_POST['employee_id'];
    $role = ($employee_id == 'supervisor') ? 'supervisor' : 'housekeeping_staff'; 
    
    if (!empty($username) && !empty($password) && !empty($employee_id)) {
        
        $checkEmployeeQuery = "SELECT * FROM staff WHERE employee_id = ?";
        $stmt = $conn->prepare($checkEmployeeQuery);
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 0) {
            
            echo "Invalid employee ID!";
        } else {
            $staffData = $result->fetch_assoc();
            $email_address = $staffData['email_address'];

           
            $checkUsernameQuery = "SELECT * FROM account WHERE username = ?";
            $usernameStmt = $conn->prepare($checkUsernameQuery);
            $usernameStmt->bind_param("s", $username);
            $usernameStmt->execute();
            $usernameResult = $usernameStmt->get_result();

            if ($usernameResult->num_rows > 0) {
                
                echo "Username already exists!";
            } else {
                
                $checkAccountQuery = "SELECT * FROM account WHERE employee_id = ?";
                $checkStmt = $conn->prepare($checkAccountQuery);
                $checkStmt->bind_param("s", $employee_id);
                $checkStmt->execute();
                $accountResult = $checkStmt->get_result();

                if ($accountResult->num_rows > 0) {
                    
                    echo "Employee ID already exists!";
                } else {
                    
                    $insertQuery = "INSERT INTO account (username, password, role, employee_id, email_address) VALUES (?, ?, ?, ?, ?)";
                    $insertStmt = $conn->prepare($insertQuery);
                    $insertStmt->bind_param("sssss", $username, $password, $role, $employee_id, $email_address);

                    if ($insertStmt->execute()) {
                        echo "Account created successfully!";
                    } else {
                        echo "Error creating account!";
                    }

                   
                    $insertStmt->close();
                }

               
                $checkStmt->close();
            }

            
            $usernameStmt->close();
        }

        
        $stmt->close();
    } else {
        echo "All fields are required!";
    }
}


$conn->close();
?>

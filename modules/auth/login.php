<?php
session_start();


header('Content-Type: application/json');


error_reporting(E_ALL);
ini_set('display_errors', 0);

try {
    require '../../config/database.php';


    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $username = $_POST['username'];
        $password = $_POST['password'];
        $modalType = $_POST['modalType'];


        if (!empty($username) && !empty($password)) {

            $stmt = $conn->prepare("SELECT account_id, username, password, role, employee_id FROM account WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $stmt->store_result();


            if ($stmt->num_rows > 0) {
                $stmt->bind_result($account_id, $username, $db_password, $role, $employee_id);
                $stmt->fetch();


                if ($password === $db_password) {

                    if (($modalType == 'supervisor' && $role != 'supervisor') || ($modalType == 'staff' && $role != 'housekeeping_staff')) {

                        echo json_encode(['success' => false, 'message' => 'Invalid username or password!']);
                    } else {

                        $_SESSION['username'] = $username;
                        $_SESSION['role'] = $role;
                        $_SESSION['account_id'] = $account_id;
                        $_SESSION['employee_id'] = $employee_id;


                        if ($role == 'housekeeping_staff') {
                            $staff_stmt = $conn->prepare("SELECT staff_id FROM staff WHERE employee_id = ?");
                            $staff_stmt->bind_param("s", $employee_id);
                            $staff_stmt->execute();
                            $staff_stmt->bind_result($staff_id);
                            $staff_stmt->fetch();
                            $_SESSION['staff_id'] = $staff_id;
                            $staff_stmt->close();
                        }


                        if ($role == 'supervisor') {
                            echo json_encode(['success' => true, 'redirect' => 'modules/supervisor/pages/staff_list.php']);
                        } else {
                            echo json_encode(['success' => true, 'redirect' => 'modules/staff/pages/dashboard.php']);
                        }
                    }
                } else {

                    echo json_encode(['success' => false, 'message' => 'Invalid username or password!']);
                }
            } else {

                echo json_encode(['success' => false, 'message' => 'User not found!']);
            }
            $stmt->close();
        } else {

            echo json_encode(['success' => false, 'message' => 'Please fill in all fields!']);
        }
        exit();
    }

} catch (Exception $e) {

    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
    exit();
}
?>
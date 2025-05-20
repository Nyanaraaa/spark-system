<?php
session_start(); 
require 'database.php'; 

if (!isset($_SESSION['username'])) {
    header("Location: index.php");
    exit();
}

$username = $_SESSION['username'];

$stmt = $conn->prepare("SELECT account_id, username, role, employee_id, email_address, password FROM account WHERE username = ?");
$stmt->bind_param("s", $username); 
$stmt->execute(); 
$stmt->bind_result($account_id, $username, $role, $employee_id, $email_address, $password); 
$stmt->fetch(); 
$stmt->close(); 

// Get staff details
$stmt = $conn->prepare("SELECT s.staff_id, s.first_name, s.last_name, s.contact_no, s.profile_picture 
                        FROM staff s 
                        JOIN account a ON s.employee_id = a.employee_id 
                        WHERE a.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($staff_id, $first_name, $last_name, $contact_no, $profile_picture);
$stmt->fetch();
$stmt->close();

$full_name = $first_name . ' ' . $last_name;

$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_password = $_POST['password'];

    // Check if username already exists
    $stmt = $conn->prepare("SELECT COUNT(*) FROM account WHERE username = ? AND account_id != ?");
    $stmt->bind_param("si", $new_username, $account_id);
    $stmt->execute();
    $stmt->bind_result($username_count);
    $stmt->fetch();
    $stmt->close();

    if ($username_count > 0) {
        $_SESSION['update_msg'] = "Error: Username already exists. Please choose another.";
    } else {
        $conn->begin_transaction();

        try {
            // Update account details
            if (!empty($new_password)) {
                $stmt = $conn->prepare("UPDATE account SET username = ?, email_address = ?, password = ? WHERE account_id = ?");
                $stmt->bind_param("sssi", $new_username, $new_email, $new_password, $account_id);
            } else {
                $stmt = $conn->prepare("UPDATE account SET username = ?, email_address = ? WHERE account_id = ?");
                $stmt->bind_param("ssi", $new_username, $new_email, $account_id);
            }

            $stmt->execute();
            $stmt->close();

            // Update staff details
            $stmt = $conn->prepare("UPDATE staff SET email_address = ? WHERE employee_id = ?");
            $stmt->bind_param("ss", $new_email, $employee_id);
            $stmt->execute();
            $stmt->close();

            // Commit changes
            $conn->commit();

            $_SESSION['update_msg'] = "Account details updated successfully.";
            $_SESSION['username'] = $new_username; 
            $_SESSION['email'] = $new_email; 

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['update_msg'] = "Error updating account details: " . $e->getMessage();
        }
    }

    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SPARK - Account Settings</title>
    <link href="https://cdn.lineicons.com/4.0/lineicons.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="supervisor_profile.css?v=<?php echo filemtime('supervisor_profile.css'); ?>">
</head>

<body>
    <?php include 'supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="text-center">
            <h1 class="mb-4">Account Management</h1>
        </div>

        <div class="container py-4">
            <?php if (isset($_SESSION['update_msg'])): ?>
                <div id="update-alert" class="alert alert-<?php echo (strpos($_SESSION['update_msg'], 'Error') === 0) ? 'danger' : 'success'; ?>">
                    <i class="lni <?php echo (strpos($_SESSION['update_msg'], 'Error') === 0) ? 'lni-close' : 'lni-checkmark-circle'; ?> me-2"></i>
                    <?php echo htmlspecialchars($_SESSION['update_msg']); ?>
                </div>
                <?php unset($_SESSION['update_msg']); ?>
            <?php endif; ?>

            <div class="row">
                <!-- Profile Information Card -->
                <div class="col-md-5 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <i class="lni lni-user me-2"></i>
                            <span>Profile Information</span>
                        </div>
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <?php
                                // Ensure profile picture is valid
                                if (!empty($profile_picture) && file_exists('uploads/' . $profile_picture)) {
                                    echo '<img src="uploads/' . htmlspecialchars($profile_picture) . '" alt="Profile Picture" 
                                        class="img-fluid rounded-circle shadow" 
                                        style="width: 150px; height: 150px; object-fit: cover; border: 3px solid var(--gold);">';
                                } else {
                                    echo '<img src="uploads/default.png" alt="Default Profile Picture" 
                                        class="img-fluid rounded-circle shadow" 
                                        style="width: 150px; height: 150px; object-fit: cover; border: 3px solid var(--gold);">';
                                }
                                ?>
                                <h3 class="mt-3" style="color: var(--maroon);"><?php echo htmlspecialchars($full_name); ?></h3>
                                <p class="badge-role"><?php echo htmlspecialchars($role); ?></p>
                            </div>
                            
                            <div class="profile-details mt-4">
                                <div class="detail-item">
                                    <i class="lni lni-user detail-icon"></i>
                                    <div class="detail-content">
                                        <div class="detail-label">Username</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($username); ?></div>
                                    </div>
                                </div>
                                
                                <div class="detail-item">
                                    <i class="lni lni-envelope detail-icon"></i>
                                    <div class="detail-content">
                                        <div class="detail-label">Email</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($email_address); ?></div>
                                    </div>
                                </div>
                                
                                <div class="detail-item">
                                    <i class="lni lni-lock-alt detail-icon"></i>
                                    <div class="detail-content">
                                        <div class="detail-label">Password</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($password); ?></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Settings Card -->
                <div class="col-md-7 mb-4">
                    <div class="card">
                        <div class="card-header d-flex align-items-center">
                            <i class="lni lni-cog me-2"></i>
                            <span>Account Settings</span>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">
                                            <i class="lni lni-user me-1" style="color: var(--maroon);"></i> Username
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="lni lni-user"></i></span>
                                            <input type="text" name="username" id="username" class="form-control" required value="<?php echo htmlspecialchars($username); ?>">
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="lni lni-envelope me-1" style="color: var(--maroon);"></i> Email
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="lni lni-envelope"></i></span>
                                            <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($email_address); ?>">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label">
                                        <i class="lni lni-lock-alt me-1" style="color: var(--maroon);"></i> New Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="lni lni-lock-alt"></i></span>
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password">
                                    </div>
                                    <small class="text-muted mt-1">Leave blank to keep current password</small>
                                </div>
                                
                                <div class="d-flex justify-content-center mt-4">
                                    <button type="submit" class="btn btn-save" style="width: 200px;">
                                        <i class="lni lni-save me-2"></i> Save Changes
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JavaScript Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe" crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const logoutLink = document.getElementById('logout-link');
            if (logoutLink) {
                logoutLink.addEventListener('click', function(event) {
                    event.preventDefault();
                    const confirmLogout = confirm('Are you sure you want to log out?');
                    if (confirmLogout) {
                        window.location.href = logoutLink.href;
                    }
                });
            }
        });
    </script>

    <script>
        setTimeout(() => {
            const alertElement = document.getElementById('update-alert');
            if (alertElement) {
                alertElement.style.transition = "opacity 0.5s ease";
                alertElement.style.opacity = "0"; 
                setTimeout(() => alertElement.remove(), 500); 
            }
        }, 5000); 
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const settingsForm = document.querySelector('form');
            settingsForm.addEventListener('submit', function(event) {
                const confirmUpdate = confirm('Are you sure you want to update your account details?');
                if (!confirmUpdate) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>
</html>

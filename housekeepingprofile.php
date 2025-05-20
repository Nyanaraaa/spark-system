<?php
session_start();
require 'database.php'; 


if (!isset($_SESSION['username'])) {
    header("Location: index.php"); 
    exit();
}

$username = $_SESSION['username'];


$stmt = $conn->prepare("SELECT account_id, username, role FROM account WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($account_id, $username, $role);
$stmt->fetch();
$stmt->close();


$stmt = $conn->prepare("SELECT a.account_id, a.username, a.role, s.staff_id, s.first_name, s.last_name, a.email_address, s.contact_no, s.employee_id, a.password
                        FROM account a
                        JOIN staff s ON a.employee_id = s.employee_id
                        WHERE a.username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$stmt->bind_result($account_id, $username, $role, $staff_id, $first_name, $last_name, $email_address, $contact_no, $employee_id, $password);
$stmt->fetch();
$stmt->close();


$full_name = $first_name . ' ' . $last_name;

$update_msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get new user data
    $new_username = $_POST['username'];
    $new_email = $_POST['email'];
    $new_phone = $_POST['phone']; 
    $new_password = $_POST['password'];

    // Handle file upload
    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $profile_picture = $_FILES['profile_picture'];
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($profile_picture['type'], $allowed_types)) {
            $upload_dir = 'uploads/';
            $profile_picture_name = uniqid('profile_', true) . '.' . pathinfo($profile_picture['name'], PATHINFO_EXTENSION);
            $upload_path = $upload_dir . $profile_picture_name;

            if (move_uploaded_file($profile_picture['tmp_name'], $upload_path)) {
                // Update the profile picture in the database
                $stmt = $conn->prepare("UPDATE staff SET profile_picture = ? WHERE staff_id = ?");
                $stmt->bind_param("si", $profile_picture_name, $staff_id);
                $stmt->execute();
                $stmt->close();
            } else {
                $_SESSION['update_msg'] = "Error uploading the profile picture.";
            }
        } else {
            $_SESSION['update_msg'] = "Invalid file type. Only JPG, PNG, and GIF files are allowed.";
        }
    }

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
            $stmt = $conn->prepare("UPDATE staff SET email_address = ?, contact_no = ? WHERE employee_id = ?");
            $stmt->bind_param("sss", $new_email, $new_phone, $employee_id);
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
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="housekeepingprofile.css?v=<?php echo filemtime('housekeepingprofile.css'); ?>">
</head>

<body>
    <?php include 'housekeeping_navbar.php'; ?>

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
                                // Fetch profile picture from the database
                                $stmt = $conn->prepare("SELECT profile_picture FROM staff WHERE staff_id = ?");
                                $stmt->bind_param("i", $staff_id);
                                $stmt->execute();
                                $stmt->bind_result($profile_picture);
                                $stmt->fetch();
                                $stmt->close();

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
                                <p class="badge" style="background-color: var(--gold); color: var(--black);"><?php echo htmlspecialchars($role); ?></p>
                            </div>
                            
                            <div class="profile-details mt-4">
                                <div class="detail-item d-flex align-items-center mb-3 p-2 rounded" style="background-color: var(--gray-light);">
                                    <i class="lni lni-user me-3" style="color: var(--maroon); font-size: 1.2rem;"></i>
                                    <div>
                                        <div class="detail-label" style="color: var(--gray); font-size: 0.85rem;">Username</div>
                                        <div class="detail-value" style="font-weight: 500;"><?php echo htmlspecialchars($username); ?></div>
                                    </div>
                                </div>
                                
                                <div class="detail-item d-flex align-items-center mb-3 p-2 rounded" style="background-color: var(--gray-light);">
                                    <i class="lni lni-envelope me-3" style="color: var(--maroon); font-size: 1.2rem;"></i>
                                    <div>
                                        <div class="detail-label" style="color: var(--gray); font-size: 0.85rem;">Email</div>
                                        <div class="detail-value" style="font-weight: 500;"><?php echo htmlspecialchars($email_address); ?></div>
                                    </div>
                                </div>
                                
                                <div class="detail-item d-flex align-items-center mb-3 p-2 rounded" style="background-color: var(--gray-light);">
                                    <i class="lni lni-phone me-3" style="color: var(--maroon); font-size: 1.2rem;"></i>
                                    <div>
                                        <div class="detail-label" style="color: var(--gray); font-size: 0.85rem;">Phone</div>
                                        <div class="detail-value" style="font-weight: 500;"><?php echo htmlspecialchars($contact_no); ?></div>
                                    </div>
                                </div>
                                
                                <div class="detail-item d-flex align-items-center mb-3 p-2 rounded" style="background-color: var(--gray-light);">
                                    <i class="lni lni-id-card me-3" style="color: var(--maroon); font-size: 1.2rem;"></i>
                                    <div>
                                        <div class="detail-label" style="color: var(--gray); font-size: 0.85rem;">Employee ID</div>
                                        <div class="detail-value" style="font-weight: 500;"><?php echo htmlspecialchars($employee_id); ?></div>
                                    </div>
                                </div>
                                
                                <div class="detail-item d-flex align-items-center mb-3 p-2 rounded" style="background-color: var(--gray-light);">
                                    <i class="lni lni-lock-alt me-3" style="color: var(--maroon); font-size: 1.2rem;"></i>
                                    <div>
                                        <div class="detail-label" style="color: var(--gray); font-size: 0.85rem;">Password</div>
                                        <div class="detail-value" style="font-weight: 500;"><?php echo htmlspecialchars($password); ?></div>
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
                            <form method="POST" enctype="multipart/form-data">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">
                                            <i class="lni lni-user me-1" style="color: var(--maroon);"></i> Username
                                        </label>
                                        <input type="text" name="username" id="username" class="form-control" required value="<?php echo htmlspecialchars($username); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="lni lni-envelope me-1" style="color: var(--maroon);"></i> Email
                                        </label>
                                        <input type="email" name="email" id="email" class="form-control" required value="<?php echo htmlspecialchars($email_address); ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="phone" class="form-label">
                                            <i class="lni lni-phone me-1" style="color: var(--maroon);"></i> Phone Number
                                        </label>
                                        <input type="text" name="phone" id="phone" class="form-control" value="<?php echo htmlspecialchars($contact_no); ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label for="password" class="form-label">
                                            <i class="lni lni-lock-alt me-1" style="color: var(--maroon);"></i> New Password
                                        </label>
                                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password">
                                    </div>
                                </div>
                                
                                <div class="mb-4">
                                    <label for="profile_picture" class="form-label">
                                        <i class="lni lni-image me-1" style="color: var(--maroon);"></i> Profile Picture
                                    </label>
                                    <input type="file" name="profile_picture" id="profile_picture" class="form-control">
                                    
                                    <?php if (!empty($profile_picture)): ?>
                                        <div class="mt-3 text-center">
                                            <p class="text-muted mb-2">Current Profile Picture</p>
                                            <img src="uploads/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" 
                                                class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                                        </div>
                                    <?php endif; ?>
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

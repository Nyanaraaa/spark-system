<?php
/**
 * Supervisor Profile Page
 * Uses new security components and authentication
 */

// Include bootstrap for security components
require_once dirname(dirname(dirname(__DIR__))) . '/includes/bootstrap.php';

// Check authentication
SessionManager::requireAuth();
SessionManager::requireRole('supervisor');

try {
    // Get current user data
    $userData = SessionManager::getCurrentUser();
    if (!$userData || !isset($userData['employee_id'])) {
        SessionManager::setFlashMessage('Session expired. Please log in again.', 'error');
        header('Location: ../../../index.php');
        exit();
    }

    // Get database connection
    $db = Database::getInstance();
    /** @var MySQLiCompatibility $conn */
    $conn = $db->getConnection();

    $username = $userData['username'];
    $account_id = $userData['account_id'];
    $employee_id = $userData['employee_id'];
    $role = $userData['role'];

    // Get complete user data from database
    // For supervisors, we don't require a staff record - get account data only
    $stmt = $conn->prepare("SELECT account_id, username, email_address, role FROM account WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($userDetails = $result->fetch_assoc()) {
        $email_address = $userDetails['email_address'];
        // Update IDs in case they differ
        $account_id = $userDetails['account_id'];
        
        // Try to get staff details if they exist (optional for supervisors)
        $stmt->close();
        $stmt = $conn->prepare("SELECT staff_id, first_name, last_name, contact_no, profile_picture FROM staff WHERE employee_id = ?");
        $stmt->bind_param("s", $employee_id);
        $stmt->execute();
        $staff_result = $stmt->get_result();
        
        if ($staffDetails = $staff_result->fetch_assoc()) {
            // Supervisor has staff record
            $first_name = $staffDetails['first_name'];
            $last_name = $staffDetails['last_name'];
            $contact_no = $staffDetails['contact_no'];
            $profile_picture = $staffDetails['profile_picture'];
            $staff_id = $staffDetails['staff_id'];
        } else {
            // Supervisor doesn't have staff record - use defaults
            $first_name = ucfirst($username); // Use username as first name
            $last_name = '(Supervisor)';
            $contact_no = '';
            $profile_picture = '';
            $staff_id = null;
        }
    } else {
        throw new Exception('User account not found in database.');
    }
    $stmt->close();

    $full_name = $first_name . ' ' . $last_name;

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            // Validate CSRF token
            CSRFProtection::validateRequest();
            
            // Get and validate new user data
            $new_username = InputValidator::sanitizeString($_POST['username'] ?? '');
            $new_email = InputValidator::sanitizeString($_POST['email'] ?? '');
            $new_contact = InputValidator::sanitizeString($_POST['contact'] ?? '');
            $new_password = $_POST['password'] ?? '';

            // Validate required fields
            if (empty($new_username) || empty($new_email)) {
                throw new Exception('Username and email are required.');
            }

            // Validate email format
            if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Please enter a valid email address.');
            }

            // Validate password if provided
            if (!empty($new_password)) {
                if (!InputValidator::validatePassword($new_password)) {
                    throw new Exception('Password must be at least 8 characters long and contain uppercase, lowercase, number, and special character.');
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
                throw new Exception('Username already exists. Please choose another.');
            }

            $conn->begin_transaction();

            try {
                // Update account details
                if (!empty($new_password)) {
                    // Hash the new password securely
                    $hashed_password = PasswordSecurity::hashPassword($new_password);
                    $stmt = $conn->prepare("UPDATE account SET username = ?, email_address = ?, password = ? WHERE account_id = ?");
                    $stmt->bind_param("sssi", $new_username, $new_email, $hashed_password, $account_id);
                } else {
                    $stmt = $conn->prepare("UPDATE account SET username = ?, email_address = ? WHERE account_id = ?");
                    $stmt->bind_param("ssi", $new_username, $new_email, $account_id);
                }

                $stmt->execute();
                $stmt->close();

                // Update staff details if staff record exists
                if ($staff_id) {
                    $stmt = $conn->prepare("UPDATE staff SET email_address = ?, contact_no = ? WHERE employee_id = ?");
                    $stmt->bind_param("sss", $new_email, $new_contact, $employee_id);
                    $stmt->execute();
                    $stmt->close();
                }

                // Commit changes
                $conn->commit();
                
                // Update session username if changed
                if ($new_username !== $username) {
                    $_SESSION['username'] = $new_username;
                }

                SessionManager::setFlashMessage('Account details updated successfully.', 'success');

            } catch (Exception $e) {
                $conn->rollback();
                throw new Exception('Error updating account details: ' . $e->getMessage());
            }

        } catch (Exception $e) {
            ErrorHandler::logError('Supervisor profile update error: ' . $e->getMessage(), [
                'user_id' => $account_id,
                'employee_id' => $employee_id
            ]);
            
            SessionManager::setFlashMessage($e->getMessage(), 'error');
        }

        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

} catch (Exception $e) {
    ErrorHandler::logError('Supervisor profile page error: ' . $e->getMessage(), [
        'employee_id' => $employee_id ?? 'unknown'
    ]);
    
    SessionManager::setFlashMessage('An error occurred loading the profile page. Please try again.', 'error');
    header('Location: ../../../index.php');
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
    <link rel="manifest" href="../../../manifest.json">

    <!-- Updated CSS paths -->
    <link rel="stylesheet"
        href="../../../assets/css/global.css?v=<?php echo filemtime('../../../assets/css/global.css'); ?>">
    <link rel="stylesheet"
        href="../../../assets/css/components.css?v=<?php echo filemtime('../../../assets/css/components.css'); ?>">
    <link rel="stylesheet"
        href="../../../assets/css/supervisor_profile.css?v=<?php echo filemtime('../../../assets/css/supervisor_profile.css'); ?>">
    <link rel="stylesheet"
        href="../../../assets/css/responsive.css?v=<?php echo filemtime('../../../assets/css/responsive.css'); ?>">
</head>


<body>
    <?php include '../../../components/navbar/supervisor_navbar.php'; ?>

    <div class="main p-9">
        <div class="text-center">
            <h1 class="mb-4">Account Management</h1>
        </div>

        <div class="container py-4">
            <?php 
            // Check for flash messages
            $flashMessage = SessionManager::getFlashMessage();
            if ($flashMessage): ?>
                <div class="alert alert-<?= $flashMessage['type'] === 'success' ? 'success' : 'danger'; ?>" id="update-alert">
                    <i class="lni lni-<?= $flashMessage['type'] === 'success' ? 'checkmark-circle' : 'close'; ?> me-2"></i>
                    <?= htmlspecialchars($flashMessage['message']); ?>
                </div>
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
                                if (!empty($profile_picture) && file_exists('../../../assets/images/uploads/' . $profile_picture)) {
                                    echo '<img src="../../../assets/images/uploads/' . htmlspecialchars($profile_picture) . '" alt="Profile Picture" 
                                        class="img-fluid rounded-circle shadow" 
                                        style="width: 150px; height: 150px; object-fit: cover; border: 3px solid var(--gold);">';
                                } else {
                                    echo '<img src="../../../assets/images/uploads/default.png" alt="Default Profile Picture" 
                                        class="img-fluid rounded-circle shadow" 
                                        style="width: 150px; height: 150px; object-fit: cover; border: 3px solid var(--gold);">';
                                }
                                ?>
                                <h3 class="mt-3" style="color: var(--maroon);">
                                    <?php echo htmlspecialchars($full_name); ?></h3>
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

                                <?php if (!empty($contact_no)): ?>
                                <div class="detail-item">
                                    <i class="lni lni-phone detail-icon"></i>
                                    <div class="detail-content">
                                        <div class="detail-label">Contact</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($contact_no); ?></div>
                                    </div>
                                </div>
                                <?php endif; ?>

                                <div class="detail-item">
                                    <i class="lni lni-id-card detail-icon"></i>
                                    <div class="detail-content">
                                        <div class="detail-label">Employee ID</div>
                                        <div class="detail-value"><?php echo htmlspecialchars($employee_id); ?></div>
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
                                <?php echo CSRFProtection::getTokenField(); ?>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="username" class="form-label">
                                            <i class="lni lni-user me-1" style="color: var(--maroon);"></i> Username
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="lni lni-user"></i></span>
                                            <input type="text" name="username" id="username" class="form-control"
                                                required value="<?php echo htmlspecialchars($username); ?>">
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">
                                            <i class="lni lni-envelope me-1" style="color: var(--maroon);"></i> Email
                                        </label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="lni lni-envelope"></i></span>
                                            <input type="email" name="email" id="email" class="form-control" required
                                                value="<?php echo htmlspecialchars($email_address); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="contact" class="form-label">
                                        <i class="lni lni-phone me-1" style="color: var(--maroon);"></i> Contact Number
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="lni lni-phone"></i></span>
                                        <input type="text" name="contact" id="contact" class="form-control"
                                            value="<?php echo htmlspecialchars($contact_no ?? ''); ?>" placeholder="Optional">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label for="password" class="form-label">
                                        <i class="lni lni-lock-alt me-1" style="color: var(--maroon);"></i> New Password
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="lni lni-lock-alt"></i></span>
                                        <input type="password" name="password" id="password" class="form-control"
                                            placeholder="Enter new password">
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
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoTz1HIcje39Wm4jDKdf19U8gI4ddQ3GYNS7NTKfAdVQSZe"
        crossorigin="anonymous"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const logoutLink = document.getElementById('logout-link');
            if (logoutLink) {
                logoutLink.addEventListener('click', function (event) {
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
        document.addEventListener('DOMContentLoaded', function () {
            const settingsForm = document.querySelector('form');
            settingsForm.addEventListener('submit', function (event) {
                const confirmUpdate = confirm('Are you sure you want to update your account details?');
                if (!confirmUpdate) {
                    event.preventDefault();
                }
            });
        });
    </script>
</body>

</html>
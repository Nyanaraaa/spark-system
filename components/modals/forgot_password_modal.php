<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Reset Username or Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <?php if (isset($_SESSION['session'])): ?>
                    <?php

                    if (
                        strpos($_SESSION['session'], 'We e-mailed you a Username/Password reset link') !== false ||
                        strpos($_SESSION['session'], 'New password and username successfully updated!') !== false
                    ) {
                        $alertClass = 'alert-success';
                    } else {
                        $alertClass = 'alert-danger';
                    }
                    ?>
                    <div class="alert <?php echo $alertClass; ?>" id="session-alert">
                        <?php
                        echo $_SESSION['session'];
                        unset($_SESSION['session']);
                        ?>
                    </div>
                <?php endif; ?>
                <form action="modules/auth/password-reset-code.php" id="forgotPasswordForm" method="post">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="employee-id-icon">
                            <i class="bi bi-person-badge"></i>
                        </span>
                        <input type="text" class="form-control" id="forgot-employee-id" placeholder="Employee ID"
                            aria-describedby="employee-id-icon" name="employee_id" required>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="email-icon">
                            <i class="bi bi-envelope-fill"></i>
                        </span>
                        <input type="email" class="form-control" id="forgot-email" placeholder="Email Address"
                            aria-describedby="email-icon" name="email" required>
                    </div>
                    <button type="submit" name="password_reset_link" class="btn btn-tertiary">Send Reset
                        Link</button>
                </form>
            </div>
        </div>
    </div>
</div>

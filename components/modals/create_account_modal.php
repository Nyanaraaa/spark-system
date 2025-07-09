<div class="modal fade" id="createnewaccountLoginModal" tabindex="-1"
    aria-labelledby="createnewaccountLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createnewaccountLoginModalLabel">Create New Account</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">

                <div id="account-message-alert" style="display:none;" class="alert alert-success" role="alert">
                </div>
                <form id="createAccountForm" method="post">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="employee-id-icon">
                            <i class="bi bi-person-badge"></i>
                        </span>
                        <input type="text" class="form-control" id="employee_id" placeholder="Employee ID"
                            aria-describedby="employee-id-icon" name="employee_id" required>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="username-icon">
                            <i class="bi bi-person-fill"></i>
                        </span>
                        <input type="text" class="form-control" id="username" placeholder="Username"
                            aria-describedby="username-icon" name="username" required>
                    </div>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="password-icon">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input type="password" class="form-control" id="account-password" placeholder="Password"
                            aria-describedby="password-icon" name="password" required>
                    </div>
                    <div class="mt-2 mb-3 showpassword_box">
                        <input type="checkbox" id="account-checkbox" class="large-checkbox">
                        <label>Show password</label>
                    </div>

                    <button type="submit" class="btn btn-tertiary">Create Account</button>
                </form>
            </div>
        </div>
    </div>
</div>

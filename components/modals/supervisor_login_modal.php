<div class="modal fade" id="supervisorLoginModal" tabindex="-1" aria-labelledby="supervisorLoginModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="supervisorLoginModalLabel">Supervisor Log In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="message-alert" style="display:none;" class="alert" role="alert"></div>
                <form id="supervisorLoginForm" method="post">
                    <?php echo CSRFProtection::getTokenField(); ?>
                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="username-icon"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" id="username1" placeholder="Username"
                            aria-describedby="username-icon" name="username">
                    </div>
                    <div class="mb-2 input-group">
                        <span class="input-group-text" id="password-icon"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="supervisor-password" placeholder="Password"
                            aria-describedby="password-icon" name="password">
                    </div>
                    <div class="mt-1 mb-3 showpassword_box">
                        <input type="checkbox" id="supervisor-checkbox" class="large-checkbox">
                        <label>Show password</label>
                    </div>
                    <button type="submit" class="btn btn-tertiary">Log In</button>
                    <div class="mt-3 text-center">
                        <a href="#" id="forgotPasswordLink" class="forgot-password" data-bs-toggle="modal"
                            data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot Username or
                            Password?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="staffLoginModal" tabindex="-1" aria-labelledby="staffLoginModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="staffLoginModalLabel">Housekeeping Staff Log In</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="staff-message-alert" style="display:none;" class="alert" role="alert"></div>
                <form id="staffloginForm" method="post">
                    <?php echo CSRFProtection::getTokenField(); ?>

                    <div class="mb-3 input-group">
                        <span class="input-group-text" id="username-icon"><i class="bi bi-person-fill"></i></span>
                        <input type="text" class="form-control" id="username2" placeholder="Username"
                            aria-describedby="username-icon" name="username">
                    </div>
                    <div class="mb-1 input-group">
                        <span class="input-group-text" id="password-icon"><i class="bi bi-lock-fill"></i></span>
                        <input type="password" class="form-control" id="staff-password" placeholder="Password"
                            aria-describedby="password-icon" name="password">
                    </div>
                    <div class="mt-2 mb-3 showpassword_box">
                        <input type="checkbox" id="staff-checkbox" class="large-checkbox">
                        <label>Show password</label>
                    </div>
                    <button type="submit" class="btn btn-tertiary">Log In</button>
                    <div class="mt-2 text-center">
                        <a href="#" id="forgotPasswordLink" class="forgot-password" data-bs-toggle="modal"
                            data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot Username or
                            Password?</a>
                    </div>
                    <hr>
                    <div class="mb-3 d-flex justify-content-center">
                        <button type="button" class="btn btn-Quinary" data-bs-toggle="modal"
                            data-bs-target="#createnewaccountLoginModal" data-bs-dismiss="modal">
                            Create New Account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

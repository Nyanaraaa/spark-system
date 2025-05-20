<?php
session_start(); 
require 'database.php'; 

$showModal = isset($_SESSION['session']);
?>
<!DOCTYPE html>
<html lang="en">

<head>
    
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="manifest" href="manifest.json">
    <link rel="stylesheet" href="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.css"> 
    <script type="module" src="https://unpkg.com/@google/model-viewer/dist/model-viewer.min.js"></script>

    <link rel="stylesheet" href="style.css?v=<?php echo filemtime('style.css'); ?>">

    <title>SPARK-Home</title>
</head>

<body>
    
    <nav class="navbar navbar-expand-lg fixed-top ">
        <div class="container-fluid">
            <a class="navbar-brand me-auto" href="#">
                <img src="spark_logo.png" alt="SPARK Logo" class="logo">
            </a>
            <a href="#" class="login-button" data-bs-toggle="modal" data-bs-target="#loginModal">Log In</a>
            
        </div>
    </nav>

    <section class="hero">
        <div class="container">
            <h1 class="headline">SPARK</h1>
            <p class="subheading">
                Staff Performance Assessment, Recording and Keeping
            </p>
        </div>
    </section>

    <section class="cta d-flex align-items-center">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-6 cta-text pe-md-5 ">
                <h2>WE EMPOWER EXCEPTIONAL TEAMS.</h2><br>
                <p>
                    We collaborate to drive performance. We help individuals unlock their potential and challenge them to continuously improve. 
                    We equip staff and supervisors with the tools they need to thrive in housekeeping.
                </p>
                
            </div>
            <div class="col-md-6 cta-image text-center" style="transform: rotate(20deg);">
   <model-viewer 
    src="broomfinal.glb" 
    alt="Teams collaborating" 
    class="img-fluid teams model-viewer-custom"
    camera-controls 
    auto-rotate 
    background-color="transparent"
    disable-zoom>
</model-viewer>
</div>


            <div class="cta-buttons">
                    <a href="#" class="cta-button primary-cta" data-bs-toggle="modal" data-bs-target="#loginModal">LET'S GET STARTED</a>
            </div>
        </div>
    </div>
</section>

<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" id="loginModalLabel">Select Your Role</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted text-center" id="modalDescription">Please choose your role to continue:</p>
                <div class="row text-center">
                    <div class="col-12 col-md-6 mb-3">
                        <button type="button" class="btn btn-outline btn-lg w-100 py-3 role-btn" 
                            data-bs-toggle="modal" 
                            data-bs-target="#supervisorLoginModal" 
                            data-bs-dismiss="modal" 
                            aria-label="Select Supervisor Role">
                            <i class="bi bi-person-workspace fs-3 me-2"></i> Supervisor
                        </button>
                    </div>
                    <div class="col-12 col-md-6 mb-3">
                        <button type="button" class="btn btn-lg w-100 py-3 role-btn" 
                            data-bs-toggle="modal" 
                            data-bs-target="#staffLoginModal" 
                            data-bs-dismiss="modal" 
                            aria-label="Select Housekeeping Staff Role">
                            <i class="bi bi-person fs-3 me-2"></i> Housekeeping Staff
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>



    <div class="modal fade" id="supervisorLoginModal" tabindex="-1" aria-labelledby="supervisorLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="supervisorLoginModalLabel">Supervisor Log In</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="message-alert" style="display:none;" class="alert" role="alert"></div> 
                    <form id="supervisorLoginForm" method="post">
                        <div class="mb-3 input-group">
                            <span class="input-group-text" id="username-icon"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" id="username1" placeholder="Username" aria-describedby="username-icon" name="username">
                        </div>
                        <div class="mb-2 input-group">
                            <span class="input-group-text" id="password-icon"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="supervisor-password" placeholder="Password" aria-describedby="password-icon" name="password">
                        </div>
                        <div class="mt-1 mb-3 showpassword_box">
                            <input type="checkbox" id="supervisor-checkbox" class="large-checkbox">
                            <label>Show password</label>
                        </div>
                        <button type="submit" class="btn btn-tertiary">Log In</button> 
                        <div class="mt-3 text-center">
                            <a href="#" id="forgotPasswordLink" class="forgot-password" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot Username or Password?</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="staffLoginModal" tabindex="-1" aria-labelledby="staffLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="staffLoginModalLabel">Housekeeping Staff Log In</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="staff-message-alert" style="display:none;" class="alert" role="alert"></div> 
                    <form id="staffloginForm" method="post">

                        <div class="mb-3 input-group">
                            <span class="input-group-text" id="username-icon"><i class="bi bi-person-fill"></i></span>
                            <input type="text" class="form-control" id="username2" placeholder="Username" aria-describedby="username-icon" name="username">
                        </div>
                        <div class="mb-1 input-group">
                            <span class="input-group-text" id="password-icon"><i class="bi bi-lock-fill"></i></span>
                            <input type="password" class="form-control" id="staff-password" placeholder="Password" aria-describedby="password-icon" name="password">
                        </div>
                        <div class="mt-2 mb-3 showpassword_box">
                            <input type="checkbox" id="staff-checkbox" class="large-checkbox">
                            <label>Show password</label>
                        </div>
                        <button type="submit" class="btn btn-tertiary">Log In</button>
                        <div class="mt-2 text-center">
                            <a href="#" id="forgotPasswordLink" class="forgot-password" data-bs-toggle="modal" data-bs-target="#forgotPasswordModal" data-bs-dismiss="modal">Forgot Username or Password?</a>
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

       
     
    <div class="modal fade" id="createnewaccountLoginModal" tabindex="-1" aria-labelledby="createnewaccountLoginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="createnewaccountLoginModalLabel">Create New Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                   
                    <div id="account-message-alert" style="display:none;" class="alert alert-success" role="alert"></div>
                    <form id="createAccountForm" method="post">
                        <div class="mb-3 input-group">
                            <span class="input-group-text" id="employee-id-icon">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <input type="text" class="form-control" id="employee_id" placeholder="Employee ID" aria-describedby="employee-id-icon" name="employee_id" required>
                        </div>
                        <div class="mb-3 input-group">
                            <span class="input-group-text" id="username-icon">
                                <i class="bi bi-person-fill"></i>
                            </span>
                            <input type="text" class="form-control" id="username" placeholder="Username" aria-describedby="username-icon" name="username" required>
                        </div>
                        <div class="mb-3 input-group">
                            <span class="input-group-text" id="password-icon">
                                <i class="bi bi-lock-fill"></i>
                            </span>
                            <input type="password" class="form-control" id="account-password" placeholder="Password" aria-describedby="password-icon" name="password" required>
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

  
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
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
                    <form action="password-reset-code.php" id="forgotPasswordForm" method="post">
                        <div class="mb-3 input-group">
                            <span class="input-group-text" id="employee-id-icon">
                                <i class="bi bi-person-badge"></i>
                            </span>
                            <input type="text" class="form-control" id="forgot-employee-id" placeholder="Employee ID" aria-describedby="employee-id-icon" name="employee_id" required>
                        </div>
                        <div class="mb-3 input-group">
                            <span class="input-group-text" id="email-icon">
                                <i class="bi bi-envelope-fill"></i>
                            </span>
                            <input type="email" class="form-control" id="forgot-email" placeholder="Email Address" aria-describedby="email-icon" name="email" required>
                        </div>
                        <button type="submit" name="password_reset_link" class="btn btn-tertiary">Send Reset Link</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    


<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>



<script>
    const navLinks = document.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function() {    
            navLinks.forEach(link => link.classList.remove('active'));
            this.classList.add('active');
        });
    });
</script>

<script>
        document.getElementById('createAccountForm').addEventListener('submit', function(event) {
            event.preventDefault(); 

            const formData = new FormData(this); 

            fetch('create_new_account.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.text()) 
                .then(data => {
                    const messageAlert = document.getElementById('account-message-alert');
                    messageAlert.innerHTML = data; 
                    messageAlert.style.display = 'block'; 

                    
                    if (data.trim() === "Account created successfully!") {
                        messageAlert.classList.remove('alert-danger');
                        messageAlert.classList.add('alert-success');

                        
                        setTimeout(() => {
                            const createAccountModal = bootstrap.Modal.getInstance(document.getElementById('createnewaccountLoginModal'));
                            createAccountModal.hide();

                            const staffLoginModal = new bootstrap.Modal(document.getElementById('staffLoginModal'));
                            staffLoginModal.show();
                        }, 2000);
                    } else {
                        
                        messageAlert.classList.remove('alert-success');
                        messageAlert.classList.add('alert-danger');
                    }

                    
                    setTimeout(() => {
                        messageAlert.style.display = 'none';
                    }, 5000);
                })
                .catch(error => {
                    console.error('Error:', error); 
                });
        });
    </script>


<script>
    
    document.getElementById('supervisorLoginForm').addEventListener('submit', function(event) {
        event.preventDefault(); 
        const formData = new FormData(this); 
        formData.append('modalType', 'supervisor'); 

        fetch('login.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json()) 
        .then(data => {
            const messageAlert = document.getElementById('message-alert');
            messageAlert.style.display = 'block';

            messageAlert.classList.remove('alert-danger', 'alert-success');

            if (data.success) {
                messageAlert.classList.add('alert-success');
                messageAlert.innerHTML = 'Login successful! Redirecting...';
                
                setTimeout(() => {
                    window.location.href = data.redirect;
                }, 2000);
            } else {
             
                messageAlert.classList.add('alert-danger');
                messageAlert.innerHTML = data.message;
            }
            setTimeout(() => {
                messageAlert.style.display = 'none';
            }, 5000);
        })
        .catch(error => {
            console.error('Error:', error); 
        });
    });
</script>

<script>
    
    document.getElementById('staffloginForm').addEventListener('submit', function(event) {
        event.preventDefault(); 

        const formData = new FormData(this); 
        formData.append('modalType', 'staff'); 

        fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                const messageAlert = document.getElementById('staff-message-alert');
                messageAlert.style.display = 'block';

              
                messageAlert.classList.remove('alert-danger', 'alert-success');

                if (data.success) {
                    messageAlert.classList.add('alert-success');
                    messageAlert.innerHTML = 'Login successful! Redirecting...';
                    
                   
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 2000);
                } else {
                   
                    messageAlert.classList.add('alert-danger');
                    messageAlert.innerHTML = data.message;
                }
                setTimeout(() => {
                    messageAlert.style.display = 'none';
                }, 5000);
            })
            .catch(error => {
                console.error('Error:', error); 
            });
    });
</script>

<script>
    
    let staff_password = document.getElementById("staff-password");
    let staff_checkbox = document.getElementById("staff-checkbox");

    staff_checkbox.onclick = function() {
        staff_password.type = staff_checkbox.checked ? 'text' : 'password';
    }
</script>

<script>
    
    let supervisor_password = document.getElementById("supervisor-password");
    let supervisor_checkbox = document.getElementById("supervisor-checkbox");

    supervisor_checkbox.onclick = function() {
        supervisor_password.type = supervisor_checkbox.checked ? 'text' : 'password';
    }
</script>

<script>
   
    let account_password = document.getElementById("account-password");
    let account_checkbox = document.getElementById("account-checkbox");

    account_checkbox.onclick = function() {
        account_password.type = account_checkbox.checked ? 'text' : 'password';
    }
</script>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function() {
        
        <?php if (isset($_SESSION['session'])): ?>
            $('#modalMessage').text("<?php echo $_SESSION['session']; ?>");
            $('#myModal').modal('show');
            <?php unset($_SESSION['session']); ?>
        <?php endif; ?>
    });
</script>

<script>
    $(document).ready(function() {
        
        <?php if ($showModal): ?>
            $('#forgotPasswordModal').modal('show');
        <?php endif; ?>

       
        var alertElement = $('#session-alert');
        if (alertElement.length) {
            setTimeout(function() {
                alertElement.fadeOut();
            }, 7000); 
        }
    });
</script>


<script>
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('service-worker.js')
                .then(registration => {
                    console.log('ServiceWorker registered: ', registration);
                })
                .catch(registrationError => {
                    console.log('ServiceWorker registration failed: ', registrationError);
                });
        });
    }
</script>


</body>

</html>
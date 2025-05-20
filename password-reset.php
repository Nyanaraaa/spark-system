<?php
session_start(); 
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Reset Username and Password</title>
    <link rel="stylesheet" href="password-reset.css"> 
</head>

<body>
    <div class="container mt-5"> 
        <div class="row justify-content-center"> 
            <div class="col-md-6"> 

                <div class="card"> 
                    <div class="card-header">
                        <h4 class="text-start">Reset Username and Password</h4> 
                    </div>

                    <div class="card-body">
                        <?php
                        
                        if (isset($_SESSION['status'])) {
                        ?>
                            <div class="row justify-content-center mt-3"> 
                                <div class="col-12"> 
                                    <div class="alert alert-danger text-center" id="alert"> 
                                        <h5><?= $_SESSION['status']; ?></h5> 
                                    </div>
                                </div>
                            </div>
                        <?php
                            unset($_SESSION['status']); 
                        }
                        ?>
                        <form action="password-reset-code.php" method="POST"> 
                            <input type="hidden" name="password_token" value="<?php if (isset($_GET['token'])) {
                                                                                    echo $_GET['token']; 
                                                                                } ?>">
                            <div class="mb-3">
                                <label for="email" class="form-label">Email:
                                    <input type="text" class="form-control" value="<?php if (isset($_GET['email'])) {
                                                                                        echo $_GET['email']; 
                                                                                    } ?>" name="email_address" placeholder="Email" required>
                                </label>
                            </div>
                            <div class="mb-3 input-group">
                                <span class="input-group-text" id="username-icon">
                                    <i class="bi bi-person-fill"></i> 
                                </span>
                                <input type="text" class="form-control" id="username" placeholder="Enter new username" aria-describedby="username-icon" name="new_username">
                            </div>
                            <div class="mb-3 input-group">
                                <span class="input-group-text" id="password-icon">
                                    <i class="bi bi-lock-fill"></i> 
                                </span>
                                <input type="password" class="form-control red-border" id="password" placeholder="Enter new password" aria-describedby="password-icon" name="new_password">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('password')">
                                    <i class="bi-eye-slash" id="toggle-icon-password"></i> 
                                </button>
                            </div>
                            <div class="mb-3 input-group" style="background-color: #f8f9fa;">
                                <span class="input-group-text" id="confirm-password-icon">
                                    <i class="bi bi-shield-lock-fill"></i> 
                                </span>
                                <input type="password" class="form-control red-border" id="confirm-password" placeholder="Confirm password" aria-describedby="confirm-password-icon" name="confirm_password">
                                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword('confirm-password')">
                                    <i class="bi-eye-slash" id="toggle-icon-confirm-password"></i> 
                                </button>
                            </div>

                            <button type="submit" name="password_update" class="btn btn-primary w-100">Submit</button> 
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <script>
     
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            const toggleIcon = document.getElementById(`toggle-icon-${inputId}`);

            if (input.type === "password") {
                input.type = "text"; 
                toggleIcon.classList.remove("bi-eye-slash"); 
                toggleIcon.classList.add("bi-eye");
            } else {
                input.type = "password"; 
                toggleIcon.classList.remove("bi-eye"); 
                toggleIcon.classList.add("bi-eye-slash");
            }
        }

       
        window.onload = function() {
            const alert = document.getElementById('alert');
            if (alert) {
                setTimeout(() => {
                    let opacity = 1;
                    const fadeOutInterval = setInterval(() => {
                        if (opacity <= 0) {
                            clearInterval(fadeOutInterval);
                            alert.style.display = 'none'; 
                        }
                        alert.style.opacity = opacity;
                        opacity -= 0.1; 
                    }, 20); 
                }, 5000); 
            }
        };
    </script>
</body>

</html>

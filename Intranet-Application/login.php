<?php
session_start();

if (!empty($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

    <div class="auth-container">
        <div class="auth-card">
            <div class="brand"><i class="fa-solid fa-rotate"></i> Company</div>
            <h2>Login</h2>
            <p class="subtitle">Welcome back! Please login to your account</p>

            <form onsubmit="validateLogin(event)">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="loginEmail" placeholder="Enter your email" required>
                </div>

                <div class="form-group">
                    <label>Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="loginPass" placeholder="Enter your password" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('loginPass')"></i>
                    </div>
                </div>

                <div class="form-actions">
                    <label><input type="checkbox"> Remember me</label>
                    <a href="forgot-password.php">Forgot Password?</a>
                </div>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>

            <div class="auth-footer">
                <span>New User?</span>
                <a href="register.php" class="btn btn-secondary">Register</a>
            </div>
        </div>
    </div>
   <script src="script.js"></script>
</body>
</html>
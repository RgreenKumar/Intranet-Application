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
    <title>Forgot Password</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card text-center">
            <div class="lock-icon-wrapper">
                <i class="fa-solid fa-lock-open custom-lock"></i>
            </div>
            <h2>Forgot Password</h2>
            <p class="subtitle">Enter your email and a new password to reset access.</p>
            <form onsubmit="validateForgotPassword(event)">
                <div class="form-group text-left">
                    <label>Email</label>
                    <input type="email" id="forgotEmail" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group text-left">
                    <label>New Password</label>
                    <div class="password-wrapper">
                        <input type="password" id="newPassword" name="password" placeholder="Enter new password" required>
                        <i class="fa-solid fa-eye toggle-password" onclick="togglePassword('newPassword')"></i>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary">Reset Password</button>
            </form>
            <div class="auth-footer">
                <a href="login.php" class="btn btn-secondary">Back to Login</a>
            </div>
        </div>
    </div>
    <script src="script.js"></script>
</body>
</html>

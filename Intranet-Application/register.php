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
    <title>Register</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="brand"><i class="fa-solid fa-rotate"></i> Company</div>
            <h2>Create Account</h2>
            <form onsubmit="validateRegister(event)">
                <div class="form-group">
                    <label>Full Name</label>
                    <input type="text" id="regName" name="name" placeholder="Enter your full name" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" id="regEmail" name="email" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" id="regPassword" name="password" placeholder="Enter password" required>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" id="regConfirmPassword" name="confirm_password" placeholder="Confirm your password" required>
                </div>
                <button type="submit" class="btn btn-primary">Register</button>
            </form>
            <div class="auth-footer">
                <span>Already have an account?</span>
                <a href="login.php" class="btn btn-secondary">Login</a>
            </div>
        </div>
    </div>
</body>
<script src="script.js"></script>
</html>
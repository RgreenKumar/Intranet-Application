<?php
http_response_code(403);
?>
<!DOCTYPE html>
<html>
<head>
  <title>Unauthorized</title>
  <style>body{font-family:Arial,Helvetica,sans-serif;display:flex;align-items:center;justify-content:center;height:100vh;background:#f8f8f8} .card{background:#fff;padding:30px;border-radius:8px;box-shadow:0 6px 20px rgba(0,0,0,0.08);text-align:center} h1{color:#c0392b} a{display:inline-block;margin-top:16px;color:#2980b9;text-decoration:none}</style>
</head>
<body>
  <div class="card">
    <h1>403 — Unauthorized</h1>
    <p>You do not have permission to access this page.</p>
    <a href="login.php">Return to Login</a>
  </div>
</body>
</html>
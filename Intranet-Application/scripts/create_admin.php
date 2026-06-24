<?php
if ($argc < 3) {
    echo "Usage: php scripts/create_admin.php <email> <password> [name]\n";
    exit(1);
}

$email = $argv[1];
$password = $argv[2];
$name = $argv[3] ?? 'Admin';

$servername = 'localhost';
$username = 'root';
$dbpassword = '';
$dbname = 'users_db';

$hash = password_hash($password, PASSWORD_DEFAULT);

$conn = new mysqli($servername, $username, $dbpassword, $dbname);
if ($conn->connect_error) {
    die('Connection failed: ' . $conn->connect_error . "\n");
}

$stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, 'ADMIN')");
$stmt->bind_param('sss', $name, $email, $hash);
if ($stmt->execute()) {
    echo "Admin user created with id: " . $stmt->insert_id . "\n";
} else {
    echo "Error creating admin: " . $stmt->error . "\n";
}
$stmt->close();
$conn->close();
?>
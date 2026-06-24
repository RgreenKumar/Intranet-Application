<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Return proper JSON for any uncaught exception instead of HTML crash page
set_exception_handler(function (Throwable $e) {
    if (!headers_sent()) { header('Content-Type: application/json'); }
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage(), 'file' => basename($e->getFile()), 'line' => $e->getLine()]);
    exit();
});

session_start();

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit();
}

require_once 'includes/db.php';

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

switch ($method) {
    case 'GET':
        if ($action === 'getUsers') {
            getUsers();
        } else {
            http_response_code(400);
            echo json_encode(["error" => "Invalid action for GET request"]);
        }
        break;

    case 'POST':
        switch ($action) {
            case 'login':
                loginUser();
                break;
            case 'register':
                registerUser();
                break;
            case 'profileUpdate':
                profileUpdate();
                break;
            case 'productCreate':
                productCreate();
                break;
            case 'forgetPassword':
                forgetPassword();
                break;
            default:
                http_response_code(400);
                echo json_encode(["success" => false, "message" => "Invalid action for POST request"]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method not allowed"]);
        break;
}

function loginUser() {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($email) || empty($password)) {
        echo json_encode(["success" => false, "message" => "Email and password are required."]);
        return;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $stmt->close();

    if (!$user || !password_verify($password, $user['password'])) {
        echo json_encode(["success" => false, "message" => "Invalid email or password."]);
        return;
    }

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['role'] = strtoupper($user['role']);

    echo json_encode([
        "success" => true,
        "message" => "Login successful.",
        "role" => $_SESSION['role']
    ]);
}

function forgetPassword(){
    $email = trim($_POST['email'] ?? '');
    $newPassword = trim($_POST['newPassword'] ?? ($_POST['password'] ?? ''));
        
    if (empty($email) || empty($newPassword)) {
        echo json_encode(["success" =>  false, "message" => "Email and New Password are required."]);
        return;
    }

    if (strlen($newPassword) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters. "]);
        return;
    }

    $conn = getConnection();
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
    $update = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
    $update->bind_param("ss", $hashedPassword, $email);

    if ($update->execute() && $update->affected_rows>0){
        echo json_encode(["success" => true, "message" => "Password reset successfully. You can now login."]);
    } else {
        echo json_encode(["success" => false, "message" => "No account found with this email."]);
    }
    $update->close();
}

function getUsers() {
    $conn = getConnection();
    $result = $conn->query("SELECT id, name, email, role FROM users");
    $users = [];

    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }

    echo json_encode($users);
    $conn->close();
}


function registerUser() {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '' );
    $password = trim($_POST['password'] ?? '');
    $confirmPassword = trim($_POST['confirm_password'] ?? '');

    if (empty($name) || empty($email) || empty($password) || empty($confirmPassword)) {
        echo json_encode(["success" => false, "message" => "Please fill in all fields."]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Please enter a valid email address."]);
        return;
    }

    if ($password !== $confirmPassword) {
        echo json_encode(["success" => false, "message" => "Passwords do not match."]);
        return;
    }

    if (strlen($password) < 6) {
        echo json_encode(["success" => false, "message" => "Password must be at least 6 characters."]);
        return;
    }

    $conn = getConnection();
    $check = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $check->store_result();
    if ($check->num_rows > 0) {
        $check->close();
        echo json_encode(["success" => false, "message" => "An account with this email already exists."]);
        return;
    }
    
    $check->close();
    
    $countResult = $conn->query("SELECT COUNT(*) AS total FROM users");
    $row = $countResult->fetch_assoc();
    $role = ($row['total'] == 0) ? 'ADMIN' : 'USER';
    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $hashPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt->bind_param("ssss", $name, $email, $hashPassword, $role);

    if ($stmt->execute()) {
        echo json_encode([
            "success" => true,
            "message" => "Registration successful. You can now login."
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Registration failed: " . $stmt->error]);
    }

    $stmt->close();
}

function profileUpdate() {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $currentPassword = trim($_POST['current_password'] ?? '');
    $newPassword = trim($_POST['new_password'] ?? '');

    if (empty($name) || empty($email)) {
        echo json_encode(["success" => false, "message" => "Please complete all profile fields."]);
        return;
    }

    $userId = $_SESSION['user_id'] ?? null;
    if (!$userId) {
        echo json_encode(["success" => false, "message" => "User not logged in."]);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(["success" => false, "message" => "Please enter a valid email address."]);
        return;
    }

    $conn = getConnection();
    $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if ($currentPassword && !password_verify($currentPassword, $row['password'])) {
        echo json_encode(["success" => false, "message" => "Current password is incorrect."]);
        return;
    }

    if ($newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $name, $email, $hashedPassword, $userId);
    } else {
        $stmt = $conn->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $name, $email, $userId);
    }

    if ($stmt->execute()) {
        echo json_encode(["success" => true, "message" => "Profile updated successfully."]);
    } else {
        echo json_encode(["success" => false, "message" => "Unable to update profile."]);
    }

    $stmt->close();
}

function productCreate() {
    $name        = trim($_POST['product_name'] ?? '');
    $description = trim($_POST['product_description'] ?? '');
    $url         = trim($_POST['product_url'] ?? ($_POST['tool_url'] ?? ''));
    $status      = trim($_POST['status'] ?? 'Active');

    if (empty($name)) {
        echo json_encode(["success" => false, "message" => "Product name is required."]);
        return;
    }
    if (empty($url)) {
        echo json_encode(["success" => false, "message" => "Tool URL is required."]);
        return;
    }

    $conn = getConnection();
    $existingCols = [];
    $colRes = $conn->query("SHOW COLUMNS FROM products");
    if ($colRes) {
        while ($col = $colRes->fetch_assoc()) {
            $existingCols[] = $col['Field'];
        }
    } else {
        $conn->query("CREATE TABLE IF NOT EXISTS products (
            id          INT AUTO_INCREMENT PRIMARY KEY,
            name        VARCHAR(255) NOT NULL,
            description TEXT,
            tool_url    VARCHAR(1024),
            image_path  VARCHAR(1024) DEFAULT NULL,
            status      ENUM('Active','Inactive') DEFAULT 'Active',
            created_by  INT DEFAULT 0,
            created_at  DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
        $existingCols = ['id','name','description','tool_url','image_path','status','created_by','created_at'];
    }

    if (!in_array('created_by', $existingCols)) {
        $conn->query("ALTER TABLE products ADD COLUMN created_by INT DEFAULT 0");
        $existingCols[] = 'created_by';
    }
    if (!in_array('image_path', $existingCols)) {
        $conn->query("ALTER TABLE products ADD COLUMN image_path VARCHAR(1024) DEFAULT NULL");
        $existingCols[] = 'image_path';
    }
    if (!in_array('status', $existingCols)) {
        $conn->query("ALTER TABLE products ADD COLUMN status ENUM('Active','Inactive') DEFAULT 'Active'");
        $existingCols[] = 'status';
    }
    if (!in_array('tool_url', $existingCols)) {
        $conn->query("ALTER TABLE products ADD COLUMN tool_url VARCHAR(1024)");
        $existingCols[] = 'tool_url';
    }

    $imagePath = null;
    if (!empty($_FILES['product_image']['name']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
        $allowed   = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $mimeType  = mime_content_type($_FILES['product_image']['tmp_name']);
        $maxSize   = 2 * 1024 * 1024; // 2 MB

        if (!in_array($mimeType, $allowed)) {
            echo json_encode(["success" => false, "message" => "Image must be JPG, PNG, GIF or WEBP."]);
            return;
        }
        if ($_FILES['product_image']['size'] > $maxSize) {
            echo json_encode(["success" => false, "message" => "Image must be under 2 MB."]);
            return;
        }

        $uploadDir = __DIR__ . '/uploads/products/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $ext       = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
        $filename  = 'product_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $destPath  = $uploadDir . $filename;

        if (move_uploaded_file($_FILES['product_image']['tmp_name'], $destPath)) {
            $imagePath = 'uploads/products/' . $filename;
        }
    }

    $createdBy = $_SESSION['user_id'] ?? 0;

    $stmt = $conn->prepare(
        "INSERT INTO products (name, description, tool_url, image_path, status, created_by) VALUES (?, ?, ?, ?, ?, ?)"
    );
    if (!$stmt) {
        echo json_encode(["success" => false, "message" => "DB prepare failed: " . $conn->error]);
        return;
    }
    $stmt->bind_param("sssssi", $name, $description, $url, $imagePath, $status, $createdBy);

    if ($stmt->execute()) {
        echo json_encode([
            "success"    => true,
            "message"    => "Product created successfully",
            "id"         => $stmt->insert_id,
            "image_path" => $imagePath
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Create failed: " . $stmt->error]);
    }

    $stmt->close();
}
?>
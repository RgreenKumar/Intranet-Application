<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_role('ADMIN');

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    header("Location: ../index.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name        = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $tool_url    = trim($_POST['tool_url'] ?? '');
    $status      = $_POST['status'] ?? 'Active';

    if (empty($name) || empty($tool_url)) {
        $error = "Product name and Tool URL are required.";
    } else {
        $upd = $conn->prepare("UPDATE products SET name=?, description=?, tool_url=?, status=? WHERE id=?");
        $upd->bind_param("ssssi", $name, $description, $tool_url, $status, $id);
        if ($upd->execute()) {
            header("Location: ../index.php");
            exit();
        } else {
            $error = "Failed to update product.";
        }
        $upd->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product</title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
<div class="auth-container" style="min-height:100vh; align-items:flex-start; padding-top:40px;">
    <div class="form-container-card" style="max-width:560px; width:100%; margin:0 auto;">
        <div style="display:flex; align-items:center; gap:12px; margin-bottom:24px;">
            <a href="../index.php" style="color:var(--text-muted); text-decoration:none; font-size:14px;"><i class="fa-solid fa-arrow-left"></i> Back to Dashboard</a>
        </div>
        <h3><i class="fa-solid fa-pen-to-square" style="color:var(--primary-color); margin-right:8px;"></i>Edit Product</h3>
        <p class="text-muted" style="font-size:13px; margin-bottom:24px;">Update the product details below.</p>

        <?php if ($error): ?>
            <div style="background:#fee2e2; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:16px; font-size:14px;">
                <i class="fa-solid fa-circle-exclamation"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label>Product Name *</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" placeholder="Enter product name" required>
            </div>
            <div class="form-group">
                <label>Description</label>
                <textarea name="description" rows="4" placeholder="Enter product description" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px; font-family:inherit; font-size:14px; resize:vertical;"><?php echo htmlspecialchars($product['description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label>Tool URL *</label>
                <input type="url" name="tool_url" value="<?php echo htmlspecialchars($product['tool_url']); ?>" placeholder="https://example.com/tool" required>
            </div>
            <div class="form-group">
                <label>Status</label>
                <select name="status" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px; font-size:14px; background:var(--bg-primary);">
                    <option value="Active" <?php if($product['status']==='Active') echo 'selected'; ?>>Active</option>
                    <option value="Inactive" <?php if($product['status']==='Inactive') echo 'selected'; ?>>Inactive</option>
                </select>
            </div>
            <div class="form-buttons">
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk"></i> Save Changes</button>
                <a href="../index.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
</body>
</html>

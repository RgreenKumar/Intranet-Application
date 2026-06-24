<?php
require_once 'includes/auth.php';
require_login();
$currentUserRole = $_SESSION['role'] ?? 'USER';
$currentUserName = $_SESSION['user_name'] ?? 'User';
require_once 'includes/db.php';

$products = [];
$res = $conn->query("SELECT id, name, description, tool_url, image_path, status, created_at FROM products WHERE status='Active'");
if ($res) {
    while ($r = $res->fetch_assoc()) {
        $products[] = $r;
    }
}

$allProducts = [];
$resAll = $conn->query("SELECT id, name, description, tool_url, image_path, status, created_at FROM products ORDER BY created_at DESC");
if ($resAll) {
    while ($r = $resAll->fetch_assoc()) {
        $allProducts[] = $r;
    }
}

$totalProducts = count($allProducts);
$activeProducts = count(array_filter($allProducts, fn($p) => $p['status'] === 'Active'));
$inactiveProducts = $totalProducts - $activeProducts;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body data-role="<?php echo strtolower($_SESSION['role'] ?? 'user'); ?>">
    <div id="appLayout" class="app-layout view-section hidden">
        <aside class="sidebar">
            <div class="sidebar-brand">
                <i class="fa-solid fa-rotate"></i> Company
            </div>
            <nav class="sidebar-menu">
                <div id="userMenu">
                    <a href="#" class="active" onclick="switchDashboardTab('user-home')"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                    <a href="#" onclick="switchDashboardTab('user-products')"><i class="fa-solid fa-box"></i> Products</a>
                    <a href="#" onclick="switchDashboardTab('user-profile')"><i class="fa-solid fa-user"></i> Profile</a>
                </div>
                <div id="adminMenu" class="hidden">
                    <a href="#" class="active" onclick="switchDashboardTab('admin-home')"><i class="fa-solid fa-chart-pie"></i> Dashboard</a>
                    <a href="#" onclick="switchDashboardTab('product-management')"><i class="fa-solid fa-box"></i> Products</a>
                    <a href="#" onclick="switchDashboardTab('settings')"><i class="fa-solid fa-gear"></i> Settings</a>
                </div>
                <button onclick="window.location.href='logout.php'"><i class="fa-solid fa-arrow-right-from-bracket"></i>Logout</button>
            </nav>
        </aside>

        <div class="main-wrapper">
            <header class="top-navbar">
                <div class="view-title" id="currentViewTitle">Dashboard</div>
                <div class="header-right">
                    <div class="notification-bell">
                        <i class="fa-regular fa-bell"></i>
                        <span class="badge">1</span>
                    </div>
                    <div class="user-avatar-wrapper">
                        <div class="nav-initials-avatar">
                            <?php
                                $np = explode(' ', trim($_SESSION['user_name'] ?? 'U'));
                                echo strtoupper(substr($np[0],0,1) . (isset($np[1]) ? substr($np[1],0,1) : ''));
                            ?>
                        </div>
                        <i class="fa-solid fa-chevron-down"></i>
                    </div>
                </div>
            </header>

            <main class="content-body">
                <div id="user-home" class="tab-content">
                    <h3 class="section-title">Project Tools</h3>
                    <div class="tools-grid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $p): ?>
                                <div class="tool-card">
                                    <?php if (!empty($p['image_path']) && file_exists(__DIR__ . '/' . $p['image_path'])): ?>
                                        <div class="tool-thumb"><img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"></div>
                                    <?php else: ?>
                                        <div class="tool-thumb tool-thumb-placeholder"><i class="fa-solid fa-box"></i></div>
                                    <?php endif; ?>
                                    <div class="tool-card-body">
                                        <div class="tool-header">
                                            <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                                        </div>
                                        <p><?php echo htmlspecialchars($p['description'] ?? ''); ?></p>
                                        <button class="btn btn-tool" onclick="window.open('<?php echo htmlspecialchars($p['tool_url']); ?>','_blank')">Open Tool</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-muted" style="padding:20px;">No active products available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="user-products" class="tab-content hidden">
                    <div class="table-header-action">
                        <div>
                            <h3>Products</h3>
                        </div>
                        <div class="search-input-wrapper" style="max-width: 300px;">
                            <i class="fa-solid fa-magnifying-glass search-icon"></i>
                            <input type="text" id="userProductSearch" placeholder="Search my tools..." onkeyup="filterUserProducts()">
                        </div>
                    </div>

                    <div class="tools-grid" id="userProductsGrid">
                        <?php if (!empty($products)): ?>
                            <?php foreach ($products as $p): ?>
                                <div class="tool-card user-prod-item">
                                    <!-- <div class="tool-status-ribbon <?php echo ($p['status']==='Active') ? 'status-active' : 'status-inactive'; ?>"><?php echo ($p['status']==='Active') ? 'Active Access' : 'Paused'; ?></div> -->
                                    <?php if (!empty($p['image_path']) && file_exists(__DIR__ . '/' . $p['image_path'])): ?>
                                        <div class="tool-thumb"><img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"></div>
                                    <?php else: ?>
                                        <div class="tool-thumb tool-thumb-placeholder"><i class="fa-solid fa-box"></i></div>
                                    <?php endif; ?>
                                    <div class="tool-card-body">
                                        <div class="tool-header">
                                            <h4 class="prod-title"><?php echo htmlspecialchars($p['name']); ?></h4>
                                        </div>
                                        <p><?php echo htmlspecialchars($p['description'] ?? ''); ?></p>
                                        <div class="user-card-footer">
                                            <span class="text-muted"><i class="fa-regular fa-clock"></i> Added: <?php echo htmlspecialchars($p['created_at'] ?? 'N/A'); ?></span>
                                            <?php if ($p['status'] === 'Active'): ?>
                                                <button class="btn btn-tool" onclick="window.open('<?php echo htmlspecialchars($p['tool_url']); ?>','_blank')">Launch <i class="fa-solid fa-arrow-right"></i></button>
                                            <?php else: ?>
                                                <button class="btn btn-tool" disabled style="opacity:0.5; cursor:not-allowed;">Locked</button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p>No products available.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="user-profile" class="tab-content hidden">
                    <div class="settings-split-container">
                        <div class="settings-profile-sidebar">
                            <div class="profile-card-summary">
                                <div class="profile-initials-avatar">
                                    <?php
                                        $nameParts = explode(' ', trim($_SESSION['user_name'] ?? 'U'));
                                        $initials = strtoupper(substr($nameParts[0], 0, 1) . (isset($nameParts[1]) ? substr($nameParts[1], 0, 1) : ''));
                                        echo htmlspecialchars($initials);
                                    ?>
                                </div>
                                <h4><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'User'); ?></h4>
                                <span class="badge-status badge-info"><?php echo ($_SESSION['role'] ?? 'USER') === 'ADMIN' ? 'Admin' : 'User'; ?></span>
                                <p class="text-muted profile-email-display"><?php echo htmlspecialchars($_SESSION['user_email'] ?? ''); ?></p>
                            </div>
                        </div>
                        <div class="form-container-card" style="max-width: 100%;">
                            <h3>My Profile</h3>
                            <div class="profile-info-row">
                                <div class="profile-info-icon"><i class="fa-solid fa-user"></i></div>
                                <div>
                                    <span class="profile-info-label">Full Name</span>
                                    <span class="profile-info-value"><?php echo htmlspecialchars($_SESSION['user_name'] ?? '—'); ?></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-icon"><i class="fa-solid fa-envelope"></i></div>
                                <div>
                                    <span class="profile-info-label">Email Address</span>
                                    <span class="profile-info-value"><?php echo htmlspecialchars($_SESSION['user_email'] ?? '—'); ?></span>
                                </div>
                            </div>
                            <div class="profile-info-row">
                                <div class="profile-info-icon"><i class="fa-solid fa-shield-halved"></i></div>
                                <div>
                                    <span class="profile-info-label">Role</span>
                                    <span class="profile-info-value"><?php echo htmlspecialchars($_SESSION['role'] ?? 'USER'); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div id="admin-home" class="tab-content hidden">
                    <div class="metrics-grid">
                        <div class="metric-card" style="border-left-color:#6366f1;">
                            <i class="fa-solid fa-boxes-stacked metric-icon" style="color:#6366f1;"></i>
                            <span>Total Products</span>
                            <h2><?php echo $totalProducts; ?></h2>
                        </div>
                        <div class="metric-card" style="border-left-color:#10b981;">
                            <i class="fa-solid fa-circle-check metric-icon" style="color:#10b981;"></i>
                            <span>Active Products</span>
                            <h2 class="text-success"><?php echo $activeProducts; ?></h2>
                        </div>
                        <div class="metric-card" style="border-left-color:#ef4444;">
                            <i class="fa-solid fa-circle-xmark metric-icon" style="color:#ef4444;"></i>
                            <span>Inactive Products</span>
                            <h2 class="text-danger"><?php echo $inactiveProducts; ?></h2>
                        </div>
                        <div class="metric-card" style="border-left-color:#0ea5e9;">
                            <i class="fa-solid fa-users metric-icon" style="color:#0ea5e9;"></i>
                            <span>Total Users</span>
                            <h2 style="color:#0ea5e9;"><?php $ur = $conn->query("SELECT COUNT(*) AS c FROM users"); $uc = ($ur && $row = $ur->fetch_assoc()) ? $row['c'] : 0; echo $uc; ?></h2>
                        </div>
                    </div>

                    <h3 class="section-title" style="margin-top:28px;">All Products</h3>
                    <div class="tools-grid">
                        <?php if (!empty($allProducts)): ?>
                            <?php foreach ($allProducts as $p): ?>
                                <div class="tool-card">
                                    <?php if (!empty($p['image_path']) && file_exists(__DIR__ . '/' . $p['image_path'])): ?>
                                        <div class="tool-thumb"><img src="<?php echo htmlspecialchars($p['image_path']); ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"></div>
                                    <?php else: ?>
                                        <div class="tool-thumb tool-thumb-placeholder"><i class="fa-solid fa-box"></i></div>
                                    <?php endif; ?>
                                    <div class="tool-card-body">
                                        <div class="tool-header" style="justify-content:space-between;">
                                            <h4><?php echo htmlspecialchars($p['name']); ?></h4>
                                            <span class="status-badge <?php echo ($p['status']==='Active') ? 'status-active' : 'status-inactive'; ?>"><?php echo $p['status']; ?></span>
                                        </div>
                                        <p><?php echo htmlspecialchars($p['description'] ?? ''); ?></p>
                                        <div class="tool-card-actions">
                                            <a href="admin/edit-product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm-outline"><i class="fa-solid fa-pen"></i> Edit</a>
                                            <a href="admin/delete-product.php?id=<?php echo $p['id']; ?>" class="btn btn-sm-danger" onclick="return confirm('Delete this product?')"><i class="fa-solid fa-trash"></i> Delete</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <div style="color:var(--text-muted); padding:20px;">No products yet. <a href="#" onclick="switchDashboardTab('create-product')">Create one now.</a></div>
                        <?php endif; ?>
                    </div>
                </div>

                <div id="product-management" class="tab-content hidden">
                    <div class="table-header-action">
                        <h3>Product Management</h3>
                        <button class="btn btn-primary btn-sm" onclick="switchDashboardTab('create-product')"><i class="fa-solid fa-plus"></i> Create Product</button>
                    </div>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>URL</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($allProducts)): ?>
                                    <?php foreach ($allProducts as $p): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($p['name']); ?></td>
                                            <td class="text-muted"><?php echo htmlspecialchars($p['tool_url']); ?></td>
                                            <td><span class="status-badge <?php echo ($p['status']==='Active') ? 'status-active' : 'status-inactive'; ?>"><?php echo htmlspecialchars($p['status']); ?></span></td>
                                            <td>
                                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'ADMIN'): ?>
                                                    <a class="btn-icon" href="admin/edit-product.php?id=<?php echo $p['id']; ?>">Edit</a>
                                                    <a class="btn-icon text-danger" href="admin/delete-product.php?id=<?php echo $p['id']; ?>" onclick="return confirm('Delete this product?')">Delete</a>
                                                <?php else: ?>
                                                    <span class="text-muted">View only</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4">No products found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div id="create-product" class="tab-content hidden">
                    <div class="form-container-card">
                        <h3>Create New Product</h3>
                        <form onsubmit="adminCreateProduct(event)">
                            <div class="form-group">
                                <label>Product Name *</label>
                                <input type="text" id="productName" name="product_name" placeholder="Enter product name" required>
                            </div>
                            <div class="form-group">
                                <label>Product Image</label>
                                <div class="upload-zone" id="uploadZone" onclick="document.getElementById('productImageInput').click()" style="cursor:pointer;">
                                    <i class="fa-solid fa-cloud-arrow-up" id="uploadIcon"></i>
                                    <p id="uploadLabel">Upload Image</p>
                                    <span id="uploadSub">JPG, PNG, WEBP — Max 2MB</span>
                                    <img id="uploadPreview" src="" alt="Preview" style="display:none; max-width:100%; max-height:160px; border-radius:8px; margin-top:10px; object-fit:cover;">
                                </div>
                                <input type="file" id="productImageInput" name="product_image" accept="image/*" style="display:none"
                                    onchange="previewProductImage(this)">
                            </div>
                            <div class="form-group">
                                <label>Description</label>
                                <textarea id="productDescription" name="product_description" rows="4" placeholder="Enter product description"></textarea>
                            </div>
                            <div class="form-group">
                                <label>Tool URL *</label>
                                <input type="url" id="productUrl" name="product_url" placeholder="https://example.com/tool" required>
                            </div>
                            <div class="form-group">
                                <label>Status</label>
                                <select id="productStatus" name="status" style="width:100%; padding:10px; border:1px solid var(--border-color); border-radius:8px; font-size:14px; background:var(--bg-primary);">
                                    <option value="Active">Active</option>
                                    <option value="Inactive">Inactive</option>
                                </select>
                            </div>
                            <div class="form-buttons">
                                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Create Product</button>
                                <button type="button" class="btn btn-secondary" onclick="switchDashboardTab('product-management')">Cancel</button>
                            </div>
                        </form>
                    </div>
                </div>

            </main>
        </div>
    </div>

</body>
    <script src="script.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('appLayout').classList.remove('hidden');
            const role = document.body.getAttribute('data-role') || 'user';
            setDashboardRole(role === 'admin' ? 'admin' : 'user');
        });
    </script>
</html>
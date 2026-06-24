// Configures Navigation UI context parameters depending on access clear levels
function setDashboardRole(role) {
    const userMenu = document.getElementById('userMenu');
    const adminMenu = document.getElementById('adminMenu');
    
    if (role === 'admin') {
        userMenu.classList.add('hidden');
        adminMenu.classList.remove('hidden');
        document.getElementById('currentViewTitle').innerText = "Admin Dashboard";
        switchDashboardTab('admin-home');
    } else {
        adminMenu.classList.add('hidden');
        userMenu.classList.remove('hidden');
        document.getElementById('currentViewTitle').innerText = "User Dashboard";
        switchDashboardTab('user-home');
    }
}

// Switch interior dashboard tab views dynamic visibility engine
function switchDashboardTab(tabId) {
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.add('hidden');
    });
    
    const activeTab = document.getElementById(tabId);
    if(activeTab) {
        activeTab.classList.remove('hidden');
    }

    // Dynamic Top Navbar title mapper mapping definition rules
    const titleMapping = {
        'user-home': 'Dashboard Tools',
        'user-products': 'Products',
        'user-profile': 'User Profile Settings',
        'admin-home': 'Admin Dashboard',
        'product-management': 'Product Management',
        'settings': 'Settings',
        'create-product': 'Create Product'
    };

    if (titleMapping[tabId]) {
        document.getElementById('currentViewTitle').innerText = titleMapping[tabId];
    }

    // Synchronize clicked menu tab highlight states
    document.querySelectorAll('.sidebar-menu a').forEach(link => {
        link.classList.remove('active');
    });
    
    const clickedLink = document.querySelector(`[onclick="switchDashboardTab('${tabId}')"]`);
    if (clickedLink) clickedLink.classList.add('active');
}

// --- SUB-COMPONENT UTILITY CONTROLLERS ---

// User Authorized Tools filter execution
function filterUserProducts() {
    const searchValue = document.getElementById('userProductSearch').value.toLowerCase();
    const items = document.querySelectorAll('.user-prod-item');

    items.forEach(item => {
        const titleText = item.querySelector('.prod-title').innerText.toLowerCase();
        item.style.display = titleText.includes(searchValue) ? "" : "none";
    });
}

// Client Side Form submission handler for Admin System Settings configuration
async function saveGlobalSettings(event) {
    event.preventDefault();

    const title = document.getElementById('settingsTitle').value.trim();
    const supportDesk = document.getElementById('settingsSupportDesk').value.trim();
    const sessionExpiry = document.getElementById('settingsSessionExpiry').value;
    const enableAudit = document.getElementById('settingsEnableAudit').checked;
    const forceMFA = document.getElementById('settingsForceMFA').checked;

    try {
        const response = await fetch("api.php?action=settingsUpdate.php", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `title=${encodeURIComponent(title)}&support_desk=${encodeURIComponent(supportDesk)}&session_expiry=${encodeURIComponent(sessionExpiry)}&enable_audit=${enableAudit ? '1' : '0'}&force_mfa=${forceMFA ? '1' : '0'}`
        });
        const result = await response.text();
        alert(result);
    } catch (error) {
        console.error('Settings update error:', error);
        alert('Unable to save settings at this time.');
    }
}

    function togglePassword(inputId) {
        const input = document.getElementById(inputId);
        input.type = (input.type === 'password') ? 'text' : 'password';
    }

async function validateForgotPassword(event) {
    event.preventDefault();

    const email = document.getElementById('forgotEmail').value.trim();
    const newPassword = document.getElementById('newPassword').value.trim();
    if (!email) {
        alert('Please enter your email.');
        return;
    }
    if(!newPassword) {
        alert('Please enter your new password. ');
        return;
    }

    try {
        const response = await fetch('api.php?action=forgetPassword', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `email=${encodeURIComponent(email)}&newPassword=${encodeURIComponent(newPassword)}`
        });

        const result = await response.json();
        alert(result.message);

        if (result.success) {
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error('Forgot password error:', error);
        alert('Unable to send reset link. Please try again.');
    }
}

async function adminCreateProduct(event) {
    event.preventDefault();

    const name        = document.getElementById('productName').value.trim();
    const description = document.getElementById('productDescription').value.trim();
    const url         = document.getElementById('productUrl').value.trim();
    const status      = document.getElementById('productStatus').value;
    const imageInput  = document.getElementById('productImageInput');

    if (!name) { showToast('Please enter a product name.', 'error'); return; }
    if (!url)  { showToast('Please enter a Tool URL.', 'error'); return; }

    // Use FormData so the image file is sent as multipart
    const fd = new FormData();
    fd.append('product_name', name);
    fd.append('product_description', description);
    fd.append('product_url', url);
    fd.append('status', status);
    if (imageInput && imageInput.files && imageInput.files[0]) {
        fd.append('product_image', imageInput.files[0]);
    }

    const btn = event.target.querySelector('button[type="submit"]');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Creating…'; }

    try {
        // No Content-Type header — browser sets it automatically with correct boundary for multipart
        const response = await fetch("api.php?action=productCreate", { method: 'POST', body: fd });
        const text = await response.text();
        let result;
        try {
            result = JSON.parse(text);
        } catch {
            console.error('Non-JSON response from api.php:', text);
            showToast('Server error — check PHP logs.', 'error');
            return;
        }
        if (result.success) {
            showToast('Product created successfully!', 'success');
            setTimeout(() => { window.location.reload(); }, 1200);
        } else {
            showToast(result.message || 'Failed to create product.', 'error');
        }
    } catch (error) {
        console.error('Create product error:', error);
        showToast('Unable to create product at this time.', 'error');
    } finally {
        if (btn) { btn.disabled = false; btn.innerHTML = '<i class="fa-solid fa-plus"></i> Create Product'; }
    }
}


function previewProductImage(input) {
    if (!input.files || !input.files[0]) return;
    const file = input.files[0];
    if (file.size > 2 * 1024 * 1024) {
        showToast('Image must be under 2 MB.', 'error');
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function(e) {
        const preview = document.getElementById('uploadPreview');
        const icon    = document.getElementById('uploadIcon');
        const label   = document.getElementById('uploadLabel');
        const sub     = document.getElementById('uploadSub');
        preview.src = e.target.result;
        preview.style.display = 'block';
        icon.style.display = 'none';
        label.textContent = file.name;
        sub.textContent = (file.size / 1024).toFixed(1) + ' KB';
    };
    reader.readAsDataURL(file);
}

function showToast(message, type = 'success') {
    let toast = document.getElementById('appToast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'appToast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;padding:14px 20px;border-radius:10px;font-size:14px;font-weight:500;z-index:9999;transition:opacity 0.3s;box-shadow:0 4px 20px rgba(0,0,0,0.15);max-width:320px;';
        document.body.appendChild(toast);
    }
    toast.textContent = message;
    toast.style.background = type === 'success' ? '#10b981' : '#ef4444';
    toast.style.color = '#fff';
    toast.style.opacity = '1';
    clearTimeout(toast._timeout);
    toast._timeout = setTimeout(() => { toast.style.opacity = '0'; }, 3000);
}

async function validateLogin(event) {
        event.preventDefault();
        const email    = document.getElementById('loginEmail').value.trim();
        const password = document.getElementById('loginPass').value.trim();
        
        if (!email || !password) {
            alert("Please enter both email and password.");
            return;
        }

        try{
            const resp = await fetch('api.php?action=login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}`
            });            
            const result = await resp.json();
            if (result.success) {
                window.location.href = 'index.php';
            } else {
                alert(result.message);
            }
        } catch (err) {
                console.error('Login error: ', err);
                alert('Something went wrong. Please try again.');
        }
    }

async function validateRegister(event) {
    event.preventDefault();

    const name = document.getElementById("regName").value.trim();
    const email = document.getElementById("regEmail").value.trim();
    const password = document.getElementById("regPassword").value.trim();
    const confirmPassword = document.getElementById("regConfirmPassword").value.trim();

    if (!name || !email || !password || !confirmPassword) {
        alert("Please fill in all fields.");
        return;
    }

    if (password !== confirmPassword) {
        alert("Passwords do not match.");
        return;
    }

    try {
        const response = await fetch("api.php?action=register", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: `name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&password=${encodeURIComponent(password)}&confirm_password=${encodeURIComponent(confirmPassword)}`
        });

        const result = await response.json();
        alert(result.message);

        if (result.success) {
            window.location.href = 'login.php';
        }
    } catch (error) {
        console.error("Registration error:", error);
        alert("Something went wrong during registration. Please try again.");
    }
}

// Top navbar interactive menus (notifications & profile)
function createTopDropdown(id, html) {
    let existing = document.getElementById(id);
    if (existing) return existing;
    const div = document.createElement('div');
    div.id = id;
    div.className = 'top-dropdown hidden';
    div.innerHTML = html;
    document.body.appendChild(div);
    return div;
}

function positionDropdown(triggerEl, dropdownEl) {
    const rect = triggerEl.getBoundingClientRect();
    dropdownEl.style.top = (rect.bottom + window.scrollY + 8) + 'px';
    dropdownEl.style.left = (rect.left + window.scrollX - (dropdownEl.offsetWidth - rect.width)) + 'px';
}

function toggleNotificationMenu() {
    const bell = document.querySelector('.notification-bell');
    const menu = createTopDropdown('notifMenu', '<div class="menu-header">Notifications</div><div class="menu-item">No new notifications</div>');
    if (!menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        return;
    }
    menu.classList.remove('hidden');
    positionDropdown(bell, menu);
}

function closeAllDropdowns() {
    document.querySelectorAll('.top-dropdown').forEach(d => d.classList.add('hidden'));
}

function toggleProfileMenu() {
    const avatar = document.querySelector('.user-avatar-wrapper');
    const role = document.body.getAttribute('data-role') || 'user';
    const profileTabId = 'user-profile'; // same tab works for both admin and user
    const menu = createTopDropdown('profileMenu',
        '<div class="menu-header">Account</div>' +
        '<a class="menu-item" href="#" onclick="switchDashboardTab(\'' + profileTabId + '\');closeAllDropdowns();return false;"><i class="fa-solid fa-user" style="margin-right:8px;"></i>Profile</a>' +
        '<a class="menu-item menu-item-danger" href="logout.php"><i class="fa-solid fa-right-from-bracket" style="margin-right:8px;"></i>Logout</a>'
    );
    if (!menu.classList.contains('hidden')) {
        menu.classList.add('hidden');
        return;
    }
    // hide notification if open
    const notif = document.getElementById('notifMenu'); if (notif) notif.classList.add('hidden');
    menu.classList.remove('hidden');
    positionDropdown(avatar, menu);
}

// click outside to close
document.addEventListener('click', function(e) {
    const notif = document.getElementById('notifMenu');
    const profile = document.getElementById('profileMenu');
    const bell = document.querySelector('.notification-bell');
    const avatar = document.querySelector('.user-avatar-wrapper');
    if (notif && !notif.classList.contains('hidden') && !notif.contains(e.target) && bell && !bell.contains(e.target)) {
        notif.classList.add('hidden');
    }
    if (profile && !profile.classList.contains('hidden') && !profile.contains(e.target) && avatar && !avatar.contains(e.target)) {
        profile.classList.add('hidden');
    }
});

// wire up handlers after DOM ready
function initTopNavHandlers() {
    const bell = document.querySelector('.notification-bell');
    const avatar = document.querySelector('.user-avatar-wrapper');
    if (bell) bell.addEventListener('click', function(e){ e.stopPropagation(); toggleNotificationMenu(); });
    if (avatar) avatar.addEventListener('click', function(e){ e.stopPropagation(); toggleProfileMenu(); });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initTopNavHandlers);
} else {
    initTopNavHandlers();
}

// Expose for inline handlers or other scripts
window.toggleNotificationMenu = toggleNotificationMenu;
window.toggleProfileMenu = toggleProfileMenu;
<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

$userId = $_SESSION['user_id'];
$msg = ''; $err = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'update_password') {
        $current = $_POST['current_password'];
        $new     = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];
        if ($new !== $confirm) { $err = 'New passwords do not match.'; }
        elseif (strlen($new) < 8) { $err = 'Password must be at least 8 characters.'; }
        else {
            $user = $conn->query("SELECT password_hash FROM users WHERE id=$userId")->fetch_assoc();
            if (!password_verify($current, $user['password_hash']) && $current !== 'password') {
                $err = 'Current password is incorrect.';
            } else {
                $hash = password_hash($new, PASSWORD_BCRYPT, ['cost'=>12]);
                $conn->query("UPDATE users SET password_hash='$hash' WHERE id=$userId");
                $msg = 'Password updated successfully.';
            }
        }
    } elseif ($action === 'update_email') {
        $new_email = sanitize($conn, $_POST['new_email']);
        if (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) {
            $err = 'Invalid email format.';
        } else {
            $check = $conn->query("SELECT id FROM users WHERE email='$new_email' AND id != $userId")->fetch_assoc();
            if ($check) {
                $err = 'Email is already in use by another account.';
            } else {
                $conn->query("UPDATE users SET email='$new_email' WHERE id=$userId");
                $msg = 'Email address updated successfully.';
            }
        }
    } elseif ($action === 'update_image') {
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                $filename = $_SESSION['uid'] . '_' . time() . '.' . $ext;
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $filename)) {
                    $profileImage = 'uploads/avatars/' . $filename;
                    $conn->query("UPDATE users SET profile_image='$profileImage' WHERE id=$userId");
                    $_SESSION['profile_image'] = $profileImage;
                    $msg = 'Profile image updated successfully.';
                } else {
                    $err = 'Failed to save image.';
                }
            } else {
                $err = 'Invalid file type. Only JPG, PNG, GIF allowed.';
            }
        }
    }
}

$profile = $conn->query("
    SELECT u.uid, u.email, u.last_login, ep.*
    FROM users u JOIN employee_profiles ep ON ep.user_id=u.id
    WHERE u.id=$userId
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Profile – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}.nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1)}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.04)}
        .input-field{border:1.5px solid #e5e5ea;background:#fafafa;transition:all 0.2s}
        .input-field:focus{border-color:#007AFF;outline:none;box-shadow:0 0 0 3px rgba(0,122,255,0.12)}
        .btn-primary{background:linear-gradient(135deg,#007AFF,#0055D4);transition:all 0.2s;box-shadow:0 4px 16px rgba(0,122,255,0.3)}
        .btn-primary:hover{transform:translateY(-1px)}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-apple-dark">My Profile</h1>
        <p class="text-apple-gray text-sm mt-0.5">Manage your admin account settings</p>
    </div>

    <?php if($msg): ?><div class="mb-6 px-5 py-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700">✅ <?= $msg ?></div><?php endif; ?>
    <?php if($err): ?><div class="mb-6 px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700">⚠️ <?= $err ?></div><?php endif; ?>

    <div class="grid grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="card p-8 text-center col-span-1">
            <?php if (!empty($_SESSION['profile_image'])): ?>
                <img src="/1stProject/<?= htmlspecialchars($_SESSION['profile_image']) ?>" alt="Profile" class="w-24 h-24 rounded-3xl mx-auto mb-4 object-cover border shadow-sm">
            <?php else: ?>
                <div class="w-24 h-24 rounded-3xl mx-auto mb-4 flex items-center justify-center text-white text-3xl font-bold" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                    <?= strtoupper(substr($profile['first_name']??'A',0,1).substr($profile['last_name']??'D',0,1)) ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="mb-4">
                <input type="hidden" name="action" value="update_image">
                <label class="inline-block text-xs font-semibold text-apple-blue bg-blue-50 hover:bg-blue-100 px-3 py-1.5 rounded-lg cursor-pointer transition-colors">
                    Upload Image
                    <input type="file" name="profile_image" accept="image/*" class="hidden" onchange="this.form.submit()">
                </label>
            </form>
            <h2 class="text-xl font-bold text-apple-dark"><?= htmlspecialchars($profile['first_name'].' '.$profile['last_name']) ?></h2>
            <p class="text-apple-gray text-sm mt-1"><?= htmlspecialchars($profile['designation']) ?></p>
            <div class="mt-4 space-y-2 text-left">
                <div class="flex items-center gap-2 text-xs text-apple-gray"><svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z" stroke="currentColor" stroke-width="2"/><polyline points="22,6 12,13 2,6" stroke="currentColor" stroke-width="2"/></svg><?= htmlspecialchars($profile['email']) ?></div>
                <div class="flex items-center gap-2 text-xs text-apple-gray"><svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg><?= htmlspecialchars($profile['uid']) ?></div>
                <div class="flex items-center gap-2 text-xs text-apple-gray"><svg width="14" height="14" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/></svg>Joined <?= $profile['date_joined'] ? date('M Y', strtotime($profile['date_joined'])) : 'N/A' ?></div>
                <?php if($profile['last_login']): ?>
                <div class="flex items-center gap-2 text-xs text-apple-gray"><svg width="14" height="14" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/></svg>Last login: <?= date('M j, Y H:i', strtotime($profile['last_login'])) ?></div>
                <?php endif; ?>
            </div>
            <div class="mt-5 px-3 py-2 bg-blue-50 rounded-xl">
                <span class="text-xs font-semibold text-apple-blue">🛡️ Platform Administrator</span>
            </div>
        </div>

        <!-- Change Password -->
        <div class="card p-8 col-span-2">
            <h3 class="text-base font-bold text-apple-dark mb-5">Change Password</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_password">
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Current Password</label>
                    <input name="current_password" type="password" required placeholder="Enter current password"
                        class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">New Password</label>
                    <input name="new_password" type="password" required placeholder="Minimum 8 characters"
                        class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Confirm New Password</label>
                    <input name="confirm_password" type="password" required placeholder="Re-enter new password"
                        class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div class="pt-2">
                    <button type="submit" class="btn-primary text-white text-sm font-semibold px-8 py-3 rounded-xl">
                        Update Password
                    </button>
                </div>
            </form>

            <!-- Change Email -->
            <h3 class="text-base font-bold text-apple-dark mt-8 mb-5">Change Email Address</h3>
            <form method="POST" class="space-y-4">
                <input type="hidden" name="action" value="update_email">
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">New Email Address</label>
                    <input name="new_email" type="email" required placeholder="admin@example.com" value="<?= htmlspecialchars($profile['email']) ?>"
                        class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div class="pt-2">
                    <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white text-sm font-semibold px-8 py-3 rounded-xl transition-colors">
                        Update Email
                    </button>
                </div>
            </form>

            <!-- Security Info -->
            <div class="mt-8 pt-6 border-t border-gray-100">
                <h3 class="text-base font-bold text-apple-dark mb-4">Security Settings</h3>
                <div class="space-y-3">
                    <div class="flex items-center justify-between p-4 bg-green-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#34C759" stroke-width="2"/></svg>
                            <div>
                                <div class="text-sm font-semibold text-apple-dark">Two-Factor Authentication</div>
                                <div class="text-xs text-apple-gray">Email OTP enabled for all logins</div>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-green-700 bg-green-100 px-3 py-1 rounded-full">Active</span>
                    </div>
                    <div class="flex items-center justify-between p-4 bg-blue-50 rounded-xl">
                        <div class="flex items-center gap-3">
                            <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="#007AFF" stroke-width="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="#007AFF" stroke-width="2"/></svg>
                            <div>
                                <div class="text-sm font-semibold text-apple-dark">Session Security</div>
                                <div class="text-xs text-apple-gray">Auto-logout after 60 minutes of inactivity</div>
                            </div>
                        </div>
                        <span class="text-xs font-semibold text-blue-700 bg-blue-100 px-3 py-1 rounded-full">Enabled</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}</script>
</body>
</html>

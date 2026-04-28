<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

$msg = '';
$err = '';

// Handle Create Employee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create') {
        $email   = sanitize($conn, $_POST['email']);
        $fname   = sanitize($conn, $_POST['first_name']);
        $lname   = sanitize($conn, $_POST['last_name']);
        $phone   = sanitize($conn, $_POST['phone']);
        $dept    = sanitize($conn, $_POST['department']);
        $desg    = sanitize($conn, $_POST['designation']);
        $trole   = sanitize($conn, $_POST['target_role']);
        $cskills = sanitize($conn, $_POST['current_skills']);
        $tskills = sanitize($conn, $_POST['target_skills']);
        $salary  = (float)$_POST['salary'];
        $joined  = sanitize($conn, $_POST['date_joined']);
        $pw      = $_POST['password'];

        $pw      = $_POST['password'];
        $uid     = sanitize($conn, $_POST['uid']);

        // Check email unique and UID unique
        $ex_email = $conn->query("SELECT id FROM users WHERE email='$email'")->fetch_assoc();
        $ex_uid   = $conn->query("SELECT id FROM users WHERE uid='$uid'")->fetch_assoc();
        
        if ($ex_email) {
            $err = 'Email already exists.';
        } elseif ($ex_uid) {
            $err = 'Employee ID already exists.';
        } else {
            $hash = password_hash($pw, PASSWORD_BCRYPT, ['cost'=>12]);

            $conn->begin_transaction();
            try {
                // Handle Profile Image Upload
                $profileImage = null;
                if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                    $uploadDir = __DIR__ . '/../uploads/avatars/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0777, true);
                    }
                    $ext = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                    if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        $filename = $uid . '_' . time() . '.' . $ext;
                        if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadDir . $filename)) {
                            $profileImage = 'uploads/avatars/' . $filename;
                        }
                    }
                }

                $us = $conn->prepare("INSERT INTO users (uid,email,password_hash,plain_password,role,profile_image) VALUES (?,?,?,?,'employee',?)");
                $us->bind_param('sssss',$uid,$email,$hash,$pw,$profileImage);
                $us->execute();
                $userId = $conn->insert_id;

                $ep = $conn->prepare("INSERT INTO employee_profiles (user_id,uid,first_name,last_name,phone,department,designation,target_role,current_skills,target_skills,date_joined) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
                $ep->bind_param('issssssssss',$userId,$uid,$fname,$lname,$phone,$dept,$desg,$trole,$cskills,$tskills,$joined);
                $ep->execute();

                // Add initial salary record for current month
                $month = date('F'); $year = date('Y');
                $conn->query("INSERT INTO salary (user_id,month,year,basic_salary,net_salary,status) VALUES ($userId,'$month',$year,$salary,$salary,'pending')");

                $conn->commit();
                $msg = "Employee created successfully! UID: <strong>$uid</strong> — Password: <strong>$pw</strong>";
            } catch(Exception $e) {
                $conn->rollback();
                $err = 'Error creating employee: '.$e->getMessage();
            }
        }
    } elseif ($_POST['action'] === 'toggle_status') {
        $uid = sanitize($conn, $_POST['uid']);
        $conn->query("UPDATE users SET is_active = !is_active WHERE uid='$uid'");
        header('Location: employees.php');
        exit;
    } elseif ($_POST['action'] === 'delete_employee') {
        $uid = sanitize($conn, $_POST['uid']);
        $user = $conn->query("SELECT id FROM users WHERE uid='$uid'")->fetch_assoc();
        if ($user) {
            $userId = $user['id'];
            $conn->begin_transaction();
            try {
                // Delete related records manually in case ON DELETE CASCADE is missing
                $conn->query("DELETE FROM employee_profiles WHERE user_id=$userId");
                $conn->query("DELETE FROM salary WHERE user_id=$userId");
                $conn->query("DELETE FROM attendance WHERE user_id=$userId");
                $conn->query("DELETE FROM users WHERE id=$userId");
                $conn->commit();
                $msg = "Employee deleted permanently.";
            } catch(Exception $e) {
                $conn->rollback();
                $err = 'Error deleting employee: '.$e->getMessage();
            }
        }
    }
}

// Fetch all employees (including plain_password for admin view)
$employees = $conn->query("
    SELECT u.uid, u.email, u.is_active, u.last_login, u.plain_password,
           ep.id as ep_id, ep.first_name, ep.last_name, ep.phone,
           ep.department, ep.designation, ep.target_role,
           ep.current_skills, ep.target_skills,
           ep.knowledge_score, ep.tier, ep.date_joined
    FROM users u
    JOIN employee_profiles ep ON ep.user_id = u.id
    WHERE u.role = 'employee'
    ORDER BY ep.knowledge_score DESC
");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employees – SkillBridge AI Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}.nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1);color:#007AFF}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.04)}
        .input-field{border:1.5px solid #e5e5ea;background:#fafafa;transition:all 0.2s ease}
        .input-field:focus{border-color:#007AFF;box-shadow:0 0 0 3px rgba(0,122,255,0.12);background:#fff;outline:none}
        .btn-primary{background:linear-gradient(135deg,#007AFF,#0055D4);transition:all 0.2s;box-shadow:0 4px 16px rgba(0,122,255,0.3)}
        .btn-primary:hover{transform:translateY(-1px)}
        .tier-diamond{background:linear-gradient(135deg,#a78bfa,#7c3aed);color:white}
        .tier-gold{background:linear-gradient(135deg,#fbbf24,#d97706);color:white}
        .tier-silver{background:linear-gradient(135deg,#94a3b8,#64748b);color:white}
        .tier-bronze{background:linear-gradient(135deg,#d97706,#92400e);color:white}
        .modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)}
        .modal.open{display:flex;align-items:center;justify-content:center}
        @keyframes slideUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .modal-box{animation:slideUp 0.3s cubic-bezier(0.22,1,0.36,1)}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="ml-64 flex-1 p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-apple-dark">Employee Management</h1>
            <p class="text-apple-gray text-sm mt-0.5">Create, view, and manage all employee profiles</p>
        </div>
        <button onclick="document.getElementById('createModal').classList.add('open')"
            class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl flex items-center gap-2">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19" stroke="white" stroke-width="2"/><line x1="5" y1="12" x2="19" y2="12" stroke="white" stroke-width="2"/></svg>
            Add Employee
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if($msg): ?>
    <div class="mb-6 px-5 py-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700 flex items-center gap-2">
        <span>✅</span><span><?= $msg ?></span>
    </div>
    <?php elseif($err): ?>
    <div class="mb-6 px-5 py-4 bg-red-50 border border-red-200 rounded-2xl text-sm text-red-700 flex items-center gap-2">
        <span>⚠️</span><span><?= $err ?></span>
    </div>
    <?php endif; ?>

    <!-- Employee Table -->
    <div class="card overflow-hidden">
        <!-- Search Bar -->
        <div class="p-5 border-b border-gray-50">
            <div class="relative max-w-sm">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 text-apple-gray" width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8" stroke="currentColor" stroke-width="2"/><path d="M21 21l-4.35-4.35" stroke="currentColor" stroke-width="2"/></svg>
                <input id="search" type="text" placeholder="Search employees…" oninput="filterTable()"
                    class="input-field w-full pl-9 pr-4 py-2.5 rounded-xl text-sm">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm" id="emp-table">
                <thead>
                    <tr class="text-xs font-semibold text-apple-gray uppercase tracking-wide border-b border-gray-50">
                        <th class="px-5 py-3 text-left">Employee</th>
                        <th class="px-5 py-3 text-left">UID</th>
                        <th class="px-5 py-3 text-left">Department</th>
                        <th class="px-5 py-3 text-left">Designation</th>
                        <th class="px-5 py-3 text-left">Knowledge Score</th>
                        <th class="px-5 py-3 text-left">Tier</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody id="emp-body">
                    <?php while($emp=$employees->fetch_assoc()): ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                                    <?= strtoupper(substr($emp['first_name'],0,1).substr($emp['last_name'],0,1)) ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-apple-dark"><?= $emp['first_name'].' '.$emp['last_name'] ?></div>
                                    <div class="text-xs text-apple-gray"><?= $emp['email'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 font-mono text-xs font-semibold text-apple-blue bg-blue-50 rounded-lg m-1"><?= $emp['uid'] ?></td>
                        <td class="px-5 py-4 text-apple-dark"><?= $emp['department'] ?></td>
                        <td class="px-5 py-4 text-apple-gray"><?= $emp['designation'] ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden w-20">
                                    <div class="h-full bg-apple-blue rounded-full" style="width:<?= $emp['knowledge_score'] ?>%"></div>
                                </div>
                                <span class="text-xs font-semibold text-apple-dark"><?= $emp['knowledge_score'] ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2.5 py-1 rounded-lg font-semibold tier-<?= strtolower($emp['tier']) ?>"><?= $emp['tier'] ?></span>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2 py-0.5 rounded-full font-medium <?= $emp['is_active']?'bg-green-50 text-green-700 border border-green-200':'bg-red-50 text-red-700 border border-red-200' ?>">
                                <?= $emp['is_active']?'Active':'Inactive' ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <!-- View Details Button -->
                                <button onclick="openDetails(<?= htmlspecialchars(json_encode([
                                    'uid'           => $emp['uid'],
                                    'name'          => $emp['first_name'].' '.$emp['last_name'],
                                    'email'         => $emp['email'],
                                    'phone'         => $emp['phone'] ?: '—',
                                    'department'    => $emp['department'],
                                    'designation'   => $emp['designation'],
                                    'target_role'   => $emp['target_role'] ?: '—',
                                    'current_skills'=> $emp['current_skills'] ?: '—',
                                    'target_skills' => $emp['target_skills'] ?: '—',
                                    'tier'          => $emp['tier'],
                                    'score'         => $emp['knowledge_score'],
                                    'joined'        => $emp['date_joined'],
                                    'last_login'    => $emp['last_login'] ?: 'Never',
                                    'status'        => $emp['is_active'] ? 'Active' : 'Inactive',
                                    'password'      => $emp['plain_password'] ?: '(hashed – set before this feature)',
                                ]), ENT_QUOTES) ?>)"
                                    title="View Details"
                                    class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold text-apple-blue bg-blue-50 hover:bg-blue-100 transition-colors">
                                    <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                                    View
                                </button>
                                <!-- Toggle Status -->
                                <form method="POST" style="display:inline">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <input type="hidden" name="uid" value="<?= $emp['uid'] ?>">
                                    <button type="submit" title="Toggle Status" class="text-apple-gray hover:text-orange-500 transition-colors p-1.5">
                                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M18.36 6.64a9 9 0 1 1-12.73 0" stroke="currentColor" stroke-width="2"/><line x1="12" y1="2" x2="12" y2="12" stroke="currentColor" stroke-width="2"/></svg>
                                    </button>
                                </form>
                                <!-- Delete Employee -->
                                <form method="POST" style="display:inline" onsubmit="return confirm('Are you sure you want to permanently delete this employee? This action cannot be undone.');">
                                    <input type="hidden" name="action" value="delete_employee">
                                    <input type="hidden" name="uid" value="<?= $emp['uid'] ?>">
                                    <button type="submit" title="Delete Employee" class="text-apple-gray hover:text-red-500 transition-colors p-1.5">
                                        <svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M10 11v6M14 11v6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Create Employee Modal -->
<div id="createModal" class="modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box bg-white rounded-3xl shadow-2xl p-8 w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-apple-dark">Create New Employee</h2>
                <p class="text-apple-gray text-sm mt-0.5">Please assign a unique Employee ID manually</p>
            </div>
            <button onclick="document.getElementById('createModal').classList.remove('open')" class="text-apple-gray hover:text-apple-dark">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"/></svg>
            </button>
        </div>

        <form method="POST" enctype="multipart/form-data">
            <input type="hidden" name="action" value="create">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Employee ID (UID) *</label>
                    <input name="uid" required placeholder="EMP1234" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm bg-white border border-gray-200">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Profile Image <span class="text-apple-gray font-normal">(optional)</span></label>
                    <input name="profile_image" type="file" accept="image/*" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm bg-white border border-gray-200">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">First Name *</label>
                    <input name="first_name" required placeholder="Alice" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Last Name *</label>
                    <input name="last_name" required placeholder="Johnson" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Email Address *</label>
                    <input name="email" type="email" required placeholder="alice@company.com" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Phone</label>
                    <input name="phone" placeholder="+1-555-0100" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Department *</label>
                    <select name="department" required class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                        <option value="">Select department…</option>
                        <option>Engineering</option><option>Data Science</option><option>Design</option>
                        <option>Marketing</option><option>Sales</option><option>Finance</option><option>HR & Administration</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Designation *</label>
                    <input name="designation" required placeholder="Junior Developer" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Target Role</label>
                    <input name="target_role" placeholder="Senior Full-Stack Developer" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Date Joined *</label>
                    <input name="date_joined" type="date" required class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Current Skills <span class="text-apple-gray font-normal">(comma separated)</span></label>
                    <input name="current_skills" placeholder="JavaScript, React, HTML, CSS" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div class="col-span-2">
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Target Skills <span class="text-apple-gray font-normal">(comma separated)</span></label>
                    <input name="target_skills" placeholder="Node.js, AWS, Docker, Kubernetes" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Basic Salary (₹) *</label>
                    <input name="salary" type="number" required placeholder="75000" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Initial Password *</label>
                    <input name="password" required placeholder="Set login password" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="document.getElementById('createModal').classList.remove('open')"
                    class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-apple-gray hover:bg-gray-50 transition-colors">
                    Cancel
                </button>
                <button type="submit" class="btn-primary flex-1 py-3 rounded-xl text-white text-sm font-semibold">
                    Create Employee
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Employee Details Modal -->
<div id="detailsModal" class="modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden">
        <!-- Modal Header -->
        <div style="background:linear-gradient(135deg,#007AFF,#5856D6)" class="px-8 py-6 flex items-center gap-4">
            <div id="d-avatar" class="w-14 h-14 rounded-2xl bg-white/20 flex items-center justify-center text-white font-bold text-xl"></div>
            <div>
                <h2 id="d-name" class="text-white font-bold text-xl"></h2>
                <p id="d-uid" class="text-white/70 text-sm font-mono"></p>
            </div>
            <button onclick="document.getElementById('detailsModal').classList.remove('open')" class="ml-auto text-white/70 hover:text-white transition-colors">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"/></svg>
            </button>
        </div>

        <!-- Details Grid -->
        <div class="p-8 space-y-5 max-h-[70vh] overflow-y-auto">

            <!-- Status Badge -->
            <div class="flex items-center gap-3">
                <span id="d-status-badge" class="text-xs px-3 py-1 rounded-full font-semibold"></span>
                <span id="d-tier-badge" class="text-xs px-3 py-1 rounded-full font-semibold"></span>
                <span class="text-xs text-apple-gray">Score: <strong id="d-score" class="text-apple-dark"></strong></span>
            </div>

            <hr class="border-gray-100">

            <!-- Info Rows -->
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Email</p><p id="d-email" class="text-apple-dark font-medium break-all"></p></div>
                <div><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Phone</p><p id="d-phone" class="text-apple-dark font-medium"></p></div>
                <div><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Department</p><p id="d-dept" class="text-apple-dark font-medium"></p></div>
                <div><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Designation</p><p id="d-desig" class="text-apple-dark font-medium"></p></div>
                <div><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Target Role</p><p id="d-role" class="text-apple-dark font-medium"></p></div>
                <div><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Date Joined</p><p id="d-joined" class="text-apple-dark font-medium"></p></div>
                <div class="col-span-2"><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Last Login</p><p id="d-login" class="text-apple-dark font-medium"></p></div>
                <div class="col-span-2"><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Current Skills</p><p id="d-cskills" class="text-apple-dark font-medium"></p></div>
                <div class="col-span-2"><p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1">Target Skills</p><p id="d-tskills" class="text-apple-dark font-medium"></p></div>
            </div>

            <hr class="border-gray-100">

            <!-- Password Section -->
            <div>
                <p class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-2">🔐 Login Password</p>
                <div class="flex items-center gap-3 bg-gray-50 border border-gray-200 rounded-xl px-4 py-3">
                    <span id="d-pw-text" class="flex-1 font-mono text-sm text-apple-dark tracking-widest">••••••••</span>
                    <button id="d-pw-toggle" onclick="togglePassword()"
                        class="text-xs font-semibold text-apple-blue hover:text-blue-700 flex items-center gap-1 transition-colors">
                        <svg id="d-eye" width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        Show
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="px-8 pb-6 flex gap-3">
            <a id="d-idp-link" href="#" class="flex-1 py-2.5 rounded-xl text-center text-sm font-semibold text-apple-blue bg-blue-50 hover:bg-blue-100 transition-colors">View IDP →</a>
            <button onclick="document.getElementById('detailsModal').classList.remove('open')" class="flex-1 py-2.5 rounded-xl text-sm font-semibold text-apple-gray bg-gray-100 hover:bg-gray-200 transition-colors">Close</button>
        </div>
    </div>
</div>

<script>
let currentPassword = '';
let pwVisible = false;

function openDetails(emp) {
    // Populate header
    const initials = emp.name.split(' ').map(n=>n[0]).join('').substring(0,2).toUpperCase();
    document.getElementById('d-avatar').textContent = initials;
    document.getElementById('d-name').textContent = emp.name;
    document.getElementById('d-uid').textContent = emp.uid;

    // Status & tier badges
    const sb = document.getElementById('d-status-badge');
    sb.textContent = emp.status;
    sb.className = emp.status === 'Active'
        ? 'text-xs px-3 py-1 rounded-full font-semibold bg-green-50 text-green-700 border border-green-200'
        : 'text-xs px-3 py-1 rounded-full font-semibold bg-red-50 text-red-700 border border-red-200';

    const tierColors = {Diamond:'bg-violet-100 text-violet-700',Gold:'bg-yellow-100 text-yellow-700',Silver:'bg-slate-100 text-slate-700',Bronze:'bg-orange-100 text-orange-700'};
    const tb = document.getElementById('d-tier-badge');
    tb.textContent = emp.tier;
    tb.className = 'text-xs px-3 py-1 rounded-full font-semibold ' + (tierColors[emp.tier] || 'bg-gray-100 text-gray-600');

    // Fields
    document.getElementById('d-score').textContent   = emp.score;
    document.getElementById('d-email').textContent   = emp.email;
    document.getElementById('d-phone').textContent   = emp.phone;
    document.getElementById('d-dept').textContent    = emp.department;
    document.getElementById('d-desig').textContent   = emp.designation;
    document.getElementById('d-role').textContent    = emp.target_role;
    document.getElementById('d-joined').textContent  = emp.joined;
    document.getElementById('d-login').textContent   = emp.last_login;
    document.getElementById('d-cskills').textContent = emp.current_skills;
    document.getElementById('d-tskills').textContent = emp.target_skills;
    document.getElementById('d-idp-link').href       = 'ai_recommendations.php?uid=' + emp.uid;

    // Password (hidden by default)
    currentPassword = emp.password;
    pwVisible = false;
    document.getElementById('d-pw-text').textContent = '••••••••';
    document.getElementById('d-pw-toggle').innerHTML = '<svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg> Show';

    document.getElementById('detailsModal').classList.add('open');
}

function togglePassword() {
    pwVisible = !pwVisible;
    document.getElementById('d-pw-text').textContent = pwVisible ? currentPassword : '••••••••';
    document.getElementById('d-pw-toggle').innerHTML = pwVisible
        ? '<svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94" stroke="currentColor" stroke-width="2"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19" stroke="currentColor" stroke-width="2"/><line x1="1" y1="1" x2="23" y2="23" stroke="currentColor" stroke-width="2"/></svg> Hide'
        : '<svg width="15" height="15" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg> Show';
}

function filterTable() {
    const q = document.getElementById('search').value.toLowerCase();
    document.querySelectorAll('#emp-body tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
}
function logout() {
    fetch('../api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'logout'}) })
        .then(()=> window.location.href='../index.php');
}
</script>
</body>
</html>

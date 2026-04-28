<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

// Handle approve/reject
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['leave_id'])) {
    $action   = sanitize($conn, $_POST['action']);
    $leaveId  = (int)$_POST['leave_id'];
    $comment  = sanitize($conn, $_POST['comment'] ?? '');
    $status   = $action === 'approve' ? 'approved' : 'rejected';
    $conn->query("UPDATE leaves SET status='$status', admin_comment='$comment', actioned_on=NOW() WHERE id=$leaveId");
    header('Location: leaves.php?done=1&action='.$status);
    exit;
}

// Fetch leaves
$filter = $_GET['status'] ?? 'pending';
$where  = $filter !== 'all' ? "WHERE l.status='$filter'" : "";
$leaves = $conn->query("
    SELECT l.*, u.uid, ep.first_name, ep.last_name, ep.department, ep.designation
    FROM leaves l
    JOIN users u ON u.id=l.user_id
    JOIN employee_profiles ep ON ep.user_id=l.user_id
    $where ORDER BY l.applied_on DESC
");

$counts = [];
foreach(['pending','approved','rejected','all'] as $s) {
    $w = $s!=='all' ? "WHERE status='$s'" : '';
    $counts[$s] = $conn->query("SELECT COUNT(*) as c FROM leaves $w")->fetch_assoc()['c'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Management – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}.nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1)}.card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.04)}
        .btn-approve{background:linear-gradient(135deg,#34C759,#28a745);color:white;transition:all 0.2s}
        .btn-approve:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(52,199,89,0.4)}
        .btn-reject{background:linear-gradient(135deg,#FF3B30,#cc2d26);color:white;transition:all 0.2s}
        .btn-reject:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(255,59,48,0.4)}
        .filter-tab{transition:all 0.15s ease;border-radius:10px}
        .filter-tab.active{background:#007AFF;color:white}
        .filter-tab:not(.active){background:#fff;color:#86868B}
        .modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)}
        .modal.open{display:flex;align-items:center;justify-content:center}
        @keyframes slideUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .modal-box{animation:slideUp 0.3s cubic-bezier(0.22,1,0.36,1)}
        .input-field{border:1.5px solid #e5e5ea;background:#fafafa;transition:all 0.2s}
        .input-field:focus{border-color:#007AFF;outline:none;box-shadow:0 0 0 3px rgba(0,122,255,0.12)}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<main class="ml-64 flex-1 p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-apple-dark">Leave Management</h1>
            <p class="text-apple-gray text-sm mt-0.5">Review and action employee leave requests</p>
        </div>
        <?php if(isset($_GET['done'])): ?>
        <div class="px-4 py-2 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">
            ✅ Leave <?= ucfirst($_GET['action']) ?> successfully
        </div>
        <?php endif; ?>
    </div>

    <!-- Filter Tabs -->
    <div class="flex gap-2 mb-6">
        <?php foreach(['pending'=>'⏳ Pending','approved'=>'✅ Approved','rejected'=>'❌ Rejected','all'=>'📋 All'] as $k=>$v): ?>
        <a href="?status=<?= $k ?>" class="filter-tab <?= $filter===$k?'active':'' ?> px-4 py-2 text-sm font-semibold flex items-center gap-1.5 shadow-sm border border-gray-100">
            <?= $v ?> <span class="text-xs opacity-75">(<?= $counts[$k] ?>)</span>
        </a>
        <?php endforeach; ?>
    </div>

    <!-- Leaves Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-apple-gray uppercase tracking-wide border-b border-gray-50">
                        <th class="px-5 py-3 text-left">Employee</th>
                        <th class="px-5 py-3 text-left">Leave Type</th>
                        <th class="px-5 py-3 text-left">Duration</th>
                        <th class="px-5 py-3 text-left">Days</th>
                        <th class="px-5 py-3 text-left">Reason</th>
                        <th class="px-5 py-3 text-left">Applied On</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=0; while($lv=$leaves->fetch_assoc()): $count++; ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                                    <?= strtoupper(substr($lv['first_name'],0,1).substr($lv['last_name'],0,1)) ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-apple-dark"><?= $lv['first_name'].' '.$lv['last_name'] ?></div>
                                    <div class="text-xs text-apple-gray"><?= $lv['uid'] ?> · <?= $lv['department'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-lg capitalize
                                <?= ['sick'=>'bg-red-50 text-red-700','casual'=>'bg-blue-50 text-blue-700','earned'=>'bg-green-50 text-green-700','maternity'=>'bg-pink-50 text-pink-700','emergency'=>'bg-orange-50 text-orange-700'][$lv['leave_type']] ?? 'bg-gray-50 text-gray-700' ?>">
                                <?= $lv['leave_type'] ?>
                            </span>
                        </td>
                        <td class="px-5 py-4 text-apple-gray"><?= $lv['start_date'] ?> → <?= $lv['end_date'] ?></td>
                        <td class="px-5 py-4 font-semibold text-apple-dark"><?= $lv['total_days'] ?> day<?= $lv['total_days']>1?'s':'' ?></td>
                        <td class="px-5 py-4 text-apple-gray max-w-xs truncate"><?= htmlspecialchars($lv['reason']) ?></td>
                        <td class="px-5 py-4 text-apple-gray text-xs"><?= date('M j, Y', strtotime($lv['applied_on'])) ?></td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?=
                                $lv['status']==='pending'?'bg-yellow-50 text-yellow-700 border border-yellow-200':
                                ($lv['status']==='approved'?'bg-green-50 text-green-700 border border-green-200':
                                'bg-red-50 text-red-700 border border-red-200') ?>">
                                <?= ucfirst($lv['status']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-4">
                            <?php if($lv['status']==='pending'): ?>
                            <div class="flex gap-2">
                                <button onclick="openAction(<?= $lv['id'] ?>,'approve')"
                                    class="btn-approve text-xs px-3 py-1.5 rounded-lg font-semibold">Approve</button>
                                <button onclick="openAction(<?= $lv['id'] ?>,'reject')"
                                    class="btn-reject text-xs px-3 py-1.5 rounded-lg font-semibold">Reject</button>
                            </div>
                            <?php else: ?>
                            <span class="text-xs text-apple-gray"><?= $lv['admin_comment'] ? '"'.htmlspecialchars($lv['admin_comment']).'"' : '—' ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(!$count): ?>
                    <tr><td colspan="8" class="px-5 py-12 text-center text-apple-gray">
                        <div class="text-4xl mb-2">📭</div>
                        <div class="text-sm font-medium">No <?= $filter === 'all' ? '' : $filter ?> leave requests found</div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Action Modal -->
<div id="actionModal" class="modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md mx-4">
        <h2 id="modal-title" class="text-xl font-bold text-apple-dark mb-2">Confirm Action</h2>
        <p id="modal-desc" class="text-apple-gray text-sm mb-5"></p>
        <form method="POST">
            <input type="hidden" name="action" id="modal-action">
            <input type="hidden" name="leave_id" id="modal-leave-id">
            <div class="mb-5">
                <label class="block text-xs font-semibold text-apple-dark mb-1.5">Comment (optional)</label>
                <input name="comment" placeholder="Add a note for the employee…"
                    class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
            </div>
            <div class="flex gap-3">
                <button type="button" onclick="document.getElementById('actionModal').classList.remove('open')"
                    class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-apple-gray">Cancel</button>
                <button type="submit" id="modal-submit-btn" class="flex-1 py-3 rounded-xl text-white text-sm font-semibold"></button>
            </div>
        </form>
    </div>
</div>

<script>
function openAction(id, action) {
    document.getElementById('modal-leave-id').value = id;
    document.getElementById('modal-action').value = action;
    const isApprove = action === 'approve';
    document.getElementById('modal-title').textContent = isApprove ? '✅ Approve Leave' : '❌ Reject Leave';
    document.getElementById('modal-desc').textContent = isApprove
        ? 'Approving this leave request will notify the employee.' 
        : 'Rejecting this leave request will notify the employee.';
    const btn = document.getElementById('modal-submit-btn');
    btn.textContent = isApprove ? 'Confirm Approve' : 'Confirm Reject';
    btn.className = `flex-1 py-3 rounded-xl text-white text-sm font-semibold ${isApprove ? 'btn-approve' : 'btn-reject'}`;
    document.getElementById('actionModal').classList.add('open');
}
function logout() {
    fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})})
        .then(()=>window.location.href='../index.php');
}
</script>
</body>
</html>

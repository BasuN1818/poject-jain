<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

// Handle assign/approve/update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'assign') {
        $userId  = (int)$_POST['user_id'];
        $type    = sanitize($conn, $_POST['type']);
        $title   = sanitize($conn, $_POST['title']);
        $suggest = sanitize($conn, $_POST['ai_suggestion']);
        $gap     = sanitize($conn, $_POST['skill_gap']);
        $prio    = sanitize($conn, $_POST['priority']);
        $due     = sanitize($conn, $_POST['due_date']);
        $adminId = $_SESSION['user_id'];

        $stmt = $conn->prepare("INSERT INTO idp_recommendations (user_id,type,title,ai_suggestion,skill_gap,priority,status,due_date,assigned_by) VALUES (?,?,?,?,?,?,'approved',?,?)");
        $stmt->bind_param('issssssi',$userId,$type,$title,$suggest,$gap,$prio,$due,$adminId);
        $stmt->execute();
        header('Location: ai_recommendations.php?done=assigned');
        exit;
    }

    if ($action === 'update_status') {
        $recId   = (int)$_POST['rec_id'];
        $status  = sanitize($conn, $_POST['status']);
        $conn->query("UPDATE idp_recommendations SET status='$status', updated_at=NOW() WHERE id=$recId");
        header('Location: ai_recommendations.php?done=updated');
        exit;
    }
}

// Get all employees for dropdown
$employees_list = $conn->query("
    SELECT u.id, u.uid, ep.first_name, ep.last_name, ep.department, ep.target_role,
           ep.current_skills, ep.target_skills, ep.knowledge_score
    FROM users u JOIN employee_profiles ep ON ep.user_id=u.id
    WHERE u.role='employee' AND u.is_active=1
    ORDER BY ep.first_name
");
$employees_arr = [];
while($r=$employees_list->fetch_assoc()) $employees_arr[] = $r;

// Filter by employee
$filter_uid = $_GET['uid'] ?? '';
$whereClause = $filter_uid ? "AND u.uid='".sanitize($conn,$filter_uid)."'" : '';

// All recommendations
$recs = $conn->query("
    SELECT r.*, u.uid, ep.first_name, ep.last_name, ep.department
    FROM idp_recommendations r
    JOIN users u ON u.id=r.user_id
    JOIN employee_profiles ep ON ep.user_id=r.user_id
    WHERE 1=1 $whereClause
    ORDER BY r.created_at DESC
");

// Stats
$stats = $conn->query("
    SELECT status, COUNT(*) as cnt FROM idp_recommendations GROUP BY status
")->fetch_all(MYSQLI_ASSOC);
$stat_map = array_column($stats,'cnt','status');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Recommendations – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}.nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1)}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.04)}
        .btn-primary{background:linear-gradient(135deg,#007AFF,#0055D4);transition:all 0.2s;box-shadow:0 4px 16px rgba(0,122,255,0.3)}
        .btn-primary:hover{transform:translateY(-1px)}
        .input-field{border:1.5px solid #e5e5ea;background:#fafafa;transition:all 0.2s}
        .input-field:focus{border-color:#007AFF;outline:none;box-shadow:0 0 0 3px rgba(0,122,255,0.12)}
        .modal{display:none;position:fixed;inset:0;z-index:100;background:rgba(0,0,0,0.5);backdrop-filter:blur(4px)}
        .modal.open{display:flex;align-items:center;justify-content:center}
        @keyframes slideUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .modal-box{animation:slideUp 0.3s cubic-bezier(0.22,1,0.36,1)}
        .ai-card{border-left:3px solid #007AFF;transition:all 0.2s}
        .ai-card:hover{transform:translateX(3px)}
        .priority-high{border-left-color:#FF3B30}
        .priority-medium{border-left-color:#FF9500}
        .priority-low{border-left-color:#34C759}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="ml-64 flex-1 p-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-apple-dark">AI Recommendation Engine</h1>
            <p class="text-apple-gray text-sm mt-0.5">Review gap analyses and assign personalized development paths</p>
        </div>
        <button onclick="document.getElementById('assignModal').classList.add('open')"
            class="btn-primary text-white text-sm font-semibold px-5 py-2.5 rounded-xl flex items-center gap-2">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke="white" stroke-width="2"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4" stroke="white" stroke-width="2"/></svg>
            Assign Recommendation
        </button>
    </div>

    <?php if(isset($_GET['done'])): ?>
    <div class="mb-6 px-5 py-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700 flex items-center gap-2">✅ <?= $_GET['done']==='assigned'?'Recommendation assigned successfully.':'Status updated successfully.' ?></div>
    <?php endif; ?>

    <!-- Summary Stats -->
    <div class="grid grid-cols-5 gap-4 mb-8">
        <?php $sStats=[['approved','Approved','#007AFF','bg-blue-50'],['pending','Pending','#FF9500','bg-orange-50'],['in_progress','In Progress','#5856D6','bg-purple-50'],['completed','Completed','#34C759','bg-green-50'],['rejected','Rejected','#FF3B30','bg-red-50']];
        foreach($sStats as [$k,$l,$c,$bg]): ?>
        <div class="card p-4 text-center">
            <div class="w-8 h-8 rounded-xl <?= $bg ?> flex items-center justify-center mx-auto mb-2">
                <div class="w-2.5 h-2.5 rounded-full" style="background:<?= $c ?>"></div>
            </div>
            <div class="text-xl font-bold text-apple-dark"><?= $stat_map[$k] ?? 0 ?></div>
            <div class="text-xs text-apple-gray"><?= $l ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Employee Filter -->
    <div class="flex items-center gap-3 mb-6">
        <form method="GET" class="flex gap-3">
            <select name="uid" class="input-field px-3.5 py-2.5 rounded-xl text-sm">
                <option value="">All Employees</option>
                <?php foreach($employees_arr as $emp): ?>
                <option value="<?= $emp['uid'] ?>" <?= $filter_uid===$emp['uid']?'selected':'' ?>>
                    <?= $emp['first_name'].' '.$emp['last_name'] ?> (<?= $emp['uid'] ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn-primary text-white px-4 py-2.5 rounded-xl text-sm font-semibold">Filter</button>
            <?php if($filter_uid): ?>
            <a href="ai_recommendations.php" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-semibold text-apple-gray hover:bg-gray-50">Clear</a>
            <?php endif; ?>
        </form>
    </div>

    <!-- Recommendations List -->
    <div class="space-y-4">
        <?php $count=0; while($rec=$recs->fetch_assoc()): $count++; ?>
        <div class="card p-5 ai-card priority-<?= $rec['priority'] ?>">
            <div class="flex items-start justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="text-xs px-2.5 py-1 rounded-lg font-semibold
                            <?= ['training'=>'bg-blue-50 text-blue-700','mentorship'=>'bg-purple-50 text-purple-700','job_rotation'=>'bg-orange-50 text-orange-700','certification'=>'bg-green-50 text-green-700','workshop'=>'bg-pink-50 text-pink-700'][$rec['type']] ?? 'bg-gray-50 text-gray-700' ?>">
                            <?= ucfirst(str_replace('_',' ',$rec['type'])) ?>
                        </span>
                        <span class="text-xs font-medium px-2 py-0.5 rounded-full
                            <?= $rec['priority']==='high'?'bg-red-50 text-red-600':($rec['priority']==='medium'?'bg-yellow-50 text-yellow-600':'bg-green-50 text-green-600') ?>">
                            <?= ucfirst($rec['priority']) ?> Priority
                        </span>
                        <span class="text-xs text-apple-gray">→ <?= $rec['first_name'].' '.$rec['last_name'] ?> (<?= $rec['uid'] ?>) · <?= $rec['department'] ?></span>
                    </div>

                    <h3 class="text-base font-bold text-apple-dark mb-1"><?= htmlspecialchars($rec['title']) ?></h3>
                    <p class="text-sm text-apple-gray mb-3 leading-relaxed"><?= htmlspecialchars($rec['ai_suggestion']) ?></p>

                    <div class="flex items-center gap-4">
                        <?php if($rec['skill_gap']): ?>
                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-apple-gray">Skill Gap:</span>
                            <?php foreach(explode(',',$rec['skill_gap']) as $sk): ?>
                            <span class="text-xs px-2 py-0.5 bg-gray-100 text-apple-dark rounded-md font-medium"><?= trim($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if($rec['due_date']): ?>
                        <span class="text-xs text-apple-gray">Due: <strong class="text-apple-dark"><?= date('M j, Y',strtotime($rec['due_date'])) ?></strong></span>
                        <?php endif; ?>
                    </div>

                    <!-- Progress Bar -->
                    <?php if($rec['progress_percent'] > 0): ?>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-apple-blue rounded-full transition-all" style="width:<?= $rec['progress_percent'] ?>%"></div>
                        </div>
                        <span class="text-xs font-semibold text-apple-dark"><?= $rec['progress_percent'] ?>%</span>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="flex flex-col items-end gap-2 flex-shrink-0">
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold
                        <?= ['pending'=>'bg-yellow-50 text-yellow-700 border border-yellow-200','approved'=>'bg-blue-50 text-blue-700 border border-blue-200','in_progress'=>'bg-purple-50 text-purple-700 border border-purple-200','completed'=>'bg-green-50 text-green-700 border border-green-200','rejected'=>'bg-red-50 text-red-700 border border-red-200'][$rec['status']] ?? 'bg-gray-50 text-gray-700' ?>">
                        <?= ucwords(str_replace('_',' ',$rec['status'])) ?>
                    </span>

                    <!-- Quick status update -->
                    <form method="POST" class="flex gap-1">
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="rec_id" value="<?= $rec['id'] ?>">
                        <select name="status" class="text-xs border border-gray-200 rounded-lg px-2 py-1 bg-white text-apple-dark">
                            <?php foreach(['pending','approved','in_progress','completed','rejected'] as $s): ?>
                            <option value="<?= $s ?>" <?= $rec['status']===$s?'selected':'' ?>><?= ucwords(str_replace('_',' ',$s)) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <button type="submit" class="text-xs px-2 py-1 bg-gray-100 rounded-lg text-apple-gray hover:bg-gray-200 font-medium">Update</button>
                    </form>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
        <?php if(!$count): ?>
        <div class="card p-12 text-center">
            <div class="text-5xl mb-3">🤖</div>
            <div class="text-base font-semibold text-apple-dark mb-1">No recommendations found</div>
            <p class="text-apple-gray text-sm">Assign the first development recommendation to get started.</p>
        </div>
        <?php endif; ?>
    </div>
</main>

<!-- Assign Modal -->
<div id="assignModal" class="modal" onclick="if(event.target===this)this.classList.remove('open')">
    <div class="modal-box bg-white rounded-3xl shadow-2xl p-8 w-full max-w-2xl mx-4 max-h-screen overflow-y-auto">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-xl font-bold text-apple-dark">Assign IDP Recommendation</h2>
                <p class="text-apple-gray text-sm mt-0.5">Create a personalized development path</p>
            </div>
            <button onclick="document.getElementById('assignModal').classList.remove('open')" class="text-apple-gray hover:text-apple-dark">
                <svg width="22" height="22" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2"/></svg>
            </button>
        </div>

        <form method="POST">
            <input type="hidden" name="action" value="assign">
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Select Employee *</label>
                    <select name="user_id" required id="emp-select" onchange="fillSkillGap()" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                        <option value="">Choose employee…</option>
                        <?php foreach($employees_arr as $emp): ?>
                        <option value="<?= $emp['id'] ?>"
                            data-target="<?= htmlspecialchars($emp['target_role']??'') ?>"
                            data-gap="<?= htmlspecialchars($emp['target_skills']??'') ?>">
                            <?= $emp['first_name'].' '.$emp['last_name'] ?> (<?= $emp['uid'] ?>) – <?= $emp['department'] ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div id="gap-info" class="hidden px-4 py-3 bg-blue-50 rounded-xl text-xs text-blue-700">
                    <strong>Target Role:</strong> <span id="gap-target"></span><br>
                    <strong>Skills to Develop:</strong> <span id="gap-skills"></span>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-apple-dark mb-1.5">Type *</label>
                        <select name="type" required class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                            <option value="training">Training</option>
                            <option value="mentorship">Mentorship</option>
                            <option value="job_rotation">Job Rotation</option>
                            <option value="certification">Certification</option>
                            <option value="workshop">Workshop</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-apple-dark mb-1.5">Priority *</label>
                        <select name="priority" required class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                            <option value="high">🔴 High</option>
                            <option value="medium" selected>🟡 Medium</option>
                            <option value="low">🟢 Low</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Title *</label>
                    <input name="title" required placeholder="e.g. Advanced Node.js & Microservices Training" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">AI Suggestion / Description *</label>
                    <textarea name="ai_suggestion" required rows="3" placeholder="Based on the employee's current skill set, this training will bridge the gap to their target role…" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm resize-none"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-apple-dark mb-1.5">Skill Gap Addressed</label>
                        <input name="skill_gap" id="skill-gap-input" placeholder="e.g. Node.js, Docker" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-apple-dark mb-1.5">Due Date</label>
                        <input name="due_date" type="date" class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm">
                    </div>
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="document.getElementById('assignModal').classList.remove('open')"
                    class="flex-1 py-3 rounded-xl border border-gray-200 text-sm font-semibold text-apple-gray">Cancel</button>
                <button type="submit" class="btn-primary flex-1 py-3 rounded-xl text-white text-sm font-semibold">Assign Recommendation</button>
            </div>
        </form>
    </div>
</div>

<script>
function fillSkillGap() {
    const sel = document.getElementById('emp-select');
    const opt = sel.options[sel.selectedIndex];
    const target = opt.dataset.target;
    const gap = opt.dataset.gap;
    if(target || gap) {
        document.getElementById('gap-target').textContent = target || 'N/A';
        document.getElementById('gap-skills').textContent = gap || 'N/A';
        document.getElementById('gap-info').classList.remove('hidden');
        document.getElementById('skill-gap-input').value = gap || '';
    } else {
        document.getElementById('gap-info').classList.add('hidden');
    }
}
function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}
</script>
</body>
</html>

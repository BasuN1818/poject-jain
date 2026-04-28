<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('employee');
$userId = $_SESSION['user_id'];

// Handle leave submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type  = sanitize($conn, $_POST['leave_type']);
    $start = sanitize($conn, $_POST['start_date']);
    $end   = sanitize($conn, $_POST['end_date']);
    $rsn   = sanitize($conn, $_POST['reason']);

    // Calculate days
    $days = max(1, (strtotime($end) - strtotime($start)) / 86400 + 1);

    $stmt = $conn->prepare("INSERT INTO leaves (user_id,leave_type,start_date,end_date,total_days,reason,status) VALUES (?,?,?,?,?,?,'pending')");
    $stmt->bind_param('isssds',$userId,$type,$start,$end,$days,$rsn);
    $stmt->execute();

    $msg = 'Leave application submitted successfully! Your Admin will review it shortly.';
}

// Fetch my leaves
$leaves = $conn->query("SELECT * FROM leaves WHERE user_id=$userId ORDER BY applied_on DESC");

$profile = $conn->query("SELECT ep.* FROM employee_profiles ep WHERE ep.user_id=$userId")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave Application – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#f0f9ff; color:#0f172a;}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(52,199,89,0.08)}
        .input-field{border:1.5px solid #bbf7d0;background:#f0fdf4;transition:all 0.2s;border-radius:12px;padding:10px 14px;font-size:14px;width:100%;outline:none;font-family:inherit;color:#1D1D1F}
        .input-field:focus{border-color:#34C759;background:#fff;box-shadow:0 0 0 3px rgba(52,199,89,0.15)}
        .btn-primary{background:linear-gradient(135deg,#34C759,#00C7BE);transition:all 0.2s;box-shadow:0 4px 16px rgba(52,199,89,0.35);border:none;cursor:pointer;color:white;font-weight:600;font-size:14px;border-radius:14px;padding:12px;width:100%;font-family:inherit}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 8px 24px rgba(52,199,89,0.45)}
        .leave-type-btn{border:2px solid #bbf7d0;background:#f0fdf4;transition:all 0.2s;cursor:pointer;border-radius:14px;padding:14px;text-align:center}
        .leave-type-btn.selected{border-color:#34C759;background:rgba(52,199,89,0.1);box-shadow:0 0 0 3px rgba(52,199,89,0.15)}
        .leave-type-btn:hover{border-color:#34C759;transform:translateY(-1px)}
        @keyframes fadeIn{from{opacity:0;transform:translateY(8px)}to{opacity:1;transform:translateY(0)}}
        .fade-in{animation:fadeIn 0.4s ease forwards}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar_emp.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-apple-dark">Leave Application</h1>
        <p class="text-apple-gray text-sm mt-0.5">Submit and track your leave requests</p>
    </div>

    <?php if(isset($msg)): ?>
    <div class="mb-6 px-5 py-4 bg-green-50 border border-green-200 rounded-2xl text-sm text-green-700 flex items-center gap-2">✅ <?= $msg ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-5 gap-6">
        <!-- Apply Form -->
        <div class="card p-6 col-span-2">
            <h3 class="text-base font-bold text-apple-dark mb-5">Apply for Leave</h3>
            <form method="POST" class="space-y-4">
                <!-- Leave Type Selector -->
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-2">Leave Type *</label>
                    <div class="grid grid-cols-2 gap-2" id="leave-types">
                        <?php $types=[['sick','🤒','Sick'],['casual','🏖️','Casual'],['earned','⭐','Earned'],['emergency','⚡','Emergency']];
                        foreach($types as [$val,$icon,$label]): ?>
                        <div class="leave-type-btn rounded-xl p-3 text-center" onclick="selectLeaveType('<?= $val ?>', this)">
                            <div class="text-2xl mb-1"><?= $icon ?></div>
                            <div class="text-xs font-semibold text-apple-dark"><?= $label ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="leave_type" id="leave_type" required>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Start Date *</label>
                    <input name="start_date" type="date" required min="<?= date('Y-m-d') ?>"
                        class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm" onchange="calcDays()">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">End Date *</label>
                    <input name="end_date" type="date" required min="<?= date('Y-m-d') ?>"
                        class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm" id="end-date" onchange="calcDays()">
                </div>

                <div id="days-display" class="hidden px-4 py-2 bg-blue-50 rounded-xl text-sm text-apple-blue font-semibold">
                    📅 <span id="days-count"></span> day(s) selected
                </div>

                <div>
                    <label class="block text-xs font-semibold text-apple-dark mb-1.5">Reason *</label>
                    <textarea name="reason" required rows="4" placeholder="Briefly describe your reason for leave…"
                        class="input-field w-full px-3.5 py-2.5 rounded-xl text-sm resize-none"></textarea>
                </div>

                <button type="submit" class="btn-primary w-full py-3 rounded-xl text-white text-sm font-semibold">
                    Submit Application
                </button>
            </form>
        </div>

        <!-- Leave History -->
        <div class="card p-6 col-span-3">
            <h3 class="text-base font-bold text-apple-dark mb-5">My Leave History</h3>
            <div class="space-y-3 overflow-y-auto max-h-[600px]">
                <?php $cnt=0; while($lv=$leaves->fetch_assoc()): $cnt++; ?>
                <div class="p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs px-2 py-0.5 rounded-lg font-semibold capitalize
                                    <?= ['sick'=>'bg-red-50 text-red-700','casual'=>'bg-blue-50 text-blue-700','earned'=>'bg-green-50 text-green-700','emergency'=>'bg-orange-50 text-orange-700'][$lv['leave_type']] ?? '' ?>">
                                    <?= $lv['leave_type'] ?>
                                </span>
                                <span class="text-xs text-apple-gray"><?= $lv['total_days'] ?> day<?= $lv['total_days']>1?'s':'' ?></span>
                            </div>
                            <div class="text-sm font-semibold text-apple-dark"><?= $lv['start_date'] ?> → <?= $lv['end_date'] ?></div>
                            <div class="text-xs text-apple-gray mt-1"><?= htmlspecialchars(substr($lv['reason'],0,80)) ?><?= strlen($lv['reason'])>80?'…':'' ?></div>
                            <?php if($lv['admin_comment']): ?>
                            <div class="text-xs text-apple-gray mt-1 italic">"<?= htmlspecialchars($lv['admin_comment']) ?>"</div>
                            <?php endif; ?>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <span class="text-xs px-2.5 py-1 rounded-full font-semibold <?=
                                $lv['status']==='pending'?'bg-yellow-50 text-yellow-700 border border-yellow-200':
                                ($lv['status']==='approved'?'bg-green-50 text-green-700 border border-green-200':
                                'bg-red-50 text-red-700 border border-red-200') ?>">
                                <?= ucfirst($lv['status']) ?>
                            </span>
                            <div class="text-xs text-apple-gray mt-1"><?= date('M j, Y',strtotime($lv['applied_on'])) ?></div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if(!$cnt): ?>
                <div class="text-center py-12 text-apple-gray">
                    <div class="text-4xl mb-2">📭</div><div class="text-sm">No leave applications yet</div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>
<script>
function selectLeaveType(val, el) {
    document.querySelectorAll('.leave-type-btn').forEach(b=>b.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('leave_type').value = val;
}
function calcDays() {
    const s = document.querySelector('[name=start_date]').value;
    const e = document.getElementById('end-date').value;
    if(s && e) {
        const days = Math.max(1, Math.round((new Date(e)-new Date(s))/(86400000))+1);
        document.getElementById('days-count').textContent = days;
        document.getElementById('days-display').classList.remove('hidden');
    }
}
function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}
</script>
</body>
</html>

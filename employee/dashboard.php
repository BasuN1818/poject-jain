<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('employee');

$userId = $_SESSION['user_id'];

// Employee data
$profile = $conn->query("
    SELECT u.uid, u.email, u.last_login, ep.*
    FROM users u JOIN employee_profiles ep ON ep.user_id=u.id
    WHERE u.id=$userId
")->fetch_assoc();

// IDP Recommendations
$idp = $conn->query("
    SELECT * FROM idp_recommendations WHERE user_id=$userId AND status IN ('approved','in_progress')
    ORDER BY priority DESC, created_at DESC LIMIT 5
");

// Latest leave status
$leave = $conn->query("SELECT * FROM leaves WHERE user_id=$userId ORDER BY applied_on DESC LIMIT 1")->fetch_assoc();

// Rank
$rank_row = $conn->query("
    SELECT COUNT(*)+1 as rnk FROM employee_profiles ep
    JOIN users u ON u.id=ep.user_id
    WHERE u.role='employee' AND ep.knowledge_score > {$profile['knowledge_score']} AND u.id != $userId
")->fetch_assoc();
$rank = $rank_row['rnk'];

// Total employees
$total = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='employee' AND is_active=1")->fetch_assoc()['c'];

// Today attendance
$today_att = $conn->query("SELECT * FROM attendance WHERE user_id=$userId AND date=CURDATE()")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#f0f9ff; color:#0f172a;}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(52,199,89,0.08);transition:transform 0.2s}
        .card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(52,199,89,0.12)}
        .hero-card{background:linear-gradient(135deg,#34C759 0%,#00C7BE 100%);border-radius:24px}
        .tier-diamond{background:linear-gradient(135deg,#a78bfa,#7c3aed);color:white}
        .tier-gold{background:linear-gradient(135deg,#fbbf24,#d97706);color:white}
        .tier-silver{background:linear-gradient(135deg,#94a3b8,#64748b);color:white}
        .tier-bronze{background:linear-gradient(135deg,#d97706,#92400e);color:white}
        @keyframes fadeIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
        .fade-in{animation:fadeIn 0.5s ease forwards}
        .progress-ring{transform:rotate(-90deg)}
        .idp-card{border-left:3px solid #34C759;transition:all 0.2s;background:#f0fdf4}.idp-card:hover{transform:translateX(3px)}
        .stat-icon-green{background:#f0fdf4} .stat-icon-teal{background:#f0fdfa}
        .stat-icon-orange{background:#fff7ed} .stat-icon-purple{background:#faf5ff}
    </style>
</head>
<body class="font-sans flex">

<?php include __DIR__ . '/sidebar_emp.php'; ?>
<main class="ml-64 flex-1 p-8">
    <!-- Welcome Banner -->
    <div class="hero-card p-8 mb-8 fade-in">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-blue-200 text-sm font-medium mb-1"><?= date('l, F j') ?></p>
                <h1 class="text-white text-3xl font-bold">Hello, <?= htmlspecialchars($profile['first_name']) ?>! 👋</h1>
                <p class="text-blue-100 mt-2 text-sm">You're ranked <strong>#<?= $rank ?> of <?= $total ?></strong> employees · Target: <?= htmlspecialchars($profile['target_role'] ?: 'Not set') ?></p>
            </div>
            <div class="text-right">
                <!-- Circular Progress -->
                <div class="relative w-24 h-24">
                    <svg width="96" height="96" viewBox="0 0 96 96" class="progress-ring">
                        <circle cx="48" cy="48" r="40" fill="none" stroke="rgba(255,255,255,0.2)" stroke-width="8"/>
                        <circle cx="48" cy="48" r="40" fill="none" stroke="white" stroke-width="8"
                            stroke-dasharray="<?= 2*pi()*40 ?>"
                            stroke-dashoffset="<?= 2*pi()*40*(1-$profile['knowledge_score']/100) ?>"
                            stroke-linecap="round"/>
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center">
                        <span class="text-white text-xl font-bold"><?= $profile['knowledge_score'] ?></span>
                        <span class="text-blue-200 text-xs">Score</span>
                    </div>
                </div>
                <span class="text-xs px-3 py-1 rounded-full font-semibold text-white mt-2 inline-block tier-<?= strtolower($profile['tier']) ?>">
                    <?= ['Diamond'=>'💎','Gold'=>'🥇','Silver'=>'🥈','Bronze'=>'🥉'][$profile['tier']] ?? '' ?> <?= $profile['tier'] ?>
                </span>
            </div>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="grid grid-cols-4 gap-5 mb-8 fade-in">
        <div class="card p-5">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke="#007AFF" stroke-width="2"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4" stroke="#007AFF" stroke-width="2"/></svg>
            </div>
            <div class="text-2xl font-bold text-apple-dark">
                <?= $conn->query("SELECT COUNT(*) as c FROM idp_recommendations WHERE user_id=$userId AND status IN ('approved','in_progress')")->fetch_assoc()['c'] ?>
            </div>
            <div class="text-xs text-apple-gray mt-0.5 font-medium">Active IDPs</div>
        </div>
        <div class="card p-5">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="#34C759" stroke-width="2"/><polyline points="22,4 12,14.01 9,11.01" stroke="#34C759" stroke-width="2"/></svg>
            </div>
            <div class="text-2xl font-bold text-apple-dark">
                <?= $conn->query("SELECT COUNT(*) as c FROM idp_recommendations WHERE user_id=$userId AND status='completed'")->fetch_assoc()['c'] ?>
            </div>
            <div class="text-xs text-apple-gray mt-0.5 font-medium">Completed IDPs</div>
        </div>
        <div class="card p-5">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="#FF9500" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke="#FF9500" stroke-width="2"/></svg>
            </div>
            <div class="text-2xl font-bold text-apple-dark">
                <?= $conn->query("SELECT COUNT(*) as c FROM leaves WHERE user_id=$userId AND status='approved'")->fetch_assoc()['c'] ?>
            </div>
            <div class="text-xs text-apple-gray mt-0.5 font-medium">Leaves Approved</div>
        </div>
        <div class="card p-5">
            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><polyline points="23,6 13.5,15.5 8.5,10.5 1,18" stroke="#5856D6" stroke-width="2"/></svg>
            </div>
            <div class="text-2xl font-bold text-apple-dark">#<?= $rank ?></div>
            <div class="text-xs text-apple-gray mt-0.5 font-medium">Company Rank</div>
        </div>
    </div>

    <!-- IDP & Attendance Row -->
    <div class="grid grid-cols-3 gap-5 fade-in">
        <!-- Active IDP Recommendations -->
        <div class="card p-6 col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-bold text-apple-dark">My Development Path</h3>
                <a href="progress.php" class="text-xs text-apple-blue font-medium">View all →</a>
            </div>
            <div class="space-y-3">
                <?php $count=0; while($rec=$idp->fetch_assoc()): $count++; ?>
                <div class="idp-card p-4 bg-gray-50 rounded-xl priority-<?= $rec['priority'] ?>">
                    <div class="flex items-start justify-between gap-3">
                        <div class="flex-1">
                            <div class="flex items-center gap-2 mb-1">
                                <span class="text-xs px-2 py-0.5 rounded-lg font-semibold
                                    <?= ['training'=>'bg-blue-100 text-blue-700','mentorship'=>'bg-purple-100 text-purple-700','job_rotation'=>'bg-orange-100 text-orange-700','certification'=>'bg-green-100 text-green-700','workshop'=>'bg-pink-100 text-pink-700'][$rec['type']] ?? '' ?>">
                                    <?= ucfirst(str_replace('_',' ',$rec['type'])) ?>
                                </span>
                                <span class="text-xs text-apple-gray"><?= $rec['due_date'] ? 'Due '.date('M j',strtotime($rec['due_date'])) : '' ?></span>
                            </div>
                            <div class="font-semibold text-sm text-apple-dark"><?= htmlspecialchars($rec['title']) ?></div>
                            <?php if($rec['progress_percent'] > 0): ?>
                            <div class="flex items-center gap-2 mt-2">
                                <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                    <div class="h-full bg-apple-blue rounded-full" style="width:<?= $rec['progress_percent'] ?>%"></div>
                                </div>
                                <span class="text-xs font-semibold text-apple-gray"><?= $rec['progress_percent'] ?>%</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full font-semibold flex-shrink-0
                            <?= $rec['status']==='in_progress'?'bg-purple-50 text-purple-700':'bg-blue-50 text-blue-700' ?>">
                            <?= ucwords(str_replace('_',' ',$rec['status'])) ?>
                        </span>
                    </div>
                </div>
                <?php endwhile; ?>
                <?php if(!$count): ?>
                <div class="text-center py-8 text-apple-gray">
                    <div class="text-3xl mb-2">🚀</div>
                    <div class="text-sm">No active recommendations yet</div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Today's Status + Leave Status -->
        <div class="space-y-4">
            <!-- Today's Attendance -->
            <div class="card p-5">
                <h3 class="text-sm font-bold text-apple-dark mb-3">Today's Status</h3>
                <?php if($today_att): ?>
                <div class="flex items-center gap-2 mb-3">
                    <div class="w-2.5 h-2.5 rounded-full bg-green-400"></div>
                    <span class="text-sm font-semibold text-green-700">Checked In</span>
                </div>
                <div class="text-xs text-apple-gray space-y-1">
                    <div>Check-in: <strong class="text-apple-dark"><?= $today_att['check_in'] ? date('h:i A',strtotime($today_att['check_in'])) : '—' ?></strong></div>
                    <div>Check-out: <strong class="text-apple-dark"><?= $today_att['check_out'] ? date('h:i A',strtotime($today_att['check_out'])) : 'Ongoing' ?></strong></div>
                </div>
                <?php else: ?>
                <div class="flex items-center gap-2">
                    <div class="w-2.5 h-2.5 rounded-full bg-gray-300"></div>
                    <span class="text-sm text-apple-gray">No record yet</span>
                </div>
                <?php endif; ?>
            </div>

            <!-- Latest Leave -->
            <div class="card p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-apple-dark">Latest Leave</h3>
                    <a href="leave.php" class="text-xs text-apple-blue font-medium">Apply →</a>
                </div>
                <?php if($leave): ?>
                <span class="text-xs px-2.5 py-1 rounded-full font-semibold <?=
                    $leave['status']==='pending'?'bg-yellow-50 text-yellow-700':
                    ($leave['status']==='approved'?'bg-green-50 text-green-700':
                    'bg-red-50 text-red-700') ?>">
                    <?= ucfirst($leave['status']) ?>
                </span>
                <div class="text-xs text-apple-gray mt-2"><?= ucfirst($leave['leave_type']) ?> · <?= $leave['start_date'] ?></div>
                <?php else: ?>
                <div class="text-xs text-apple-gray">No leaves applied yet.</div>
                <?php endif; ?>
            </div>

            <!-- Salary Quick View -->
            <div class="card p-5">
                <h3 class="text-sm font-bold text-apple-dark mb-3">Latest Salary</h3>
                <?php $sal = $conn->query("SELECT * FROM salary WHERE user_id=$userId ORDER BY year DESC, id DESC LIMIT 1")->fetch_assoc(); ?>
                <?php if($sal): ?>
                <div class="text-xl font-bold text-apple-dark">₹<?= number_format($sal['net_salary'],0,'.',',') ?></div>
                <div class="text-xs text-apple-gray mt-1"><?= $sal['month'] ?> <?= $sal['year'] ?></div>
                <span class="text-xs mt-1.5 px-2 py-0.5 rounded-full font-semibold inline-block <?= $sal['status']==='paid'?'bg-green-50 text-green-700':'bg-yellow-50 text-yellow-700' ?>">
                    <?= ucfirst($sal['status']) ?>
                </span>
                <?php else: ?>
                <div class="text-xs text-apple-gray">No salary records.</div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<script>
function logout(){
    fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})})
        .then(()=>window.location.href='../index.php');
}
</script>
</body>
</html>

<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

// Fetch dashboard stats
$stats = [];
$stats['employees']   = $conn->query("SELECT COUNT(*) as c FROM users WHERE role='employee' AND is_active=1")->fetch_assoc()['c'];
$stats['leaves']      = $conn->query("SELECT COUNT(*) as c FROM leaves WHERE status='pending'")->fetch_assoc()['c'];
$stats['salary_due']  = $conn->query("SELECT COUNT(*) as c FROM salary WHERE status='pending'")->fetch_assoc()['c'];
$stats['avg_score']   = round($conn->query("SELECT AVG(knowledge_score) as a FROM employee_profiles WHERE uid != 'ADMIN001'")->fetch_assoc()['a'] ?? 0);

// Recent activity
$recent_leaves = $conn->query("
    SELECT l.*, u.uid, ep.first_name, ep.last_name, ep.department
    FROM leaves l
    JOIN users u ON u.id = l.user_id
    JOIN employee_profiles ep ON ep.user_id = l.user_id
    ORDER BY l.applied_on DESC LIMIT 5
");

// Top employees by score
$top_employees = $conn->query("
    SELECT ep.*, u.email FROM employee_profiles ep
    JOIN users u ON u.id = ep.user_id
    WHERE ep.uid != 'ADMIN001'
    ORDER BY ep.knowledge_score DESC LIMIT 5
");

// Today's attendance
$today_att = $conn->query("
    SELECT COUNT(*) as present FROM attendance WHERE date=CURDATE() AND status='present'
")->fetch_assoc()['present'];

// Dept breakdown
$dept_data = $conn->query("SELECT department, COUNT(*) as count FROM employee_profiles WHERE uid != 'ADMIN001' GROUP BY department");
$depts = []; while($r = $dept_data->fetch_assoc()) $depts[] = $r;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}
        body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}
        .nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1);color:#007AFF}
        .nav-item.active svg{color:#007AFF}
        .stat-card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);transition:transform 0.2s;border:1px solid rgba(0,0,0,0.04)}
        .stat-card:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,0.10)}
        .main-content{background:#F5F5F7;min-height:100vh}
        @keyframes fadeIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
        .fade-in{animation:fadeIn 0.5s ease forwards}
        .tier-diamond{background:linear-gradient(135deg,#a78bfa,#7c3aed);color:white}
        .tier-gold{background:linear-gradient(135deg,#fbbf24,#d97706);color:white}
        .tier-silver{background:linear-gradient(135deg,#94a3b8,#64748b);color:white}
        .tier-bronze{background:linear-gradient(135deg,#d97706,#92400e);color:white}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar.php'; ?>

<!-- ===== MAIN CONTENT ===== -->
<main class="ml-64 flex-1 p-8 main-content">

    <!-- Header -->
    <div class="flex items-center justify-between mb-8 fade-in">
        <div>
            <h1 class="text-2xl font-bold text-apple-dark">Command Dashboard</h1>
            <p class="text-apple-gray text-sm mt-0.5"><?= date('l, F j, Y') ?> &bull; Good <?= date('H')<12?'Morning':(date('H')<17?'Afternoon':'Evening') ?>, <?= explode(' ',$_SESSION['name'])[0] ?> 👋</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="bg-white rounded-xl px-4 py-2 flex items-center gap-2 shadow-sm border border-gray-100">
                <div class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></div>
                <span class="text-xs font-medium text-apple-dark">System Online</span>
            </div>
        </div>
    </div>

    <!-- Stat Cards -->
    <div class="grid grid-cols-4 gap-5 mb-8 fade-in">
        <!-- Total Employees -->
        <div class="stat-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="#007AFF" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="#007AFF" stroke-width="2"/></svg>
                </div>
                <span class="text-xs font-medium text-green-600 bg-green-50 px-2 py-0.5 rounded-full">Active</span>
            </div>
            <div class="text-3xl font-bold text-apple-dark"><?= $stats['employees'] ?></div>
            <div class="text-xs text-apple-gray mt-1 font-medium">Total Employees</div>
        </div>
        <!-- Pending Leaves -->
        <div class="stat-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2" stroke="#FF9500" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke="#FF9500" stroke-width="2"/></svg>
                </div>
                <?php if($stats['leaves']>0): ?>
                <span class="text-xs font-medium text-orange-600 bg-orange-50 px-2 py-0.5 rounded-full">Action Needed</span>
                <?php endif; ?>
            </div>
            <div class="text-3xl font-bold text-apple-dark"><?= $stats['leaves'] ?></div>
            <div class="text-xs text-apple-gray mt-1 font-medium">Pending Leaves</div>
        </div>
        <!-- Salary Due -->
        <div class="stat-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23" stroke="#5856D6" stroke-width="2"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="#5856D6" stroke-width="2"/></svg>
                </div>
                <?php if($stats['salary_due']>0): ?>
                <span class="text-xs font-medium text-purple-600 bg-purple-50 px-2 py-0.5 rounded-full">Pending</span>
                <?php endif; ?>
            </div>
            <div class="text-3xl font-bold text-apple-dark"><?= $stats['salary_due'] ?></div>
            <div class="text-xs text-apple-gray mt-1 font-medium">Salary Payments Due</div>
        </div>
        <!-- Avg Knowledge Score -->
        <div class="stat-card p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-xl bg-green-50 flex items-center justify-center">
                    <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><polyline points="23,6 13.5,15.5 8.5,10.5 1,18" stroke="#34C759" stroke-width="2"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-apple-dark"><?= $stats['avg_score'] ?><span class="text-lg text-apple-gray">/100</span></div>
            <div class="text-xs text-apple-gray mt-1 font-medium">Avg Knowledge Score</div>
        </div>
    </div>

    <!-- Middle Row: Chart + Top Performers -->
    <div class="grid grid-cols-3 gap-5 mb-8">
        <!-- Knowledge Score Distribution Chart -->
        <div class="col-span-2 stat-card p-6 fade-in">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-apple-dark">Skill Growth Overview</h3>
                <span class="text-xs text-apple-gray bg-gray-50 px-3 py-1 rounded-full">Last 6 months</span>
            </div>
            <canvas id="growthChart" height="160"></canvas>
        </div>

        <!-- Top Performers -->
        <div class="stat-card p-6 fade-in">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-apple-dark">Top Performers</h3>
                <a href="leaderboard.php" class="text-xs text-apple-blue font-medium">View all →</a>
            </div>
            <div class="space-y-3">
                <?php $rank=1; while($emp=$top_employees->fetch_assoc()): ?>
                <div class="flex items-center gap-3">
                    <div class="w-7 h-7 rounded-full flex items-center justify-center text-xs font-bold <?= $rank===1?'bg-yellow-400 text-white':($rank===2?'bg-gray-300 text-gray-700':'bg-orange-300 text-white') ?>">
                        <?= $rank ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-xs font-semibold text-apple-dark truncate"><?= $emp['first_name'].' '.$emp['last_name'] ?></div>
                        <div class="text-xs text-apple-gray"><?= $emp['department'] ?></div>
                    </div>
                    <div class="text-right">
                        <div class="text-xs font-bold text-apple-dark"><?= $emp['knowledge_score'] ?></div>
                        <span class="text-xs px-1.5 py-0.5 rounded-md tier-<?= strtolower($emp['tier']) ?>"><?= $emp['tier'] ?></span>
                    </div>
                </div>
                <?php $rank++; endwhile; ?>
            </div>
        </div>
    </div>

    <!-- Bottom Row: Recent Leaves + Today's Attendance -->
    <div class="grid grid-cols-2 gap-5 fade-in">
        <!-- Recent Leave Requests -->
        <div class="stat-card p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-apple-dark">Recent Leave Requests</h3>
                <a href="leaves.php" class="text-xs text-apple-blue font-medium">Manage →</a>
            </div>
            <div class="space-y-3">
                <?php while($lv=$recent_leaves->fetch_assoc()): ?>
                <div class="flex items-start justify-between p-3 bg-gray-50 rounded-xl">
                    <div>
                        <div class="text-xs font-semibold text-apple-dark"><?= $lv['first_name'].' '.$lv['last_name'] ?> <span class="text-apple-gray font-normal">(<?= $lv['uid'] ?>)</span></div>
                        <div class="text-xs text-apple-gray mt-0.5"><?= ucfirst($lv['leave_type']) ?> · <?= $lv['start_date'] ?> – <?= $lv['end_date'] ?></div>
                        <div class="text-xs text-apple-gray mt-0.5 truncate max-w-xs"><?= substr($lv['reason'],0,50) ?>…</div>
                    </div>
                    <span class="text-xs px-2 py-0.5 rounded-full font-medium flex-shrink-0 <?=
                        $lv['status']==='pending'?'bg-yellow-50 text-yellow-700 border border-yellow-200':
                        ($lv['status']==='approved'?'bg-green-50 text-green-700 border border-green-200':
                        'bg-red-50 text-red-700 border border-red-200') ?>">
                        <?= ucfirst($lv['status']) ?>
                    </span>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- Dept Distribution + Today Attendance -->
        <div class="space-y-5">
            <div class="stat-card p-6">
                <h3 class="text-base font-semibold text-apple-dark mb-4">Department Distribution</h3>
                <canvas id="deptChart" height="140"></canvas>
            </div>
            <div class="stat-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <div class="text-xs text-apple-gray font-medium">Today's Attendance</div>
                        <div class="text-2xl font-bold text-apple-dark mt-1"><?= $today_att ?> <span class="text-sm text-apple-gray font-normal">/ <?= $stats['employees'] ?></span></div>
                    </div>
                    <div class="w-14 h-14">
                        <canvas id="attPie"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
// Growth Chart
new Chart(document.getElementById('growthChart'), {
    type: 'line',
    data: {
        labels: ['Nov','Dec','Jan','Feb','Mar','Apr'],
        datasets: [{
            label: 'Avg Knowledge Score',
            data: [42,48,54,58,63,<?= $stats['avg_score'] ?>],
            borderColor: '#007AFF',
            backgroundColor: 'rgba(0,122,255,0.08)',
            borderWidth: 2.5,
            tension: 0.4,
            fill: true,
            pointBackgroundColor: '#007AFF',
            pointRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: false, min: 30, max: 100, grid: { color: '#f0f0f0' }, ticks: { font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});

// Dept Doughnut
const depts = <?= json_encode($depts) ?>;
new Chart(document.getElementById('deptChart'), {
    type: 'doughnut',
    data: {
        labels: depts.map(d=>d.department),
        datasets: [{ data: depts.map(d=>d.count), backgroundColor: ['#007AFF','#5856D6','#FF9500','#34C759','#FF3B30'], borderWidth: 0, hoverOffset: 4 }]
    },
    options: { responsive: true, plugins: { legend: { position: 'right', labels: { font: { size: 11 }, boxWidth: 10 } } }, cutout: '65%' }
});

// Attendance Pie
new Chart(document.getElementById('attPie'), {
    type: 'doughnut',
    data: {
        datasets: [{ data: [<?= $today_att ?>, <?= max(0, $stats['employees']-$today_att) ?>], backgroundColor: ['#34C759','#F5F5F7'], borderWidth: 0 }]
    },
    options: { responsive: true, plugins: { legend: { display: false } }, cutout: '70%' }
});

function logout() {
    fetch('../api/auth.php', { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({action:'logout'}) })
        .then(()=> window.location.href='../index.php');
}
</script>
</body>
</html>

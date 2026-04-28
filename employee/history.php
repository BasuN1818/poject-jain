<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('employee');
$userId = $_SESSION['user_id'];
$profile = $conn->query("SELECT ep.* FROM employee_profiles ep WHERE ep.user_id=$userId")->fetch_assoc();

// Attendance History
$attendance = $conn->query("SELECT * FROM attendance WHERE user_id=$userId ORDER BY date DESC LIMIT 30");

// Salary History
$salary = $conn->query("SELECT * FROM salary WHERE user_id=$userId ORDER BY year DESC, id DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My History – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#f0f9ff; color:#0f172a;}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(52,199,89,0.08)}
        .tab{border-radius:12px;transition:all 0.2s;cursor:pointer;font-weight:600;font-size:13px;padding:10px 20px;border:none;font-family:inherit}
        .tab.active{background:linear-gradient(135deg,#34C759,#00C7BE);color:white;box-shadow:0 4px 14px rgba(52,199,89,0.35)}
        .tab:not(.active){background:#fff;color:#86868B;border:1px solid rgba(52,199,89,0.15)}
        .tab:not(.active):hover{background:#f0fdf4;color:#16a34a}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar_emp.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-apple-dark">My History</h1>
        <p class="text-apple-gray text-sm mt-0.5">View your attendance and salary records</p>
    </div>

    <!-- Tabs -->
    <div class="flex gap-2 mb-6">
        <button id="tab-att" class="tab active px-5 py-2.5 text-sm font-semibold shadow-sm border border-gray-100"
            onclick="showTab('att')">📅 Attendance History</button>
        <button id="tab-sal" class="tab px-5 py-2.5 text-sm font-semibold shadow-sm border border-gray-100"
            onclick="showTab('sal')">💰 Salary History</button>
    </div>

    <!-- Attendance Tab -->
    <div id="panel-att" class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h3 class="text-sm font-semibold text-apple-dark">Last 30 Attendance Records</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-apple-gray uppercase tracking-wide border-b border-gray-50">
                        <th class="px-5 py-3 text-left">Date</th>
                        <th class="px-5 py-3 text-left">Day</th>
                        <th class="px-5 py-3 text-left">Check In</th>
                        <th class="px-5 py-3 text-left">Check Out</th>
                        <th class="px-5 py-3 text-left">Hours</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $cnt=0; while($att=$attendance->fetch_assoc()): $cnt++;
                        $hrs='';
                        if($att['check_in']&&$att['check_out']) {
                            $diff=(strtotime($att['check_out'])-strtotime($att['check_in']))/3600;
                            $hrs=number_format($diff,1).'h';
                        }
                    ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5 font-semibold text-apple-dark"><?= date('M j, Y',strtotime($att['date'])) ?></td>
                        <td class="px-5 py-3.5 text-apple-gray"><?= date('l',strtotime($att['date'])) ?></td>
                        <td class="px-5 py-3.5"><?= $att['check_in']?date('h:i A',strtotime($att['check_in'])):'—' ?></td>
                        <td class="px-5 py-3.5 text-apple-gray"><?= $att['check_out']?date('h:i A',strtotime($att['check_out'])):'—' ?></td>
                        <td class="px-5 py-3.5 font-medium"><?= $hrs?:'—' ?></td>
                        <td class="px-5 py-3.5">
                            <span class="text-xs flex items-center gap-1.5 font-semibold">
                                <div class="w-2 h-2 rounded-full <?= ['present'=>'bg-green-400','absent'=>'bg-red-400','late'=>'bg-orange-400','half_day'=>'bg-purple-400'][$att['status']]??'bg-gray-400' ?>"></div>
                                <?= ucwords(str_replace('_',' ',$att['status'])) ?>
                            </span>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(!$cnt): ?>
                    <tr><td colspan="6" class="px-5 py-12 text-center text-apple-gray">
                        <div class="text-4xl mb-2">📅</div><div class="text-sm">No attendance records yet</div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Salary Tab -->
    <div id="panel-sal" class="hidden card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h3 class="text-sm font-semibold text-apple-dark">Salary Payment History</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-apple-gray uppercase tracking-wide border-b border-gray-50">
                        <th class="px-5 py-3 text-left">Period</th>
                        <th class="px-5 py-3 text-right">Basic</th>
                        <th class="px-5 py-3 text-right">Allowances</th>
                        <th class="px-5 py-3 text-right">Deductions</th>
                        <th class="px-5 py-3 text-right">Net Salary</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Paid On</th>
                        <th class="px-5 py-3 text-left">Transaction</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $cnt=0; while($sal=$salary->fetch_assoc()): $cnt++; ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3.5 font-semibold text-apple-dark"><?= $sal['month'] ?> <?= $sal['year'] ?></td>
                        <td class="px-5 py-3.5 text-right">₹<?= number_format($sal['basic_salary'],0,'.',',') ?></td>
                        <td class="px-5 py-3.5 text-right text-green-600">+₹<?= number_format($sal['allowances'],0,'.',',') ?></td>
                        <td class="px-5 py-3.5 text-right text-red-500">-₹<?= number_format($sal['deductions'],0,'.',',') ?></td>
                        <td class="px-5 py-3.5 text-right font-bold text-apple-dark">₹<?= number_format($sal['net_salary'],0,'.',',') ?></td>
                        <td class="px-5 py-3.5">
                            <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $sal['status']==='paid'?'bg-green-50 text-green-700 border border-green-200':'bg-yellow-50 text-yellow-700 border border-yellow-200' ?>">
                                <?= ucfirst($sal['status']) ?>
                            </span>
                        </td>
                        <td class="px-5 py-3.5 text-apple-gray text-xs"><?= $sal['paid_on']?date('M j, Y',strtotime($sal['paid_on'])):'—' ?></td>
                        <td class="px-5 py-3.5 font-mono text-xs text-apple-gray"><?= $sal['transaction_id']?:'—' ?></td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(!$cnt): ?>
                    <tr><td colspan="8" class="px-5 py-12 text-center text-apple-gray">
                        <div class="text-4xl mb-2">💰</div><div class="text-sm">No salary records yet</div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>
<script>
function showTab(t) {
    document.getElementById('panel-att').classList.toggle('hidden', t!=='att');
    document.getElementById('panel-sal').classList.toggle('hidden', t!=='sal');
    document.getElementById('tab-att').classList.toggle('active', t==='att');
    document.getElementById('tab-sal').classList.toggle('active', t==='sal');
}
function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}
</script>
</body>
</html>

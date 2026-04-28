<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

// Handle salary payment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay') {
    $salaryId = (int)$_POST['salary_id'];
    $txId     = 'TXN'.strtoupper(uniqid());
    $conn->query("UPDATE salary SET status='paid', paid_on=NOW(), transaction_id='$txId' WHERE id=$salaryId");
    header('Location: salary.php?paid=1');
    exit;
}

// Filter
$month   = $_GET['month'] ?? date('F');
$year    = (int)($_GET['year'] ?? date('Y'));
$status  = $_GET['status'] ?? 'all';

$where = "WHERE s.month='$month' AND s.year=$year";
if ($status !== 'all') $where .= " AND s.status='$status'";

$records = $conn->query("
    SELECT s.*, u.uid, ep.first_name, ep.last_name, ep.department, ep.designation
    FROM salary s
    JOIN users u ON u.id=s.user_id
    JOIN employee_profiles ep ON ep.user_id=s.user_id
    $where
    ORDER BY s.status ASC, ep.first_name ASC
");

// Summary
$summary = $conn->query("
    SELECT 
        SUM(net_salary) as total_due,
        SUM(IF(status='paid',net_salary,0)) as total_paid,
        SUM(IF(status='pending',net_salary,0)) as total_pending,
        COUNT(IF(status='pending',1,NULL)) as pending_count
    FROM salary WHERE month='$month' AND year=$year
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Salary & Payroll – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}.nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1)}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.04)}
        .btn-pay{background:linear-gradient(135deg,#34C759,#28a745);transition:all 0.2s;box-shadow:0 4px 12px rgba(52,199,89,0.3)}
        .btn-pay:hover{transform:translateY(-1px)}
        .input-field{border:1.5px solid #e5e5ea;background:#fafafa;transition:all 0.2s}
        .input-field:focus{border-color:#007AFF;outline:none}
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
            <h1 class="text-2xl font-bold text-apple-dark">Salary & Payroll</h1>
            <p class="text-apple-gray text-sm mt-0.5">Manage monthly salary disbursements</p>
        </div>
        <?php if(isset($_GET['paid'])): ?>
        <div class="px-4 py-2 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700 font-medium">✅ Salary marked as paid</div>
        <?php endif; ?>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-4 gap-5 mb-8">
        <div class="card p-5">
            <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23" stroke="#007AFF" stroke-width="2"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="#007AFF" stroke-width="2"/></svg>
            </div>
            <div class="text-xl font-bold text-apple-dark">₹<?= number_format($summary['total_due'],0,'.',',') ?></div>
            <div class="text-xs text-apple-gray mt-0.5">Total Payroll (<?= $month ?>)</div>
        </div>
        <div class="card p-5">
            <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" stroke="#34C759" stroke-width="2"/><polyline points="22,4 12,14.01 9,11.01" stroke="#34C759" stroke-width="2"/></svg>
            </div>
            <div class="text-xl font-bold text-apple-dark">₹<?= number_format($summary['total_paid'],0,'.',',') ?></div>
            <div class="text-xs text-apple-gray mt-0.5">Total Paid</div>
        </div>
        <div class="card p-5">
            <div class="w-10 h-10 bg-orange-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="#FF9500" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="#FF9500" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="#FF9500" stroke-width="2"/></svg>
            </div>
            <div class="text-xl font-bold text-apple-dark">₹<?= number_format($summary['total_pending'],0,'.',',') ?></div>
            <div class="text-xs text-apple-gray mt-0.5">Pending Disbursement</div>
        </div>
        <div class="card p-5">
            <div class="w-10 h-10 bg-purple-50 rounded-xl flex items-center justify-center mb-3">
                <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="#5856D6" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="#5856D6" stroke-width="2"/></svg>
            </div>
            <div class="text-xl font-bold text-apple-dark"><?= $summary['pending_count'] ?></div>
            <div class="text-xs text-apple-gray mt-0.5">Employees Awaiting</div>
        </div>
    </div>

    <!-- Filters -->
    <div class="flex items-center gap-3 mb-6">
        <form method="GET" class="flex gap-3">
            <select name="month" class="input-field px-3.5 py-2.5 rounded-xl text-sm">
                <?php foreach(['January','February','March','April','May','June','July','August','September','October','November','December'] as $m): ?>
                <option <?= $month===$m?'selected':'' ?>><?= $m ?></option>
                <?php endforeach; ?>
            </select>
            <select name="year" class="input-field px-3.5 py-2.5 rounded-xl text-sm">
                <?php for($y=2024;$y<=2026;$y++): ?>
                <option <?= $year===$y?'selected':'' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <select name="status" class="input-field px-3.5 py-2.5 rounded-xl text-sm">
                <option value="all" <?= $status==='all'?'selected':'' ?>>All Statuses</option>
                <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
                <option value="paid" <?= $status==='paid'?'selected':'' ?>>Paid</option>
            </select>
            <button type="submit" class="px-4 py-2.5 rounded-xl text-sm font-semibold text-white" style="background:linear-gradient(135deg,#007AFF,#0055D4)">Apply</button>
        </form>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-apple-gray uppercase tracking-wide border-b border-gray-50">
                        <th class="px-5 py-3 text-left">Employee</th>
                        <th class="px-5 py-3 text-left">Department</th>
                        <th class="px-5 py-3 text-right">Basic</th>
                        <th class="px-5 py-3 text-right">Allowances</th>
                        <th class="px-5 py-3 text-right">Deductions</th>
                        <th class="px-5 py-3 text-right">Net Salary</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $count=0; while($sal=$records->fetch_assoc()): $count++; ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                                    <?= strtoupper(substr($sal['first_name'],0,1).substr($sal['last_name'],0,1)) ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-apple-dark"><?= $sal['first_name'].' '.$sal['last_name'] ?></div>
                                    <div class="text-xs text-apple-gray"><?= $sal['uid'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-apple-gray"><?= $sal['department'] ?></td>
                        <td class="px-5 py-4 text-right font-medium text-apple-dark">₹<?= number_format($sal['basic_salary'],0,'.',',') ?></td>
                        <td class="px-5 py-4 text-right text-green-600 font-medium">+₹<?= number_format($sal['allowances'],0,'.',',') ?></td>
                        <td class="px-5 py-4 text-right text-red-500 font-medium">-₹<?= number_format($sal['deductions'],0,'.',',') ?></td>
                        <td class="px-5 py-4 text-right font-bold text-apple-dark">₹<?= number_format($sal['net_salary'],0,'.',',') ?></td>
                        <td class="px-5 py-4">
                            <div>
                                <span class="text-xs px-2 py-0.5 rounded-full font-semibold <?= $sal['status']==='paid'?'bg-green-50 text-green-700 border border-green-200':'bg-yellow-50 text-yellow-700 border border-yellow-200' ?>">
                                    <?= ucfirst($sal['status']) ?>
                                </span>
                                <?php if($sal['status']==='paid' && $sal['paid_on']): ?>
                                <div class="text-xs text-apple-gray mt-0.5"><?= date('M j, Y', strtotime($sal['paid_on'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <?php if($sal['status']==='pending'): ?>
                            <button onclick="confirmPay(<?= $sal['id'] ?>, '<?= $sal['first_name'].' '.$sal['last_name'] ?>', <?= $sal['net_salary'] ?>)"
                                class="btn-pay text-white text-xs px-4 py-1.5 rounded-lg font-semibold">
                                Pay Now
                            </button>
                            <?php else: ?>
                            <div class="text-xs text-apple-gray font-mono"><?= $sal['transaction_id'] ?></div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php if(!$count): ?>
                    <tr><td colspan="8" class="px-5 py-12 text-center text-apple-gray">
                        <div class="text-4xl mb-2">💰</div><div class="text-sm font-medium">No salary records for selected period</div>
                    </td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<!-- Payment Gateway Modal -->
<div id="payModal" class="modal" onclick="if(event.target===this && !document.getElementById('processingOverlay').classList.contains('flex'))this.classList.remove('open')">
    <div class="modal-box bg-white rounded-2xl shadow-2xl overflow-hidden w-full max-w-md mx-4 relative" id="payment-gate-box">
        
        <!-- Processing Overlay -->
        <div id="processingOverlay" class="absolute inset-0 bg-white/95 backdrop-blur-md z-50 hidden flex-col items-center justify-center">
            <div id="spinner" class="w-12 h-12 border-4 border-blue-100 border-t-blue-600 rounded-full animate-spin mb-5 shadow-sm"></div>
            <div id="processingText" class="font-bold text-apple-dark text-lg tracking-tight">Connecting to Gateway...</div>
        </div>

        <div class="bg-gray-50 border-b border-gray-100 p-6 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-black text-white rounded-xl flex items-center justify-center font-bold text-xl shadow-sm">
                    $
                </div>
                <div>
                    <h2 class="text-lg font-bold text-apple-dark leading-tight">Secure Checkout</h2>
                    <p class="text-apple-gray text-xs font-medium">Powered by SkillBridge Pay</p>
                </div>
            </div>
            <div class="text-right">
                <div class="text-[10px] text-apple-gray uppercase font-bold tracking-wider">Amount to Pay</div>
                <div class="text-xl font-bold text-apple-dark" id="pay-amount-display">₹0</div>
            </div>
        </div>

        <div class="p-6">
            <div class="mb-5 p-3.5 bg-blue-50/50 border border-blue-100 rounded-xl">
                <div class="text-[10px] text-apple-gray font-bold uppercase tracking-wider mb-1">Paying To Employee</div>
                <div class="font-bold text-apple-dark text-sm flex items-center gap-2">
                    <span id="pay-emp-name"></span>
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#34C759" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Corporate Card Number</label>
                    <div class="relative">
                        <input type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 pl-12 text-sm focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none transition-all font-mono font-medium text-gray-700 bg-white" value="4111 1111 1111 1111" disabled>
                        <div class="absolute left-4 top-1/2 -translate-y-1/2">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="#9ca3af" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>
                        </div>
                    </div>
                </div>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">Expiry</label>
                        <input type="text" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm font-mono font-medium bg-gray-50 text-gray-500" value="12/28" disabled>
                    </div>
                    <div class="flex-1">
                        <label class="block text-[10px] font-bold text-gray-500 uppercase tracking-wider mb-1.5">CVC</label>
                        <input type="password" class="w-full border border-gray-200 rounded-xl px-4 py-3 text-sm font-mono font-medium bg-gray-50 text-gray-500" value="123" disabled>
                    </div>
                </div>
            </div>
            
            <form id="paymentForm" method="POST" class="mt-6">
                <input type="hidden" name="action" value="pay">
                <input type="hidden" name="salary_id" id="pay-salary-id">
                <button type="button" onclick="simulatePayment()" class="w-full py-3.5 rounded-xl text-white text-sm font-bold shadow-lg shadow-blue-500/30 flex items-center justify-center gap-2 transition-all hover:-translate-y-0.5 hover:shadow-xl hover:shadow-blue-500/40" style="background:linear-gradient(135deg, #007AFF, #5856D6)">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 15V3m0 12l-4-4m4 4l4-4M2 17l.621 2.485A2 2 0 0 0 4.561 21h14.878a2 2 0 0 0 1.94-1.515L22 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    Pay Securely
                </button>
            </form>
            <div class="text-center mt-4 text-[11px] text-gray-400 font-semibold flex items-center justify-center gap-1.5">
                <svg width="12" height="12" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                256-bit Bank-Grade SSL Encryption
            </div>
        </div>
    </div>
</div>

<script>
function confirmPay(id, name, amount) {
    document.getElementById('pay-salary-id').value = id;
    document.getElementById('pay-emp-name').textContent = name;
    document.getElementById('pay-amount-display').textContent = `₹${parseInt(amount).toLocaleString('en-IN')}`;
    document.getElementById('payModal').classList.add('open');
}

function simulatePayment() {
    const overlay = document.getElementById('processingOverlay');
    const text = document.getElementById('processingText');
    overlay.classList.remove('hidden');
    overlay.classList.add('flex'); // Show overlay
    
    // Simulating gateway phases
    setTimeout(() => { text.textContent = "Authorizing with Bank..."; }, 1000);
    setTimeout(() => { text.textContent = "Verifying Security Token..."; }, 2200);
    setTimeout(() => { 
        // Success State
        document.getElementById('spinner').style.display = 'none';
        overlay.innerHTML = `<div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mb-4"><svg width="32" height="32" fill="none" viewBox="0 0 24 24" stroke="#16a34a" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6L9 17l-5-5"/></svg></div><div class="font-bold text-xl tracking-tight text-green-600">Payment Successful!</div><div class="text-sm font-medium text-gray-500 mt-1">Generating receipt...</div>`;
    }, 3500);
    
    // Submit form after animation finishes
    setTimeout(() => {
        document.getElementById('paymentForm').submit();
    }, 5000);
}

function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}
</script>
</body>
</html>

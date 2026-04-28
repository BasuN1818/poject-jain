<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

$msg = '';
$err = '';

// Get date filter
$date = isset($_GET['date']) ? sanitize($conn, $_GET['date']) : date('Y-m-d');

// Handle saving attendance
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_attendance') {
    $conn->begin_transaction();
    try {
        $attendance_data = $_POST['attendance'] ?? [];
        $check_in_data = $_POST['check_in'] ?? [];
        $check_out_data = $_POST['check_out'] ?? [];

        $check_stmt = $conn->prepare("SELECT id FROM attendance WHERE user_id=? AND date=?");
        $update_stmt = $conn->prepare("UPDATE attendance SET status=?, check_in=?, check_out=? WHERE id=?");
        $insert_stmt = $conn->prepare("INSERT INTO attendance (user_id, date, status, check_in, check_out) VALUES (?, ?, ?, ?, ?)");

        foreach ($attendance_data as $user_id => $status) {
            $user_id = (int)$user_id;
            $status = sanitize($conn, $status);
            
            // Convert empty time strings to null
            $ci = !empty($check_in_data[$user_id]) ? sanitize($conn, $check_in_data[$user_id]) : null;
            $co = !empty($check_out_data[$user_id]) ? sanitize($conn, $check_out_data[$user_id]) : null;

            if (empty($status) || $status === 'none') {
                // If they changed status back to 'none', we might want to delete the record, but skipping is safer for now
                continue;
            }

            $check_stmt->bind_param('is', $user_id, $date);
            $check_stmt->execute();
            $res = $check_stmt->get_result();

            if ($row = $res->fetch_assoc()) {
                $update_stmt->bind_param('sssi', $status, $ci, $co, $row['id']);
                $update_stmt->execute();
            } else {
                $insert_stmt->bind_param('issss', $user_id, $date, $status, $ci, $co);
                $insert_stmt->execute();
            }
        }
        $conn->commit();
        $msg = 'Attendance saved successfully for ' . date('M j, Y', strtotime($date));
    } catch(Exception $e) {
        $conn->rollback();
        $err = 'Error saving attendance: ' . $e->getMessage();
    }
}

// Fetch ALL active employees and left join their attendance for the date
$records = $conn->query("
    SELECT u.id as user_id, u.uid, ep.first_name, ep.last_name, ep.department, ep.designation,
           a.id as attendance_id, a.status, a.check_in, a.check_out, a.notes
    FROM users u
    JOIN employee_profiles ep ON ep.user_id=u.id
    LEFT JOIN attendance a ON u.id=a.user_id AND a.date='$date'
    WHERE u.role='employee' AND u.is_active=1
    ORDER BY ep.first_name ASC
");

// Stats for the day
$stats = $conn->query("
    SELECT status, COUNT(*) as cnt FROM attendance WHERE date='$date' GROUP BY status
")->fetch_all(MYSQLI_ASSOC);
$stat_map = array_column($stats, 'cnt', 'status');
$total_marked = array_sum($stat_map);
$total = $records->num_rows;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendance – SkillBridge AI</title>
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
        .status-dot-present{background:#34C759}.status-dot-absent{background:#FF3B30}
        .status-dot-late{background:#FF9500}.status-dot-half_day{background:#5856D6}
        
        .select-status { border: 1.5px solid #e5e5ea; border-radius: 8px; padding: 6px 10px; font-size: 13px; font-weight: 600; outline: none; transition: border-color 0.2s; background: #fafafa; width: 130px;}
        .select-status:focus { border-color: #007AFF; background: #fff; }
        .select-present { color: #16a34a; background:#f0fdf4!important; border-color:#bbf7d0;}
        .select-absent { color: #dc2626; background:#fef2f2!important; border-color:#fecaca;}
        .select-late { color: #d97706; background:#fff7ed!important; border-color:#fed7aa;}
        .select-half { color: #7c3aed; background:#faf5ff!important; border-color:#e9d5ff;}
        
        .time-input { padding: 5px 8px; border: 1.5px solid #e5e5ea; border-radius: 8px; font-size: 13px; font-weight: 500; color: #1D1D1F; outline: none; background: #fafafa; }
        .time-input:focus { border-color: #007AFF; background: #fff; box-shadow:0 0 0 2px rgba(0,122,255,0.1); }
        .btn-primary{background:linear-gradient(135deg,#007AFF,#0055D4);transition:all 0.2s;box-shadow:0 4px 16px rgba(0,122,255,0.3)}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 20px rgba(0,122,255,0.4)}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-apple-dark">Assign Attendance</h1>
            <p class="text-apple-gray text-sm mt-0.5">Mark or edit daily attendance for all employees</p>
        </div>
        <form method="GET" class="flex items-center gap-3 bg-white p-2 rounded-2xl shadow-sm border border-gray-100">
            <label class="text-sm font-semibold text-apple-dark pl-3">Date:</label>
            <input name="date" type="date" value="<?= $date ?>" class="input-field px-3.5 py-2 rounded-xl text-sm" onchange="this.form.submit()">
        </form>
    </div>

    <?php if($msg): ?>
    <div class="mb-6 p-4 rounded-2xl bg-green-50 border border-green-200 text-green-700 text-sm font-medium flex items-center gap-2">
        ✅ <?= $msg ?>
    </div>
    <?php endif; ?>
    <?php if($err): ?>
    <div class="mb-6 p-4 rounded-2xl bg-red-50 border border-red-200 text-red-700 text-sm font-medium flex items-center gap-2">
        ⚠️ <?= $err ?>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="grid grid-cols-5 gap-4 mb-8">
        <div class="card p-5" style="background: linear-gradient(135deg, #007AFF, #5856D6); color: white;">
            <div class="text-2xl font-bold mb-1"><?= $total_marked ?> <span class="text-sm font-normal opacity-70">/ <?= $total ?></span></div>
            <div class="text-xs font-medium opacity-90">Total Marked</div>
        </div>
        <?php
        $stat_items = [
            ['present','Present','#34C759','bg-green-50'],
            ['absent','Absent','#FF3B30','bg-red-50'],
            ['late','Late','#FF9500','bg-orange-50'],
            ['half_day','Half Day','#5856D6','bg-purple-50'],
        ];
        foreach($stat_items as [$key,$label,$color,$bg]):
        ?>
        <div class="card p-4">
            <div class="flex items-center gap-2 mb-2">
                <div class="w-3 h-3 rounded-full status-dot-<?= $key ?>"></div>
                <div class="text-xs text-apple-gray font-medium uppercase tracking-wide"><?= $label ?></div>
            </div>
            <div class="text-2xl font-bold text-apple-dark"><?= $stat_map[$key] ?? 0 ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <form method="POST">
            <input type="hidden" name="action" value="save_attendance">
            
            <div class="px-5 py-4 border-b border-gray-50 flex items-center justify-between bg-gray-50/50">
                <h3 class="text-sm font-semibold text-apple-dark">Records for <?= date('F j, Y', strtotime($date)) ?></h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="markAll('present')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-green-50 text-green-700 hover:bg-green-100 transition-colors border border-green-200">Mark All Present</button>
                    <button type="button" onclick="markAll('absent')" class="px-3 py-1.5 text-xs font-semibold rounded-lg bg-red-50 text-red-700 hover:bg-red-100 transition-colors border border-red-200">Mark All Absent</button>
                </div>
            </div>
            <div class="overflow-x-auto" style="max-height: 60vh;">
                <table class="w-full text-sm">
                    <thead class="sticky top-0 bg-white z-10 shadow-sm">
                        <tr class="text-xs font-semibold text-apple-gray uppercase tracking-wide border-b border-gray-50">
                            <th class="px-5 py-4 text-left">Employee</th>
                            <th class="px-5 py-4 text-left">UID</th>
                            <th class="px-5 py-4 text-left">Status</th>
                            <th class="px-5 py-4 text-left">Check In (Optional)</th>
                            <th class="px-5 py-4 text-left">Check Out (Optional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($total === 0): ?>
                        <tr><td colspan="5" class="px-5 py-12 text-center text-apple-gray">No active employees found.</td></tr>
                        <?php endif; ?>

                        <?php while($emp=$records->fetch_assoc()): ?>
                        <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                                        <?= strtoupper(substr($emp['first_name'],0,1).substr($emp['last_name'],0,1)) ?>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-apple-dark"><?= $emp['first_name'].' '.$emp['last_name'] ?></div>
                                        <div class="text-xs text-apple-gray"><?= $emp['department'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 font-mono text-xs text-apple-blue font-semibold"><?= $emp['uid'] ?></td>
                            <td class="px-5 py-3">
                                <select name="attendance[<?= $emp['user_id'] ?>]" class="select-status status-select" onchange="updateColor(this)">
                                    <option value="none" class="text-gray-500" <?= empty($emp['status']) ? 'selected' : '' ?>>— Unmarked —</option>
                                    <option value="present" class="select-present" <?= $emp['status'] === 'present' ? 'selected' : '' ?>>Present</option>
                                    <option value="absent" class="select-absent" <?= $emp['status'] === 'absent' ? 'selected' : '' ?>>Absent</option>
                                    <option value="late" class="select-late" <?= $emp['status'] === 'late' ? 'selected' : '' ?>>Late</option>
                                    <option value="half_day" class="select-half" <?= $emp['status'] === 'half_day' ? 'selected' : '' ?>>Half Day</option>
                                </select>
                            </td>
                            <td class="px-5 py-3">
                                <input type="time" name="check_in[<?= $emp['user_id'] ?>]" value="<?= $emp['check_in'] ? date('H:i', strtotime($emp['check_in'])) : '' ?>" class="time-input w-32">
                            </td>
                            <td class="px-5 py-3">
                                <input type="time" name="check_out[<?= $emp['user_id'] ?>]" value="<?= $emp['check_out'] ? date('H:i', strtotime($emp['check_out'])) : '' ?>" class="time-input w-32">
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            
            <?php if($total > 0): ?>
            <div class="px-6 py-5 bg-white border-t border-gray-100 flex justify-end sticky bottom-0 shadow-[0_-4px_12px_rgba(0,0,0,0.02)]">
                <button type="submit" class="btn-primary px-8 py-3 rounded-xl text-white font-bold text-sm tracking-wide">
                    Save All Attendance
                </button>
            </div>
            <?php endif; ?>
        </form>
    </div>
</main>
<script>
function logout() {
    fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})})
        .then(()=>window.location.href='../index.php');
}

function updateColor(select) {
    select.className = 'select-status status-select';
    if(select.value === 'present') select.classList.add('select-present');
    if(select.value === 'absent') select.classList.add('select-absent');
    if(select.value === 'late') select.classList.add('select-late');
    if(select.value === 'half_day') select.classList.add('select-half');
}

// Initial color update on page load
document.querySelectorAll('.status-select').forEach(updateColor);

function markAll(status) {
    if(!confirm(`Are you sure you want to mark all unmarked employees as ${status}?`)) return;
    document.querySelectorAll('.status-select').forEach(select => {
        if(select.value === 'none' || select.value === '') {
            select.value = status;
            updateColor(select);
        }
    });
}
</script>
</body>
</html>

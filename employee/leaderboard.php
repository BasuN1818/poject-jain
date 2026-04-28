<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('employee');
$userId = $_SESSION['user_id'];

$profile = $conn->query("SELECT ep.* FROM employee_profiles ep WHERE ep.user_id=$userId")->fetch_assoc();

// Get all employees ranked
$allEmployees = $conn->query("
    SELECT ep.uid, ep.first_name, ep.last_name, ep.department, ep.knowledge_score, ep.tier,
           u.id as user_id
    FROM employee_profiles ep
    JOIN users u ON u.id=ep.user_id
    WHERE ep.uid != 'ADMIN001' AND u.is_active=1
    ORDER BY ep.knowledge_score DESC
");
$all=[]; while($r=$allEmployees->fetch_assoc()) $all[]=$r;

// Find my rank
$myRank = 1;
foreach($all as $i=>$emp) { if($emp['user_id'] == $userId) { $myRank=$i+1; break; } }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#f8fffe}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(52,199,89,0.08)}
        .tier-diamond{background:linear-gradient(135deg,#a78bfa,#7c3aed);color:white}
        .tier-gold{background:linear-gradient(135deg,#fbbf24,#d97706);color:white}
        .tier-silver{background:linear-gradient(135deg,#94a3b8,#64748b);color:white}
        .tier-bronze{background:linear-gradient(135deg,#d97706,#92400e);color:white}
        .my-row{background:rgba(52,199,89,0.05);border-left:3px solid #34C759}
        @keyframes fadeIn{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}
        .fade-in{animation:fadeIn 0.5s ease forwards}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar_emp.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-apple-dark">Company Leaderboard</h1>
        <p class="text-apple-gray text-sm mt-0.5">See where you stand among your peers</p>
    </div>

    <!-- My Rank Banner -->
    <div class="card p-6 mb-8 fade-in" style="background:linear-gradient(135deg,#34C759,#00C7BE)">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium mb-1" style="color:rgba(255,255,255,0.8)">Your Current Standing</p>
                <h2 class="text-white text-4xl font-bold">#<?= $myRank ?> <span class="text-2xl" style="opacity:0.7">of <?= count($all) ?></span></h2>
                <p class="text-sm mt-2" style="color:rgba(255,255,255,0.8)">Knowledge Score: <strong class="text-white"><?= $profile['knowledge_score'] ?>/100</strong></p>
            </div>
            <div class="text-right">
                <div class="text-6xl">
                    <?= $myRank === 1 ? '👑' : ($myRank <= 3 ? ['🥇','🥈','🥉'][$myRank-1] : '🏅') ?>
                </div>
                <span class="text-xs px-3 py-1.5 rounded-full font-semibold text-white mt-2 inline-block tier-<?= strtolower($profile['tier']) ?>">
                    <?= $profile['tier'] ?> Tier
                </span>
            </div>
        </div>
    </div>

    <!-- Full Rankings Table -->
    <div class="card overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-50">
            <h3 class="text-sm font-semibold text-apple-dark">Full Rankings</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs font-semibold text-apple-gray uppercase tracking-wide border-b border-gray-50">
                        <th class="px-5 py-3 text-left">Rank</th>
                        <th class="px-5 py-3 text-left">Employee</th>
                        <th class="px-5 py-3 text-left">Department</th>
                        <th class="px-5 py-3 text-left">Knowledge Score</th>
                        <th class="px-5 py-3 text-left">Tier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all as $i=>$emp):
                        $rank=$i+1;
                        $isMe = $emp['user_id'] == $userId;
                    ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors <?= $isMe?'my-row':'' ?>">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white
                                    <?= $rank===1?'bg-yellow-400':($rank===2?'bg-gray-400':($rank===3?'bg-orange-400':'bg-gray-100 !text-apple-gray')) ?>">
                                    <?= $rank<=3?['🥇','🥈','🥉'][$rank-1]:'#'.$rank ?>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-xl flex items-center justify-center text-white text-xs font-bold <?= $isMe?'':'opacity-80' ?>" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                                    <?= strtoupper(substr($emp['first_name'],0,1).substr($emp['last_name'],0,1)) ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-apple-dark flex items-center gap-1.5">
                                        <?= $emp['first_name'].' '.$emp['last_name'] ?>
                                        <?php if($isMe): ?><span class="text-xs bg-blue-50 text-apple-blue px-1.5 py-0.5 rounded-md font-medium">You</span><?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-apple-gray"><?= $emp['department'] ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width:<?= $emp['knowledge_score'] ?>%;background:<?= $emp['knowledge_score']>=80?'#a78bfa':($emp['knowledge_score']>=60?'#fbbf24':($emp['knowledge_score']>=40?'#94a3b8':'#d97706')) ?>"></div>
                                </div>
                                <span class="font-bold text-apple-dark"><?= $emp['knowledge_score'] ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2.5 py-1 rounded-lg font-semibold text-white tier-<?= strtolower($emp['tier']) ?>">
                                <?= ['Diamond'=>'💎','Gold'=>'🥇','Silver'=>'🥈','Bronze'=>'🥉'][$emp['tier']]??'' ?> <?= $emp['tier'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tier Info -->
    <div class="card p-5 mt-5">
        <div class="grid grid-cols-4 gap-4 text-center">
            <?php foreach([['💎','Diamond','80+','#a78bfa'],['🥇','Gold','60–79','#fbbf24'],['🥈','Silver','40–59','#94a3b8'],['🥉','Bronze','< 40','#d97706']] as [$i,$n,$r,$c]): ?>
            <div class="p-3 rounded-2xl bg-gray-50">
                <div class="text-2xl"><?= $i ?></div>
                <div class="font-bold text-apple-dark text-sm mt-1"><?= $n ?></div>
                <div class="text-xs text-apple-gray">Score <?= $r ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<script>function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}</script>
</body>
</html>

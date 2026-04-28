<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('admin');

// Fetch ranked employees
$employees = $conn->query("
    SELECT ep.*, u.email, u.last_login,
           (SELECT COUNT(*) FROM idp_recommendations WHERE user_id=ep.user_id AND status='completed') as completed_idp,
           (SELECT COUNT(*) FROM attendance WHERE user_id=ep.user_id AND status='present') as present_days
    FROM employee_profiles ep
    JOIN users u ON u.id=ep.user_id
    WHERE ep.uid != 'ADMIN001'
    ORDER BY ep.knowledge_score DESC
");

$all = [];
while($r = $employees->fetch_assoc()) $all[] = $r;
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
        *{-webkit-font-smoothing:antialiased}body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}.nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1)}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.04)}
        .tier-diamond{background:linear-gradient(135deg,#a78bfa,#7c3aed)}
        .tier-gold{background:linear-gradient(135deg,#fbbf24,#d97706)}
        .tier-silver{background:linear-gradient(135deg,#94a3b8,#64748b)}
        .tier-bronze{background:linear-gradient(135deg,#d97706,#92400e)}
        .rank-1{background:linear-gradient(135deg,#FFD700,#FFA500)}
        .rank-2{background:linear-gradient(135deg,#C0C0C0,#A0A0A0)}
        .rank-3{background:linear-gradient(135deg,#CD7F32,#A0522D)}
        .podium-card{transition:all 0.3s ease}
        .podium-card:hover{transform:translateY(-4px)}
        @keyframes shimmer{0%{opacity:0.5}50%{opacity:1}100%{opacity:0.5}}
        .crown{animation:shimmer 2s ease-in-out infinite}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-apple-dark">Knowledge Leaderboard</h1>
        <p class="text-apple-gray text-sm mt-0.5">Company-wide skill rankings and competitive tiers</p>
    </div>

    <!-- Top 3 Podium -->
    <?php if(count($all) >= 3): ?>
    <div class="card p-8 mb-8" style="background:linear-gradient(135deg,#1a1a2e 0%,#16213e 50%,#0f3460 100%)">
        <div class="text-center mb-6">
            <span class="text-white text-sm font-semibold opacity-75">🏆 Top Performers This Cycle</span>
        </div>
        <div class="flex items-end justify-center gap-4">
            <!-- 2nd Place -->
            <div class="podium-card text-center flex-1 max-w-48">
                <div class="text-4xl mb-2">🥈</div>
                <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center text-white font-bold text-xl" style="background:linear-gradient(135deg,#C0C0C0,#808080)">
                    <?= strtoupper(substr($all[1]['first_name'],0,1).substr($all[1]['last_name'],0,1)) ?>
                </div>
                <div class="text-white font-semibold text-sm"><?= $all[1]['first_name'].' '.$all[1]['last_name'] ?></div>
                <div class="text-white opacity-60 text-xs mb-2"><?= $all[1]['department'] ?></div>
                <div class="text-white font-bold text-2xl"><?= $all[1]['knowledge_score'] ?></div>
                <div class="h-16 bg-white bg-opacity-10 rounded-t-xl mt-3"></div>
            </div>
            <!-- 1st Place -->
            <div class="podium-card text-center flex-1 max-w-48">
                <div class="crown text-5xl mb-2">👑</div>
                <div class="w-20 h-20 rounded-2xl mx-auto mb-3 flex items-center justify-center text-white font-bold text-2xl" style="background:linear-gradient(135deg,#FFD700,#FFA500);box-shadow:0 0 30px rgba(255,215,0,0.5)">
                    <?= strtoupper(substr($all[0]['first_name'],0,1).substr($all[0]['last_name'],0,1)) ?>
                </div>
                <div class="text-white font-bold text-base"><?= $all[0]['first_name'].' '.$all[0]['last_name'] ?></div>
                <div class="text-white opacity-60 text-xs mb-2"><?= $all[0]['department'] ?></div>
                <div class="text-white font-bold text-3xl"><?= $all[0]['knowledge_score'] ?></div>
                <div class="h-24 bg-white bg-opacity-10 rounded-t-xl mt-3"></div>
            </div>
            <!-- 3rd Place -->
            <div class="podium-card text-center flex-1 max-w-48">
                <div class="text-4xl mb-2">🥉</div>
                <div class="w-16 h-16 rounded-2xl mx-auto mb-3 flex items-center justify-center text-white font-bold text-xl" style="background:linear-gradient(135deg,#CD7F32,#8B4513)">
                    <?= strtoupper(substr($all[2]['first_name'],0,1).substr($all[2]['last_name'],0,1)) ?>
                </div>
                <div class="text-white font-semibold text-sm"><?= $all[2]['first_name'].' '.$all[2]['last_name'] ?></div>
                <div class="text-white opacity-60 text-xs mb-2"><?= $all[2]['department'] ?></div>
                <div class="text-white font-bold text-2xl"><?= $all[2]['knowledge_score'] ?></div>
                <div class="h-10 bg-white bg-opacity-10 rounded-t-xl mt-3"></div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Full Rankings -->
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
                        <th class="px-5 py-3 text-left">Target Role</th>
                        <th class="px-5 py-3 text-center">Completed IDP</th>
                        <th class="px-5 py-3 text-left">Knowledge Score</th>
                        <th class="px-5 py-3 text-left">Tier</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($all as $i=>$emp): $rank=$i+1; ?>
                    <tr class="border-b border-gray-50 hover:bg-gray-50 transition-colors <?= $rank<=3?'bg-amber-50 bg-opacity-30':'' ?>">
                        <td class="px-5 py-4">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold text-white
                                <?= $rank===1?'rank-1':($rank===2?'rank-2':($rank===3?'rank-3':'bg-gray-200 !text-apple-gray')) ?>">
                                <?= $rank<=3 ? ['🥇','🥈','🥉'][$rank-1] : '#'.$rank ?>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center text-white text-xs font-bold" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                                    <?= strtoupper(substr($emp['first_name'],0,1).substr($emp['last_name'],0,1)) ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-apple-dark"><?= $emp['first_name'].' '.$emp['last_name'] ?></div>
                                    <div class="text-xs text-apple-gray"><?= $emp['uid'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-apple-gray"><?= $emp['department'] ?></td>
                        <td class="px-5 py-4 text-apple-gray text-xs"><?= $emp['target_role'] ?: '—' ?></td>
                        <td class="px-5 py-4 text-center font-semibold text-apple-dark"><?= $emp['completed_idp'] ?></td>
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-2">
                                <div class="w-24 h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full" style="width:<?= $emp['knowledge_score'] ?>%;background:<?= $emp['knowledge_score']>=80?'#34C759':($emp['knowledge_score']>=60?'#007AFF':($emp['knowledge_score']>=40?'#FF9500':'#FF3B30')) ?>"></div>
                                </div>
                                <span class="text-sm font-bold text-apple-dark"><?= $emp['knowledge_score'] ?></span>
                            </div>
                        </td>
                        <td class="px-5 py-4">
                            <span class="text-xs px-2.5 py-1 rounded-lg font-semibold text-white tier-<?= strtolower($emp['tier']) ?>">
                                <?= ['Diamond'=>'💎','Gold'=>'🥇','Silver'=>'🥈','Bronze'=>'🥉'][$emp['tier']] ?? '' ?> <?= $emp['tier'] ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Tier Legend -->
    <div class="card p-6 mt-6">
        <h3 class="text-sm font-semibold text-apple-dark mb-4">Tier System</h3>
        <div class="grid grid-cols-4 gap-4">
            <?php $tiers=[['💎','Diamond','80–100','#a78bfa'],['🥇','Gold','60–79','#fbbf24'],['🥈','Silver','40–59','#94a3b8'],['🥉','Bronze','0–39','#d97706']];
            foreach($tiers as [$icon,$name,$range,$color]): ?>
            <div class="text-center p-4 bg-gray-50 rounded-2xl">
                <div class="text-3xl mb-2"><?= $icon ?></div>
                <div class="font-bold text-apple-dark"><?= $name ?></div>
                <div class="text-xs text-apple-gray mt-1">Score <?= $range ?></div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>
<script>function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}</script>
</body>
</html>

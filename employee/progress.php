<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('employee');
$userId = $_SESSION['user_id'];

$profile = $conn->query("SELECT ep.* FROM employee_profiles ep WHERE ep.user_id=$userId")->fetch_assoc();
$allRecs = $conn->query("SELECT * FROM idp_recommendations WHERE user_id=$userId ORDER BY priority DESC, status ASC");
$allArr = []; while($r=$allRecs->fetch_assoc()) $allArr[] = $r;

// Skill breakdown for radar chart
$currentSkills = array_filter(array_map('trim', explode(',', $profile['current_skills'] ?? '')));
$targetSkills  = array_filter(array_map('trim', explode(',', $profile['target_skills'] ?? '')));
$allSkills = array_unique(array_merge($currentSkills, $targetSkills));

// Simulated competency levels (could be from DB in production)
$currentLevels = []; $targetLevels = [];
foreach($allSkills as $skill) {
    $currentLevels[] = in_array($skill, $currentSkills) ? rand(55, 85) : rand(5, 30);
    $targetLevels[]  = rand(80, 100);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Progress – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#F5F5F7}
        .sidebar{background:rgba(255,255,255,0.9);backdrop-filter:blur(20px);border-right:1px solid rgba(0,0,0,0.06)}
        .nav-item{transition:all 0.15s ease;border-radius:12px}.nav-item:hover{background:#F5F5F7}
        .nav-item.active{background:rgba(0,122,255,0.1)}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(0,0,0,0.04)}
        .idp-card{border-left:3px solid #007AFF;transition:all 0.2s}.idp-card:hover{transform:translateX(3px)}
        .priority-high{border-left-color:#FF3B30}.priority-medium{border-left-color:#FF9500}.priority-low{border-left-color:#34C759}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar_emp.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-apple-dark">My Progress</h1>
        <p class="text-apple-gray text-sm mt-0.5">Track your skill development and IDP completion</p>
    </div>

    <!-- Summary Row -->
    <div class="grid grid-cols-4 gap-5 mb-8">
        <?php
        $status_counts=['approved'=>0,'in_progress'=>0,'completed'=>0,'pending'=>0];
        foreach($allArr as $r) $status_counts[$r['status']] = ($status_counts[$r['status']] ?? 0) + 1;
        $items=[['🎯','Total IDPs',count($allArr)],['🔵','Approved',$status_counts['approved']],['🟣','In Progress',$status_counts['in_progress']],['✅','Completed',$status_counts['completed']]];
        foreach($items as [$icon,$label,$val]): ?>
        <div class="card p-5 text-center">
            <div class="text-2xl mb-1"><?= $icon ?></div>
            <div class="text-2xl font-bold text-apple-dark"><?= $val ?></div>
            <div class="text-xs text-apple-gray mt-0.5"><?= $label ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="grid grid-cols-2 gap-6 mb-6">
        <!-- Radar Chart -->
        <div class="card p-6">
            <h3 class="text-base font-bold text-apple-dark mb-4">Skill Competency Analysis</h3>
            <div class="flex justify-center">
                <canvas id="radarChart" width="280" height="280"></canvas>
            </div>
            <div class="flex items-center justify-center gap-6 mt-4 text-xs text-apple-gray">
                <div class="flex items-center gap-1.5"><div class="w-3 h-1 rounded bg-apple-blue"></div> Current Level</div>
                <div class="flex items-center gap-1.5"><div class="w-3 h-1 rounded" style="background:#FF9500"></div> Target Level</div>
            </div>
        </div>

        <!-- Progress Bars -->
        <div class="card p-6">
            <h3 class="text-base font-bold text-apple-dark mb-5">Skill Gap Closure</h3>
            <div class="space-y-4">
                <?php foreach(array_slice($allSkills,0,8) as $i=>$skill): ?>
                <div>
                    <div class="flex justify-between text-xs mb-1">
                        <span class="font-medium text-apple-dark"><?= htmlspecialchars($skill) ?></span>
                        <span class="text-apple-gray"><?= $currentLevels[$i] ?>% / <?= $targetLevels[$i] ?>%</span>
                    </div>
                    <div class="relative h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="absolute h-full rounded-full opacity-30" style="width:<?= $targetLevels[$i] ?>%;background:#007AFF"></div>
                        <div class="h-full rounded-full bg-apple-blue" style="width:<?= $currentLevels[$i] ?>%;transition:width 1s ease"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- All IDP Recommendations -->
    <div class="card p-6">
        <h3 class="text-base font-bold text-apple-dark mb-5">All Development Recommendations</h3>
        <?php if(empty($allArr)): ?>
        <div class="text-center py-12 text-apple-gray">
            <div class="text-5xl mb-3">🚀</div>
            <div class="text-sm font-semibold">No recommendations assigned yet</div>
            <div class="text-xs mt-1">Your HR Admin will assign development paths based on your skill gap analysis.</div>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php foreach($allArr as $rec): ?>
            <div class="idp-card priority-<?= $rec['priority'] ?> p-4 bg-gray-50 rounded-xl">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1">
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs px-2.5 py-1 rounded-lg font-semibold
                                <?= ['training'=>'bg-blue-50 text-blue-700','mentorship'=>'bg-purple-50 text-purple-700','job_rotation'=>'bg-orange-50 text-orange-700','certification'=>'bg-green-50 text-green-700','workshop'=>'bg-pink-50 text-pink-700'][$rec['type']] ?? '' ?>">
                                <?= ucfirst(str_replace('_',' ',$rec['type'])) ?>
                            </span>
                            <span class="text-xs font-medium <?= $rec['priority']==='high'?'text-red-500':($rec['priority']==='medium'?'text-orange-500':'text-green-600') ?>">
                                <?= ucfirst($rec['priority']) ?> Priority
                            </span>
                            <?php if($rec['due_date']): ?>
                            <span class="text-xs text-apple-gray">Due <?= date('M j, Y',strtotime($rec['due_date'])) ?></span>
                            <?php endif; ?>
                        </div>
                        <h4 class="font-semibold text-sm text-apple-dark mb-1"><?= htmlspecialchars($rec['title']) ?></h4>
                        <p class="text-xs text-apple-gray leading-relaxed"><?= htmlspecialchars($rec['ai_suggestion']) ?></p>
                        <?php if($rec['skill_gap']): ?>
                        <div class="flex flex-wrap gap-1.5 mt-2">
                            <?php foreach(explode(',',$rec['skill_gap']) as $sk): ?>
                            <span class="text-xs bg-gray-100 text-apple-dark px-2 py-0.5 rounded-md"><?= trim($sk) ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                        <?php if($rec['progress_percent'] > 0 || $rec['status']==='in_progress'): ?>
                        <div class="flex items-center gap-2 mt-3">
                            <div class="flex-1 h-1.5 bg-gray-200 rounded-full overflow-hidden">
                                <div class="h-full bg-apple-blue rounded-full" style="width:<?= $rec['progress_percent'] ?>%"></div>
                            </div>
                            <span class="text-xs font-bold text-apple-dark"><?= $rec['progress_percent'] ?>%</span>
                        </div>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-semibold flex-shrink-0
                        <?= ['pending'=>'bg-yellow-50 text-yellow-700 border border-yellow-200','approved'=>'bg-blue-50 text-blue-700 border border-blue-200','in_progress'=>'bg-purple-50 text-purple-700 border border-purple-200','completed'=>'bg-green-50 text-green-700 border border-green-200','rejected'=>'bg-red-50 text-red-700 border border-red-200'][$rec['status']] ?? '' ?>">
                        <?= ucwords(str_replace('_',' ',$rec['status'])) ?>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</main>

<script>
// Radar Chart
const skills = <?= json_encode(array_values($allSkills)) ?>;
const current = <?= json_encode($currentLevels) ?>;
const target = <?= json_encode($targetLevels) ?>;

if(skills.length > 0) {
    new Chart(document.getElementById('radarChart'), {
        type: 'radar',
        data: {
            labels: skills,
            datasets: [
                { label:'Current Level', data:current, backgroundColor:'rgba(0,122,255,0.1)', borderColor:'#007AFF', borderWidth:2.5, pointBackgroundColor:'#007AFF', pointRadius:4 },
                { label:'Target Level', data:target, backgroundColor:'rgba(255,149,0,0.08)', borderColor:'#FF9500', borderWidth:2, pointBackgroundColor:'#FF9500', pointRadius:4, borderDash:[4,4] }
            ]
        },
        options: {
            responsive: true,
            scales: { r: { min:0, max:100, grid:{color:'#f0f0f0'}, ticks:{font:{size:10},backdropColor:'transparent'}, pointLabels:{font:{size:11}} } },
            plugins: { legend:{display:false} }
        }
    });
}
function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}
</script>
</body>
</html>

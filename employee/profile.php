<?php
require_once __DIR__ . '/../config/db.php';
requireLogin('employee');
$userId = $_SESSION['user_id'];
$profile = $conn->query("
    SELECT u.uid, u.email, u.last_login, ep.*
    FROM users u JOIN employee_profiles ep ON ep.user_id=u.id
    WHERE u.id=$userId
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile – SkillBridge AI</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}body{background:#f0f9ff; color:#0f172a;}
        .card{background:#fff;border-radius:20px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border:1px solid rgba(52,199,89,0.08)}
        .tier-diamond{background:linear-gradient(135deg,#a78bfa,#7c3aed);color:white}
        .tier-gold{background:linear-gradient(135deg,#fbbf24,#d97706);color:white}
        .tier-silver{background:linear-gradient(135deg,#94a3b8,#64748b);color:white}
        .tier-bronze{background:linear-gradient(135deg,#d97706,#92400e);color:white}
        .skill-tag{background:#f0fdf4;border:1px solid #bbf7d0;transition:all 0.2s}.skill-tag:hover{background:#dcfce7}
        .read-only-field{background:#fafafa;border:1.5px solid #f0fdf4;border-radius:12px;padding:10px 14px;font-size:0.875rem;color:#1D1D1F}
        .score-bar{background:#34C759}
        .score-bg{background:#dcfce7}
    </style>
</head>
<body class="font-sans flex">
<?php include __DIR__ . '/sidebar_emp.php'; ?>
<main class="ml-64 flex-1 p-8">
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-apple-dark">My Profile</h1>
        <p class="text-apple-gray text-sm mt-0.5">Your professional information as set by HR</p>
        <p class="text-xs text-orange-600 mt-1 bg-orange-50 inline-flex items-center gap-1.5 px-3 py-1 rounded-full">
            <svg width="12" height="12" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><line x1="12" y1="8" x2="12" y2="12" stroke="currentColor" stroke-width="2"/><line x1="12" y1="16" x2="12.01" y2="16" stroke="currentColor" stroke-width="2"/></svg>
            Read-only. Contact your HR Admin to update information.
        </p>
    </div>

    <div class="grid grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="card p-8 text-center col-span-1">
            <?php if (!empty($_SESSION['profile_image'])): ?>
                <img src="/1stProject/<?= htmlspecialchars($_SESSION['profile_image']) ?>" alt="Profile" class="w-28 h-28 rounded-3xl mx-auto mb-5 object-cover border shadow-sm">
            <?php else: ?>
                <div class="w-28 h-28 rounded-3xl mx-auto mb-5 flex items-center justify-center text-white text-4xl font-bold" style="background:linear-gradient(135deg,#007AFF,#5856D6)">
                    <?= strtoupper(substr($profile['first_name'],0,1).substr($profile['last_name'],0,1)) ?>
                </div>
            <?php endif; ?>
            <h2 class="text-2xl font-bold text-apple-dark"><?= htmlspecialchars($profile['first_name'].' '.$profile['last_name']) ?></h2>
            <p class="text-apple-gray text-sm mt-1"><?= htmlspecialchars($profile['designation'] ?: 'N/A') ?></p>
            <p class="text-apple-gray text-xs mt-0.5"><?= htmlspecialchars($profile['department'] ?: 'N/A') ?></p>

            <div class="mt-5 space-y-2 text-left">
                <div class="read-only-field"><?= htmlspecialchars($profile['email']) ?></div>
                <div class="read-only-field"><?= htmlspecialchars($profile['uid']) ?> <span class="text-apple-gray text-xs">(Employee ID)</span></div>
                <div class="read-only-field"><?= htmlspecialchars($profile['phone'] ?: 'Not provided') ?></div>
            </div>

            <div class="mt-4">
                <span class="text-sm px-4 py-2 rounded-xl font-semibold text-white tier-<?= strtolower($profile['tier']) ?>">
                    <?= ['Diamond'=>'💎','Gold'=>'🥇','Silver'=>'🥈','Bronze'=>'🥉'][$profile['tier']] ?> <?= $profile['tier'] ?> Tier
                </span>
            </div>

            <div class="mt-4 p-4 rounded-2xl" style="background:#f0fdf4">
                <div class="text-3xl font-bold" style="color:#34C759"><?= $profile['knowledge_score'] ?></div>
                <div class="text-xs text-apple-gray">Knowledge Score</div>
                <div class="h-2 rounded-full mt-2 overflow-hidden score-bg">
                    <div class="h-full rounded-full score-bar" style="width:<?= $profile['knowledge_score'] ?>%"></div>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="col-span-2 space-y-5">
            <!-- Professional Info -->
            <div class="card p-6">
                <h3 class="text-base font-bold text-apple-dark mb-5">Professional Information</h3>
                <div class="grid grid-cols-2 gap-4">
                    <?php $fields = [
                        ['Department','department'],['Designation','designation'],
                        ['Target Role','target_role'],['Date Joined','date_joined'],
                    ];
                    foreach($fields as [$label,$key]): ?>
                    <div>
                        <label class="block text-xs font-semibold text-apple-gray uppercase tracking-wide mb-1.5"><?= $label ?></label>
                        <div class="read-only-field"><?= htmlspecialchars($profile[$key] ?: 'Not set') ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Skills -->
            <div class="card p-6">
                <h3 class="text-base font-bold text-apple-dark mb-4">Skills & Development</h3>
                <div class="mb-4">
                    <div class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-2">Current Skills</div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach(explode(',',$profile['current_skills']??'') as $sk): $sk=trim($sk); if(!$sk) continue; ?>
                        <span class="skill-tag text-xs px-3 py-1.5 rounded-xl font-medium text-apple-dark"><?= htmlspecialchars($sk) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div>
                    <div class="text-xs font-semibold text-apple-gray uppercase tracking-wide mb-2">Skills to Develop</div>
                    <div class="flex flex-wrap gap-2">
                        <?php foreach(explode(',',$profile['target_skills']??'') as $sk): $sk=trim($sk); if(!$sk) continue; ?>
                        <span class="text-xs px-3 py-1.5 rounded-xl font-medium bg-blue-50 text-apple-blue border border-blue-100"><?= htmlspecialchars($sk) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Last Login -->
            <div class="card p-5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-50 rounded-xl flex items-center justify-center">
                        <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#34C759" stroke-width="2"/></svg>
                    </div>
                    <div>
                        <div class="text-sm font-semibold text-apple-dark">Account Security</div>
                        <div class="text-xs text-apple-gray">Last login: <?= $profile['last_login'] ? date('M j, Y H:i',strtotime($profile['last_login'])) : 'N/A' ?></div>
                    </div>
                    <div class="ml-auto">
                        <span class="text-xs text-green-700 bg-green-50 px-3 py-1 rounded-full font-semibold">2FA Active</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<script>function logout(){fetch('../api/auth.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'logout'})}).then(()=>window.location.href='../index.php')}</script>
</body>
</html>

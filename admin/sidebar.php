<?php
require_once __DIR__ . '/../config/theme.php';
// Shared sidebar for admin pages
$current = basename($_SERVER['PHP_SELF']);
$pages = [
    ['dashboard.php','Dashboard','<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/><polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>'],
    ['employees.php','Employees','<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2"/><path d="M16 3.13a4 4 0 0 1 0 7.75" stroke="currentColor" stroke-width="2"/>'],
    ['leaves.php','Leave Management','<rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/><line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>'],
    ['attendance.php','Attendance','<circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2"/><polyline points="12,6 12,12 16,14" stroke="currentColor" stroke-width="2"/>'],
    ['salary.php','Salary & Payroll','<line x1="12" y1="1" x2="12" y2="23" stroke="currentColor" stroke-width="2"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="currentColor" stroke-width="2"/>'],
    ['ai_recommendations.php','AI Recommendations','<circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="currentColor" stroke-width="2"/>'],
    ['leaderboard.php','Leaderboard','<polyline points="23,6 13.5,15.5 8.5,10.5 1,18" stroke="currentColor" stroke-width="2"/><polyline points="17,6 23,6 23,12" stroke="currentColor" stroke-width="2"/>'],
    ['profile.php','My Profile','<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>'],
];
?>
<aside class="sidebar w-64 min-h-screen fixed left-0 top-0 z-50 flex flex-col py-6 px-4">
    <div class="flex items-center gap-3 px-3 mb-8">
        <img src="/1stProject/assets/logo.png.jpeg" alt="SkillBridge Logo" class="w-10 h-10 rounded-xl object-cover shadow-md">
        <div><div class="text-sm font-bold text-apple-dark">SkillBridge AI</div><div class="text-xs text-apple-gray">Admin Portal</div></div>
    </div>
    <nav class="flex-1 space-y-1">
        <?php foreach($pages as $p): ?>
        <a href="<?= $p[0] ?>" class="nav-item <?= $current===$p[0]?'active':'' ?> flex items-center gap-3 px-3 py-2.5 text-sm font-medium <?= $current===$p[0]?'text-apple-blue':'text-apple-dark' ?>">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" class="flex-shrink-0 <?= $current===$p[0]?'text-apple-blue':'text-apple-gray' ?>"><?= $p[2] ?></svg>
            <?= $p[1] ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="border-t border-gray-100 pt-4 mt-4">
        <button onclick="toggleTheme()" class="w-full flex items-center gap-3 px-3 py-2.5 mb-2 rounded-xl text-sm font-medium nav-item text-apple-gray hover:text-apple-dark">
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" class="dark-hidden"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2"/></svg>
            <svg width="18" height="18" fill="none" viewBox="0 0 24 24" class="light-hidden"><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/><line x1="12" y1="1" x2="12" y2="3" stroke="currentColor" stroke-width="2"/><line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" stroke="currentColor" stroke-width="2"/><line x1="1" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2"/><line x1="21" y1="12" x2="23" y2="12" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22" stroke="currentColor" stroke-width="2"/></svg>
            Toggle Theme
        </button>
        <div class="flex items-center gap-3 px-3">
            <?php if (!empty($_SESSION['profile_image'])): ?>
                <img src="/1stProject/<?= htmlspecialchars($_SESSION['profile_image']) ?>" alt="Profile" class="w-8 h-8 rounded-xl object-cover border border-gray-200">
            <?php else: ?>
                <div class="w-8 h-8 rounded-xl bg-apple-blue flex items-center justify-center text-white text-xs font-bold">
                    <?= strtoupper(substr($_SESSION['name']??'A',0,2)) ?>
                </div>
            <?php endif; ?>
            <div class="flex-1 min-w-0">
                <div class="text-xs font-semibold text-apple-dark truncate"><?= htmlspecialchars($_SESSION['name']??'Admin') ?></div>
                <div class="text-xs text-apple-gray"><?= $_SESSION['uid']??'' ?> · Admin</div>
            </div>
            <button onclick="logout()" title="Logout" class="text-apple-gray hover:text-red-500 transition-colors">
                <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2"/><polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2"/><line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2"/></svg>
            </button>
        </div>
    </div>
</aside>

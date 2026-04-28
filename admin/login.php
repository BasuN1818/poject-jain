<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkillBridge AI Admin Portal – Secure administrator login.">
    <title>SkillBridge AI – Admin Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        * { -webkit-font-smoothing: antialiased; }
        body { background: #080810; min-height: 100vh; font-family: 'Inter', sans-serif; }

        .bg-orb { position: fixed; border-radius: 50%; filter: blur(100px); opacity: 0.2; pointer-events: none; }

        .glass-card {
            background: rgba(255,255,255,0.05);
            backdrop-filter: blur(40px);
            border: 1px solid rgba(255,255,255,0.10);
            box-shadow: 0 40px 100px rgba(0,0,0,0.5), 0 0 0 1px rgba(88,86,214,0.15);
            border-radius: 28px;
        }
        .input-field {
            background: rgba(255,255,255,0.06);
            border: 1.5px solid rgba(255,255,255,0.10);
            color: white;
            border-radius: 14px;
            padding: 13px 16px 13px 44px;
            font-size: 14px;
            width: 100%;
            transition: all 0.2s ease;
            outline: none;
        }
        .input-field::placeholder { color: rgba(255,255,255,0.3); }
        .input-field:focus {
            border-color: #5856D6;
            background: rgba(88,86,214,0.12);
            box-shadow: 0 0 0 3px rgba(88,86,214,0.2);
        }
        .btn-admin {
            background: linear-gradient(135deg, #5856D6 0%, #007AFF 100%);
            box-shadow: 0 4px 24px rgba(88,86,214,0.5);
            border-radius: 14px; width: 100%; padding: 14px;
            color: white; font-weight: 600; font-size: 14px;
            letter-spacing: 0.02em; border: none; cursor: pointer;
            transition: all 0.2s ease;
        }
        .btn-admin:hover { transform: translateY(-2px); box-shadow: 0 8px 32px rgba(88,86,214,0.6); }
        .btn-admin:active { transform: translateY(0); }

        .badge {
            background: linear-gradient(135deg, #5856D6, #007AFF);
            box-shadow: 0 8px 24px rgba(88,86,214,0.6);
            width: 64px; height: 64px; border-radius: 18px;
            display: flex; align-items: center; justify-content: center;
        }
        .label-text { color: rgba(255,255,255,0.5); font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 6px; display: block; }
        .icon-wrap { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: rgba(255,255,255,0.3); }

        @keyframes fadeInUp { from { opacity:0; transform:translateY(24px); } to { opacity:1; transform:translateY(0); } }
        .animate-in { animation: fadeInUp 0.6s cubic-bezier(0.22,1,0.36,1) forwards; }

        .alert-box { border-radius: 12px; padding: 12px 16px; font-size: 13px; font-weight: 500; margin-bottom: 16px; display: flex; align-items: center; gap-8px; }
        .alert-error { background: rgba(255,59,48,0.12); border: 1px solid rgba(255,59,48,0.3); color: #ff6b6b; }
        .alert-success { background: rgba(52,199,89,0.12); border: 1px solid rgba(52,199,89,0.3); color: #4ade80; }

        .back-link { color: rgba(255,255,255,0.3); font-size: 12px; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; transition: color 0.2s; }
        .back-link:hover { color: rgba(255,255,255,0.7); }
    </style>
</head>
<body class="flex flex-col items-center justify-center min-h-screen p-4">

    <!-- BG orbs -->
    <div class="bg-orb w-96 h-96" style="top:-10%;left:-10%;background:radial-gradient(circle,#5856D6,transparent);"></div>
    <div class="bg-orb w-80 h-80" style="bottom:-5%;right:-5%;background:radial-gradient(circle,#007AFF,transparent);"></div>

    <div class="w-full max-w-sm animate-in">

        <!-- Back link -->
        <a href="../index.php" class="back-link mb-8 block">
            <svg width="14" height="14" fill="none" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Back to Portal
        </a>

        <!-- Brand -->
        <div class="text-center mb-8">
            <div class="badge mx-auto mb-4">
                <svg width="28" height="28" fill="none" viewBox="0 0 24 24">
                    <path d="M12 2L2 7l10 5 10-5-10-5z" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 17l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    <path d="M2 12l10 5 10-5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white tracking-tight">Admin Portal</h1>
            <p style="color:rgba(255,255,255,0.35);" class="text-sm mt-1">SkillBridge AI Command Center</p>
        </div>

        <!-- Card -->
        <div class="glass-card p-8">
            <h2 class="text-lg font-semibold text-white mb-1">Welcome back, Admin</h2>
            <p style="color:rgba(255,255,255,0.4);" class="text-xs mb-6">Sign in with your administrator credentials</p>

            <!-- Alert -->
            <div id="alert-box" class="hidden"></div>

            <form id="login-form" novalidate>
                <!-- Employee ID -->
                <div class="mb-4">
                    <label class="label-text">Admin ID</label>
                    <div class="relative">
                        <span class="icon-wrap">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <input id="uid" type="text" placeholder="e.g. ADMIN001" class="input-field" autocomplete="username" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-6">
                    <label class="label-text">Password</label>
                    <div class="relative">
                        <span class="icon-wrap">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M12 17v-2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="12" r="1" fill="currentColor"/><rect x="3" y="8" width="18" height="13" rx="2" stroke="currentColor" stroke-width="2"/><path d="M8 8V6a4 4 0 0 1 8 0v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        </span>
                        <input id="password" type="password" placeholder="Enter your password" class="input-field" style="padding-right:48px;" autocomplete="current-password" required>
                        <button type="button" id="toggle-pw" style="position:absolute;right:14px;top:50%;transform:translateY(-50%);color:rgba(255,255,255,0.3);background:none;border:none;cursor:pointer;transition:color 0.2s;" onmouseover="this.style.color='rgba(255,255,255,0.7)'" onmouseout="this.style.color='rgba(255,255,255,0.3)'">
                            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                        </button>
                    </div>
                </div>

                <button id="login-btn" type="submit" class="btn-admin">
                    <span id="btn-text">Access Command Center</span>
                    <span id="btn-spinner" class="hidden inline-flex items-center justify-center gap-2">
                        <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" stroke-dasharray="30 50"/></svg>
                        Verifying…
                    </span>
                </button>
            </form>

            <p style="color:rgba(255,255,255,0.25);" class="text-xs text-center mt-5">
                A 2FA verification code will be sent to your registered email
            </p>
        </div>

        <p style="color:rgba(255,255,255,0.15);" class="text-center text-xs mt-6">&copy; 2026 SkillBridge AI</p>
    </div>

    <script>
        // Toggle password visibility
        document.getElementById('toggle-pw').addEventListener('click', () => {
            const pw = document.getElementById('password');
            pw.type = pw.type === 'password' ? 'text' : 'password';
        });

        function showAlert(msg, type = 'error') {
            const box = document.getElementById('alert-box');
            box.className = type === 'error'
                ? 'alert-box alert-error flex items-center gap-2'
                : 'alert-box alert-success flex items-center gap-2';
            box.innerHTML = `<span>${type === 'error' ? '⚠️' : '✅'}</span><span>${msg}</span>`;
            box.classList.remove('hidden');
        }

        document.getElementById('login-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const uid      = document.getElementById('uid').value.trim().toUpperCase();
            const password = document.getElementById('password').value;

            if (!uid || !password) { showAlert('Please fill in all fields.'); return; }

            document.getElementById('btn-text').classList.add('hidden');
            document.getElementById('btn-spinner').classList.remove('hidden');
            document.getElementById('login-btn').disabled = true;

            try {
                const res  = await fetch('../api/auth.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'login', uid, password, login_type: 'admin' })
                });
                const data = await res.json();

                if (data.success) {
                    showAlert('Code sent! Redirecting to verification…', 'success');
                    setTimeout(() => {
                        window.location.href = `../verify_2fa.php?uid=${encodeURIComponent(uid)}&type=admin`;
                    }, 1200);
                } else {
                    showAlert(data.message || 'Invalid credentials. Please try again.');
                    document.getElementById('btn-text').classList.remove('hidden');
                    document.getElementById('btn-spinner').classList.add('hidden');
                    document.getElementById('login-btn').disabled = false;
                }
            } catch (err) {
                showAlert('Connection error. Please check the server.');
                document.getElementById('btn-text').classList.remove('hidden');
                document.getElementById('btn-spinner').classList.add('hidden');
                document.getElementById('login-btn').disabled = false;
            }
        });
    </script>
</body>
</html>

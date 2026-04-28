<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SkillBridge AI – Login</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; min-height: 100vh; display: flex; -webkit-font-smoothing: antialiased; }

        /* ── LEFT HERO PANEL ── */
        .hero {
            width: 52%;
            background: #080c14;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            padding: 48px 56px;
        }
        .hero::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 10%, rgba(0,122,255,0.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 80%, rgba(88,86,214,0.15) 0%, transparent 55%),
                radial-gradient(ellipse 40% 40% at 60% 30%, rgba(0,199,190,0.08) 0%, transparent 50%);
            pointer-events: none;
        }
        /* Animated grid */
        .hero::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.025) 1px, transparent 1px);
            background-size: 56px 56px;
            pointer-events: none;
        }
        .hero-content { position: relative; z-index: 1; }

        /* Logo */
        .logo { display: flex; align-items: center; gap: 12px; }
        .logo-icon {
            width: 56px; height: 56px; border-radius: 12px;
            object-fit: cover;
            box-shadow: 0 8px 24px rgba(0,0,0,0.2);
            display: flex; align-items: center; justify-content: center;
        }
        .logo-text { font-size: 18px; font-weight: 700; color: white; letter-spacing: -0.02em; }
        .logo-sub { font-size: 11px; color: rgba(255,255,255,0.4); font-weight: 500; margin-top: 1px; }

        /* Hero headline */
        .hero-headline {
            margin-top: 80px;
        }
        .hero-tag {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(0,122,255,0.12);
            border: 1px solid rgba(0,122,255,0.25);
            border-radius: 100px; padding: 6px 14px;
            font-size: 11px; font-weight: 600; color: #60a5fa;
            letter-spacing: 0.04em; text-transform: uppercase;
            margin-bottom: 24px;
        }
        .hero-tag-dot { width: 6px; height: 6px; border-radius: 50%; background: #007AFF; animation: blink 2s infinite; }
        @keyframes blink { 0%,100%{opacity:1} 50%{opacity:0.3} }
        .hero-title {
            font-size: 42px; font-weight: 800; line-height: 1.1;
            letter-spacing: -0.03em; color: white;
        }
        .hero-title span {
            background: linear-gradient(135deg, #60a5fa, #a78bfa);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        }
        .hero-desc {
            margin-top: 20px; font-size: 15px; color: rgba(255,255,255,0.45);
            line-height: 1.7; max-width: 380px;
        }

        /* Feature pills */
        .features { display: flex; flex-direction: column; gap: 14px; margin-top: 48px; }
        .feature {
            display: flex; align-items: center; gap: 14px;
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.06);
            border-radius: 14px; padding: 14px 18px;
            transition: background 0.2s;
        }
        .feature:hover { background: rgba(255,255,255,0.07); }
        .feature-icon {
            width: 36px; height: 36px; border-radius: 10px; flex-shrink: 0;
            display: flex; align-items: center; justify-content: center;
        }
        .feature-title { font-size: 13px; font-weight: 600; color: white; }
        .feature-desc { font-size: 11px; color: rgba(255,255,255,0.4); margin-top: 2px; }

        /* Stats row */
        .stats { display: flex; gap: 32px; }
        .stat-num { font-size: 26px; font-weight: 800; color: white; letter-spacing: -0.02em; }
        .stat-label { font-size: 11px; color: rgba(255,255,255,0.35); margin-top: 2px; font-weight: 500; }

        /* ── RIGHT FORM PANEL ── */
        .form-panel {
            flex: 1;
            background: #f9fafb;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
        }
        .form-box { width: 100%; max-width: 400px; }

        .form-title { font-size: 26px; font-weight: 800; color: #0f172a; letter-spacing: -0.02em; }
        .form-sub { font-size: 14px; color: #64748b; margin-top: 6px; }

        /* Divider */
        .divider { display: flex; align-items: center; gap: 12px; margin: 28px 0; }
        .divider-line { flex: 1; height: 1px; background: #e2e8f0; }
        .divider-text { font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.06em; }

        /* Form */
        .field { margin-bottom: 18px; }
        .field-label { display: block; font-size: 12px; font-weight: 600; color: #374151; letter-spacing: 0.04em; text-transform: uppercase; margin-bottom: 8px; }
        .field-wrap { position: relative; }
        .field-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
        .field-input {
            width: 100%; padding: 13px 14px 13px 44px;
            background: white; border: 1.5px solid #e2e8f0;
            border-radius: 12px; font-size: 14px; font-family: 'Inter', sans-serif;
            color: #0f172a; outline: none;
            transition: all 0.2s ease;
        }
        .field-input::placeholder { color: #cbd5e1; }
        .field-input:focus { border-color: #007AFF; box-shadow: 0 0 0 4px rgba(0,122,255,0.1); }
        .pw-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer; color: #94a3b8;
            padding: 2px; transition: color 0.2s;
        }
        .pw-toggle:hover { color: #475569; }

        /* Alert */
        .alert { border-radius: 12px; padding: 12px 16px; font-size: 13px; font-weight: 500; display: flex; align-items: center; gap: 10px; margin-bottom: 20px; }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #dc2626; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #16a34a; }

        /* Submit */
        .btn-submit {
            width: 100%; padding: 14px;
            background: linear-gradient(135deg, #007AFF 0%, #0055D4 100%);
            border: none; border-radius: 12px; cursor: pointer;
            font-family: 'Inter', sans-serif; font-size: 15px; font-weight: 600;
            color: white; letter-spacing: 0.01em;
            box-shadow: 0 4px 20px rgba(0,122,255,0.35);
            transition: all 0.2s ease;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover:not(:disabled) { transform: translateY(-2px); box-shadow: 0 8px 28px rgba(0,122,255,0.45); }
        .btn-submit:active { transform: translateY(0); }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

        /* 2FA note */
        .twofa-note {
            display: flex; align-items: center; justify-content: center; gap: 8px;
            margin-top: 20px; font-size: 12px; color: #94a3b8;
        }
        .footer-links { display: flex; justify-content: center; gap: 20px; margin-top: 32px; }
        .footer-links a { font-size: 12px; color: #94a3b8; text-decoration: none; transition: color 0.2s; }
        .footer-links a:hover { color: #475569; }

        /* Spin */
        @keyframes spin { to { transform: rotate(360deg); } }
        .spin { animation: spin 0.8s linear infinite; }
        @keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
        .fade-up { animation: fadeUp 0.6s cubic-bezier(0.22,1,0.36,1) both; }

        @media (max-width: 900px) {
            .hero { display: none; }
            .form-panel { background: white; }
        }
    </style>
</head>
<body>

<!-- LEFT HERO -->
<div class="hero">
    <div class="hero-content">
        <!-- Logo -->
        <div class="logo">
            <img src="assets/logo.png.jpeg" alt="SkillBridge Logo" class="logo-icon">
            <div>
                <div class="logo-text">SkillBridge AI</div>
                <div class="logo-sub">Corporate Platform</div>
            </div>
        </div>

        <!-- Headline -->
        <div class="hero-headline">
            <div class="hero-tag"><div class="hero-tag-dot"></div>AI-Powered Workforce</div>
            <h1 class="hero-title">Grow smarter,<br>perform <span>better.</span></h1>
            <p class="hero-desc">A unified platform for employee development, payroll, attendance, and AI-driven career growth — all in one place.</p>
        </div>

        <!-- Features -->
        <div class="features">
            <div class="feature">
                <div class="feature-icon" style="background:rgba(0,122,255,0.15);">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3" stroke="#60a5fa" stroke-width="2"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83" stroke="#60a5fa" stroke-width="2"/></svg>
                </div>
                <div><div class="feature-title">AI Skill Recommendations</div><div class="feature-desc">Personalized learning paths powered by machine learning</div></div>
            </div>
            <div class="feature">
                <div class="feature-icon" style="background:rgba(88,86,214,0.15);">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><line x1="12" y1="1" x2="12" y2="23" stroke="#a78bfa" stroke-width="2"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" stroke="#a78bfa" stroke-width="2"/></svg>
                </div>
                <div><div class="feature-title">Payroll & Salary Management</div><div class="feature-desc">Automated salary processing with full transparency</div></div>
            </div>
            <div class="feature">
                <div class="feature-icon" style="background:rgba(52,199,89,0.15);">
                    <svg width="18" height="18" fill="none" viewBox="0 0 24 24"><polyline points="23,6 13.5,15.5 8.5,10.5 1,18" stroke="#4ade80" stroke-width="2"/><polyline points="17,6 23,6 23,12" stroke="#4ade80" stroke-width="2"/></svg>
                </div>
                <div><div class="feature-title">Real-time Leaderboard</div><div class="feature-desc">Track performance and celebrate top achievers</div></div>
            </div>
        </div>
    </div>

    <!-- Stats -->
    <div class="hero-content">
        <div style="height:1px;background:rgba(255,255,255,0.07);margin-bottom:28px;"></div>
        <div class="stats">
            <div><div class="stat-num">500+</div><div class="stat-label">Employees Managed</div></div>
            <div><div class="stat-num">98%</div><div class="stat-label">Satisfaction Rate</div></div>
            <div><div class="stat-num">2FA</div><div class="stat-label">Secure Access</div></div>
        </div>
    </div>
</div>

<!-- RIGHT FORM PANEL -->
<div class="form-panel">
    <div class="form-box fade-up">

        <div style="margin-bottom:36px;">
            <h2 class="form-title">Welcome back</h2>
            <p class="form-sub">Sign in to your SkillBridge AI account</p>
        </div>

        <!-- Alert -->
        <div id="alert-box" style="display:none;"></div>

        <form id="login-form" novalidate>

            <!-- Employee ID -->
            <div class="field">
                <label class="field-label" for="uid">Employee ID</label>
                <div class="field-wrap">
                    <span class="field-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/></svg>
                    </span>
                    <input id="uid" type="text" class="field-input" placeholder="e.g. EMP001 or ADMIN001" autocomplete="username" required>
                </div>
            </div>

            <!-- Password -->
            <div class="field">
                <label class="field-label" for="password">Password</label>
                <div class="field-wrap">
                    <span class="field-icon">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" stroke="currentColor" stroke-width="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                    </span>
                    <input id="password" type="password" class="field-input" style="padding-right:44px;" placeholder="Enter your password" autocomplete="current-password" required>
                    <button type="button" class="pw-toggle" id="toggle-pw">
                        <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2"/></svg>
                    </button>
                </div>
            </div>

            <!-- Submit -->
            <button id="login-btn" type="submit" class="btn-submit" style="margin-top:8px;">
                <span id="btn-text">Sign In</span>
                <span id="btn-spinner" style="display:none;align-items:center;gap:8px;">
                    <svg class="spin" width="16" height="16" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" stroke-dasharray="30 50"/></svg>
                    Verifying…
                </span>
            </button>
        </form>

        <!-- 2FA note -->
        <div class="twofa-note">
            <svg width="13" height="13" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
            Protected by Two-Factor Authentication
        </div>

        <div class="footer-links">
            <a href="#">Privacy Policy</a>
            <a href="#">Help & Support</a>
            <a href="#">© 2026 SkillBridge AI</a>
        </div>
    </div>
</div>

<script>
    document.getElementById('toggle-pw').addEventListener('click', function() {
        const pw = document.getElementById('password');
        pw.type = pw.type === 'password' ? 'text' : 'password';
    });

    function showAlert(msg, type) {
        const box = document.getElementById('alert-box');
        box.className = 'alert alert-' + (type || 'error');
        box.innerHTML = '<span>' + (type === 'success' ? '✅' : '⚠️') + '</span><span>' + msg + '</span>';
        box.style.display = 'flex';
    }

    function setLoading(on) {
        document.getElementById('btn-text').style.display    = on ? 'none'  : 'inline';
        document.getElementById('btn-spinner').style.display = on ? 'flex'  : 'none';
        document.getElementById('login-btn').disabled        = on;
    }

    document.getElementById('login-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        const uid      = document.getElementById('uid').value.trim().toUpperCase();
        const password = document.getElementById('password').value;
        if (!uid || !password) { showAlert('Please fill in all fields.'); return; }
        setLoading(true);
        try {
            const res  = await fetch('api/auth.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'login', uid, password })
            });
            const data = await res.json();
            if (data.success) {
                showAlert('Code sent! Redirecting…', 'success');
                setTimeout(() => { window.location.href = 'verify_2fa.php?uid=' + encodeURIComponent(uid); }, 1200);
            } else {
                showAlert(data.message || 'Invalid credentials. Please try again.');
                setLoading(false);
            }
        } catch(err) {
            showAlert('Connection error. Please check the server.');
            setLoading(false);
        }
    });
</script>
</body>
</html>

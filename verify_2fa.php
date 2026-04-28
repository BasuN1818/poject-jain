<?php
require_once __DIR__ . '/config/db.php';
initSecureSession();

// If already verified, redirect
if (isset($_SESSION['user_id']) && isset($_SESSION['verified'])) {
    header('Location: ' . ($_SESSION['role'] === 'admin' ? 'admin/dashboard.php' : 'employee/dashboard.php'));
    exit;
}
$uid = isset($_GET['uid']) ? htmlspecialchars($_GET['uid']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SkillBridge AI – Two-Factor Authentication Verification">
    <title>SkillBridge AI – Verify Identity</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{fontFamily:{sans:['Inter','system-ui','sans-serif']},colors:{apple:{blue:'#007AFF',gray:'#86868B',dark:'#1D1D1F',light:'#F5F5F7'}}}}}</script>
    <style>
        *{-webkit-font-smoothing:antialiased}
        body{background:linear-gradient(135deg,#f5f5f7 0%,#e8e8ed 100%);min-height:100vh}
        .glass-card{background:rgba(255,255,255,0.85);backdrop-filter:blur(40px);-webkit-backdrop-filter:blur(40px);border:1px solid rgba(255,255,255,0.9);box-shadow:0 32px 80px rgba(0,0,0,0.10)}
        .otp-input{border:1.5px solid #e5e5ea;background:#fafafa;transition:all 0.2s ease;text-align:center;font-size:1.5rem;font-weight:700;letter-spacing:0.1em}
        .otp-input:focus{border-color:#007AFF;box-shadow:0 0 0 3px rgba(0,122,255,0.12);background:#fff;outline:none}
        .btn-primary{background:linear-gradient(135deg,#007AFF 0%,#0055D4 100%);transition:all 0.2s ease;box-shadow:0 4px 16px rgba(0,122,255,0.3)}
        .btn-primary:hover{transform:translateY(-1px);box-shadow:0 6px 24px rgba(0,122,255,0.4)}
        .logo-badge{background:linear-gradient(135deg,#007AFF 0%,#5856D6 100%);box-shadow:0 8px 24px rgba(0,122,255,0.4)}
        @keyframes fadeInUp{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:translateY(0)}}
        .animate-in{animation:fadeInUp 0.6s cubic-bezier(0.22,1,0.36,1) forwards}
        .digit-box{width:52px;height:64px;border-radius:14px}
        #countdown-bar{transition:width 1s linear}
    </style>
</head>
<body class="font-sans flex items-center justify-center min-h-screen p-4">

    <div class="fixed top-1/4 -left-32 w-96 h-96 rounded-full opacity-20 blur-3xl" style="background:radial-gradient(circle,#007AFF,transparent)"></div>

    <div class="w-full max-w-md animate-in">
        <div class="text-center mb-8">
            <img src="assets/logo.png.jpeg" alt="SkillBridge Logo" class="w-20 h-20 mx-auto mb-4 object-cover shadow-lg rounded-2xl">
            <h1 class="text-2xl font-bold text-apple-dark tracking-tight">Identity Verification</h1>
            <p class="text-apple-gray text-sm mt-1">Two-Factor Authentication</p>
        </div>

        <div class="glass-card rounded-3xl p-8">
            <!-- Shield Icon -->
            <div class="flex justify-center mb-5">
                <div class="w-16 h-16 bg-blue-50 rounded-2xl flex items-center justify-center">
                    <svg width="30" height="30" fill="none" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" stroke="#007AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/><path d="M9 12l2 2 4-4" stroke="#007AFF" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                </div>
            </div>

            <h2 class="text-xl font-semibold text-apple-dark text-center mb-1">Enter Verification Code</h2>
            <p class="text-apple-gray text-sm text-center mb-6">
                A 6-digit code was sent to your registered email address for <strong class="text-apple-dark"><?= $uid ?></strong>
            </p>

            <!-- Alert -->
            <div id="alert-box" class="hidden mb-4 px-4 py-3 rounded-xl text-sm font-medium"></div>

            <!-- OTP Inputs -->
            <div class="flex gap-2 justify-center mb-6" id="otp-container">
                <?php for($i=0;$i<6;$i++): ?>
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]"
                    class="otp-input digit-box text-apple-dark"
                    data-index="<?= $i ?>" id="otp-<?= $i ?>" autocomplete="one-time-code">
                <?php endfor; ?>
            </div>

            <!-- Progress Bar -->
            <div class="mb-5">
                <div class="flex justify-between text-xs text-apple-gray mb-1.5">
                    <span>Code expires in</span>
                    <span id="countdown-text" class="text-apple-dark font-semibold">5:00</span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div id="countdown-bar" class="h-full bg-apple-blue rounded-full" style="width:100%"></div>
                </div>
            </div>

            <!-- Verify Button -->
            <button id="verify-btn" onclick="verifyOTP()"
                class="btn-primary w-full py-3 rounded-xl text-white text-sm font-semibold tracking-wide mb-4">
                <span id="vbtn-text">Verify & Continue</span>
                <span id="vbtn-spinner" class="hidden inline-flex items-center gap-2 justify-center">
                    <svg class="animate-spin w-4 h-4" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="white" stroke-width="3" stroke-dasharray="30 50"/></svg>Verifying…
                </span>
            </button>

            <!-- Resend -->
            <p class="text-center text-xs text-apple-gray">
                Didn't receive the code?
                <button id="resend-btn" onclick="resendOTP()" class="text-apple-blue font-semibold hover:underline ml-1" disabled>
                    Resend Code <span id="resend-timer">(60s)</span>
                </button>
            </p>
        </div>

        <div class="text-center mt-5">
            <a href="index.php" class="text-xs text-apple-gray hover:text-apple-dark transition-colors">← Back to Login</a>
        </div>
    </div>

<script>
    const UID = '<?= $uid ?>';
    let otpTimer, countdownTimer;
    let totalSecs = 300; // 5 min
    let resendSecs = 60;

    // OTP Input auto-focus navigation
    const inputs = document.querySelectorAll('.otp-input');
    inputs.forEach((inp, idx) => {
        inp.addEventListener('input', e => {
            const val = e.target.value.replace(/\D/g,'');
            e.target.value = val;
            if (val && idx < 5) inputs[idx+1].focus();
        });
        inp.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !e.target.value && idx > 0) inputs[idx-1].focus();
            if (e.key === 'ArrowLeft' && idx > 0) inputs[idx-1].focus();
            if (e.key === 'ArrowRight' && idx < 5) inputs[idx+1].focus();
        });
        inp.addEventListener('paste', e => {
            e.preventDefault();
            const txt = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g,'').substring(0,6);
            txt.split('').forEach((ch, i) => { if(inputs[i]) inputs[i].value = ch; });
            if(inputs[Math.min(txt.length, 5)]) inputs[Math.min(txt.length,5)].focus();
        });
    });

    // Countdown timer
    function startCountdown() {
        clearInterval(countdownTimer);
        totalSecs = 300;
        countdownTimer = setInterval(() => {
            totalSecs--;
            const m = Math.floor(totalSecs/60), s = totalSecs%60;
            document.getElementById('countdown-text').textContent = `${m}:${s.toString().padStart(2,'0')}`;
            document.getElementById('countdown-bar').style.width = (totalSecs/300*100)+'%';
            if(totalSecs <= 0) { clearInterval(countdownTimer); showAlert('Code expired. Please request a new one.','error'); }
        }, 1000);
    }

    // Resend timer
    function startResendTimer() {
        resendSecs = 60;
        document.getElementById('resend-btn').disabled = true;
        const t = setInterval(() => {
            resendSecs--;
            document.getElementById('resend-timer').textContent = `(${resendSecs}s)`;
            if(resendSecs <= 0) {
                clearInterval(t);
                document.getElementById('resend-btn').disabled = false;
                document.getElementById('resend-timer').textContent = '';
            }
        }, 1000);
    }

    function showAlert(msg, type='error') {
        const box = document.getElementById('alert-box');
        box.className = `mb-4 px-4 py-3 rounded-xl text-sm font-medium flex items-center gap-2 ${
            type==='error'?'bg-red-50 text-red-700 border border-red-200':
            type==='success'?'bg-green-50 text-green-700 border border-green-200':
            'bg-blue-50 text-blue-700 border border-blue-200'}`;
        box.innerHTML = `<span>${type==='error'?'⚠️':type==='success'?'✅':'ℹ️'}</span><span>${msg}</span>`;
        box.classList.remove('hidden');
    }

    async function verifyOTP() {
        const otp = Array.from(inputs).map(i=>i.value).join('');
        if(otp.length < 6) { showAlert('Please enter the complete 6-digit code.'); return; }

        document.getElementById('vbtn-text').classList.add('hidden');
        document.getElementById('vbtn-spinner').classList.remove('hidden');
        document.getElementById('verify-btn').disabled = true;

        try {
            const res = await fetch('api/auth.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({action:'verify_2fa', uid:UID, otp})
            });
            const data = await res.json();
            if(data.success) {
                showAlert('Verified! Redirecting…','success');
                setTimeout(() => { window.location.href = data.redirect; }, 1000);
            } else {
                showAlert(data.message || 'Invalid or expired code.');
                document.getElementById('vbtn-text').classList.remove('hidden');
                document.getElementById('vbtn-spinner').classList.add('hidden');
                document.getElementById('verify-btn').disabled = false;
            }
        } catch(err) {
            showAlert('Connection error. Please try again.');
            document.getElementById('vbtn-text').classList.remove('hidden');
            document.getElementById('vbtn-spinner').classList.add('hidden');
            document.getElementById('verify-btn').disabled = false;
        }
    }

    async function resendOTP() {
        try {
            const res = await fetch('api/auth.php', {
                method:'POST', headers:{'Content-Type':'application/json'},
                body: JSON.stringify({action:'resend_otp', uid:UID})
            });
            const data = await res.json();
            if(data.success) {
                showAlert('New code sent to your email!','success');
                startCountdown();
                startResendTimer();
                inputs.forEach(i=>i.value='');
                inputs[0].focus();
            } else {
                showAlert(data.message || 'Failed to resend code.');
            }
        } catch(err) { showAlert('Connection error.'); }
    }

    // Init
    startCountdown();
    startResendTimer();
    inputs[0].focus();
</script>
</body>
</html>

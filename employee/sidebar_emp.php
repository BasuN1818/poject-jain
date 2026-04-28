<?php
require_once __DIR__ . '/../config/theme.php';
// Shared sidebar for employee pages
require_once __DIR__ . '/../config/db.php';
$current = basename($_SERVER['PHP_SELF']);
$pages = [
    ['dashboard.php', 'My Dashboard',   '<path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z" stroke="currentColor" stroke-width="2"/><polyline points="9,22 9,12 15,12 15,22" stroke="currentColor" stroke-width="2"/>'],
    ['profile.php',   'My Profile',     '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/><circle cx="12" cy="7" r="4" stroke="currentColor" stroke-width="2"/>'],
    ['progress.php',  'My Progress',    '<polyline points="23,6 13.5,15.5 8.5,10.5 1,18" stroke="currentColor" stroke-width="2"/><polyline points="17,6 23,6 23,12" stroke="currentColor" stroke-width="2"/>'],
    ['leave.php',     'Apply for Leave','<rect x="3" y="4" width="18" height="18" rx="2" stroke="currentColor" stroke-width="2"/><line x1="16" y1="2" x2="16" y2="6" stroke="currentColor" stroke-width="2"/><line x1="8" y1="2" x2="8" y2="6" stroke="currentColor" stroke-width="2"/><line x1="3" y1="10" x2="21" y2="10" stroke="currentColor" stroke-width="2"/>'],
    ['leaderboard.php','Leaderboard',   '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2" stroke="currentColor" stroke-width="2"/><path d="M23 21v-2a4 4 0 0 0-3-3.87" stroke="currentColor" stroke-width="2"/><circle cx="9" cy="7" r="4" stroke="currentColor" stroke-width="2"/>'],
    ['history.php',   'My History',     '<path d="M12 8v4l3 3m6-3a9 9 0 1 1-18 0 9 9 0 0 1 18 0z" stroke="currentColor" stroke-width="2"/>'],
];
$profile_data = $conn->query("SELECT ep.* FROM employee_profiles ep WHERE ep.user_id=".$_SESSION['user_id'])->fetch_assoc();
?>
<style>
    .emp-sidebar {
        width: 256px; min-height: 100vh; position: fixed; left: 0; top: 0; z-index: 50;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        border-right: 1px solid rgba(52,199,89,0.12);
        display: flex; flex-direction: column;
        padding: 24px 16px;
        box-shadow: 4px 0 24px rgba(52,199,89,0.06);
    }
    .emp-logo-badge {
        background: linear-gradient(135deg, #34C759, #00C7BE);
        box-shadow: 0 6px 20px rgba(52,199,89,0.4);
    }
    .emp-nav-item {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 12px; border-radius: 12px;
        font-size: 14px; font-weight: 500;
        color: #1D1D1F; text-decoration: none;
        transition: all 0.15s ease;
        margin-bottom: 2px;
        background: transparent;
    }
    .emp-nav-item:hover { background: #f0fdf4; color: #16a34a; }
    .emp-nav-item svg { color: #86868B; flex-shrink: 0; transition: color 0.15s; }
    .emp-nav-item:hover svg { color: #34C759; }
    .emp-nav-item.active {
        background: rgba(52,199,89,0.12);
        color: #16a34a; font-weight: 600;
    }
    .emp-nav-item.active svg { color: #34C759; }
    .emp-user-avatar {
        background: linear-gradient(135deg, #34C759, #00C7BE);
        width: 32px; height: 32px; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        color: white; font-size: 11px; font-weight: 700;
    }
    .emp-online-dot {
        width: 8px; height: 8px; border-radius: 50%; background: #34C759;
        animation: pulse-dot 2s ease-in-out infinite;
        box-shadow: 0 0 0 0 rgba(52,199,89,0.4);
    }
    @keyframes pulse-dot {
        0%,100% { box-shadow: 0 0 0 0 rgba(52,199,89,0.4); }
        50%      { box-shadow: 0 0 0 6px rgba(52,199,89,0); }
    }
</style>

<aside class="emp-sidebar">
    <!-- Logo -->
    <div style="display:flex;align-items:center;gap:10px;padding:0 4px;margin-bottom:28px;">
        <img src="/1stProject/assets/logo.png.jpeg" alt="SkillBridge Logo" style="width:36px;height:36px;border-radius:10px;object-fit:cover;box-shadow:0 6px 20px rgba(0,0,0,0.15);">
        <div>
            <div style="font-size:13px;font-weight:700;color:#1D1D1F;">SkillBridge AI</div>
            <div style="font-size:11px;color:#34C759;font-weight:600;">Employee Portal</div>
        </div>
    </div>

    <!-- Navigation -->
    <nav style="flex:1;">
        <div style="font-size:9px;font-weight:700;color:#9ca3af;letter-spacing:0.1em;text-transform:uppercase;padding:0 12px;margin-bottom:8px;">Navigation</div>
        <?php foreach($pages as $p): ?>
        <a href="<?= $p[0] ?>" class="emp-nav-item <?= $current === $p[0] ? 'active' : '' ?>">
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24"><?= $p[2] ?></svg>
            <?= $p[1] ?>
        </a>
        <?php endforeach; ?>
    </nav>

    <!-- User Footer -->
    <div style="border-top:1px solid rgba(52,199,89,0.12);padding-top:16px;margin-top:8px;" class="border-gray-100">
        <button onclick="toggleTheme()" class="emp-nav-item" style="width:100%; justify-content:flex-start; margin-bottom:8px; border:none; cursor:pointer;">
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" class="dark-hidden"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z" stroke="currentColor" stroke-width="2"/></svg>
            <svg width="17" height="17" fill="none" viewBox="0 0 24 24" class="light-hidden"><circle cx="12" cy="12" r="5" stroke="currentColor" stroke-width="2"/><line x1="12" y1="1" x2="12" y2="3" stroke="currentColor" stroke-width="2"/><line x1="12" y1="21" x2="12" y2="23" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78" stroke="currentColor" stroke-width="2"/><line x1="1" y1="12" x2="3" y2="12" stroke="currentColor" stroke-width="2"/><line x1="21" y1="12" x2="23" y2="12" stroke="currentColor" stroke-width="2"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36" stroke="currentColor" stroke-width="2"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22" stroke="currentColor" stroke-width="2"/></svg>
            Toggle Theme
        </button>
        <div style="display:flex;align-items:center;gap:10px;padding:0 4px;">
            <?php if (!empty($_SESSION['profile_image'])): ?>
                <img src="/1stProject/<?= htmlspecialchars($_SESSION['profile_image']) ?>" style="width:32px;height:32px;border-radius:10px;object-fit:cover;" alt="Profile">
            <?php else: ?>
                <div class="emp-user-avatar">
                    <?= strtoupper(substr($profile_data['first_name']??'E',0,1).substr($profile_data['last_name']??'',0,1)) ?>
                </div>
            <?php endif; ?>
            <div style="flex:1;min-width:0;">
                <div style="font-size:12px;font-weight:600;color:#1D1D1F;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    <?= htmlspecialchars(($profile_data['first_name']??'').' '.($profile_data['last_name']??'')) ?>
                </div>
                <div style="font-size:11px;color:#86868B;display:flex;align-items:center;gap:4px;">
                    <div class="emp-online-dot"></div>
                    <?= $_SESSION['uid']??'' ?> · Employee
                </div>
            </div>
            <button onclick="logout()" title="Logout"
                style="background:none;border:none;cursor:pointer;color:#86868B;padding:4px;border-radius:8px;transition:all 0.15s;"
                onmouseover="this.style.color='#ef4444';this.style.background='#fef2f2'"
                onmouseout="this.style.color='#86868B';this.style.background='none'">
                <svg width="15" height="15" fill="none" viewBox="0 0 24 24">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" stroke="currentColor" stroke-width="2"/>
                    <polyline points="16,17 21,12 16,7" stroke="currentColor" stroke-width="2"/>
                    <line x1="21" y1="12" x2="9" y2="12" stroke="currentColor" stroke-width="2"/>
                </svg>
            </button>
        </div>
    </div>
</aside>

<!-- ==========================================
     FLOATING AI CHATBOT (Available on all pages)
     ========================================== -->
<style>
    /* Chatbot Styles */
    #ai-chat-btn {
        position: fixed; bottom: 24px; right: 24px; z-index: 9999;
        width: 60px; height: 60px; border-radius: 50%;
        background: linear-gradient(135deg, #007AFF, #5856D6);
        color: white; box-shadow: 0 8px 24px rgba(0,122,255,0.4);
        display: flex; align-items: center; justify-content: center;
        cursor: pointer; border: none; transition: transform 0.2s, box-shadow 0.2s;
    }
    #ai-chat-btn:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,122,255,0.5); }
    
    #ai-chat-window {
        position: fixed; bottom: 100px; right: 24px; z-index: 9998;
        width: 360px; height: 480px; background: white;
        border-radius: 20px; box-shadow: 0 12px 40px rgba(0,0,0,0.15);
        display: flex; flex-direction: column; overflow: hidden;
        border: 1px solid rgba(0,0,0,0.05);
        opacity: 0; pointer-events: none; transform: translateY(20px);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
    }
    #ai-chat-window.open {
        opacity: 1; pointer-events: auto; transform: translateY(0);
    }
    html.dark #ai-chat-window { background: #1c1c1e; border-color: #2c2c2e; }

    #ai-chat-header {
        background: linear-gradient(135deg, #007AFF, #5856D6); color: white;
        padding: 16px 20px; display: flex; align-items: center; justify-content: space-between;
    }
    #ai-chat-messages {
        flex: 1; padding: 16px; overflow-y: auto; background: #fafafa;
        display: flex; flex-direction: column; gap: 12px;
    }
    html.dark #ai-chat-messages { background: #111111; }
    
    .chat-msg { max-width: 85%; padding: 10px 14px; border-radius: 14px; font-size: 13px; line-height: 1.4; }
    .chat-msg.user { align-self: flex-end; background: #007AFF; color: white; border-bottom-right-radius: 4px; }
    .chat-msg.ai { align-self: flex-start; background: white; color: #1D1D1F; border: 1px solid #eee; border-bottom-left-radius: 4px; box-shadow: 0 2px 5px rgba(0,0,0,0.02); }
    html.dark .chat-msg.ai { background: #2c2c2e; color: white; border-color: #3a3a3c; }

    #ai-chat-input-area {
        padding: 12px; background: white; border-top: 1px solid #eee;
        display: flex; gap: 8px; align-items: center;
    }
    html.dark #ai-chat-input-area { background: #1c1c1e; border-color: #2c2c2e; }
    
    #ai-chat-input {
        flex: 1; border: none; padding: 10px 14px; border-radius: 20px;
        background: #f4f4f5; outline: none; font-size: 13px;
    }
    html.dark #ai-chat-input { background: #2c2c2e; color: white; }
    
    #ai-chat-send {
        background: #007AFF; color: white; border: none; border-radius: 50%;
        width: 36px; height: 36px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; flex-shrink: 0;
    }
    #ai-chat-send:disabled { background: #ccc; cursor: not-allowed; }
</style>

<!-- Floating Button -->
<button id="ai-chat-btn" onclick="toggleChat()">
    <svg width="28" height="28" fill="none" viewBox="0 0 24 24">
        <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
        <path d="M9 10h.01M15 10h.01M12 10h.01" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
    </svg>
</button>

<!-- Chat Window -->
<div id="ai-chat-window">
    <div id="ai-chat-header">
        <div>
            <div style="font-weight:600; font-size:15px;">SkillBridge AI Assistant</div>
            <div style="font-size:11px; opacity:0.8;">Online & Ready</div>
        </div>
        <button onclick="toggleChat()" style="background:none; border:none; color:white; cursor:pointer;">
            <svg width="20" height="20" fill="none" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
    </div>
    <div id="ai-chat-messages">
        <!-- Initial Welcome Message -->
        <div class="chat-msg ai">
            Hello! I'm the SkillBridge AI. I can instantly answer your questions about Leaves, Salary, Attendance, IDP Progress, and more. How can I help you today?
        </div>
    </div>
    <div id="ai-chat-input-area">
        <input type="text" id="ai-chat-input" placeholder="Type your question..." onkeypress="if(event.key === 'Enter') sendChatMessage()">
        <button id="ai-chat-send" onclick="sendChatMessage()">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13" stroke="currentColor" stroke-width="2" stroke-linecap="round"/><polygon points="22 2 15 22 11 13 2 9 22 2" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        </button>
    </div>
</div>

<script>
    function toggleChat() {
        const win = document.getElementById('ai-chat-window');
        win.classList.toggle('open');
        if (win.classList.contains('open')) {
            document.getElementById('ai-chat-input').focus();
        }
    }

    async function sendChatMessage() {
        const input = document.getElementById('ai-chat-input');
        const msg = input.value.trim();
        if (!msg) return;

        // Add user message to UI
        appendMessage(msg, 'user');
        input.value = '';
        
        // Disable input while fetching
        input.disabled = true;
        document.getElementById('ai-chat-send').disabled = true;

        // Show typing indicator
        const typingId = appendMessage("...", 'ai');

        try {
            const res = await fetch('/1stProject/api/chat.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ message: msg })
            });
            const data = await res.json();
            
            // Remove typing indicator and add real response
            const typingMsg = document.getElementById(typingId);
            if(typingMsg) typingMsg.remove();
            
            appendMessage(data.reply || "Sorry, I couldn't process that.", 'ai');
        } catch (err) {
            console.error(err);
            const typingMsg = document.getElementById(typingId);
            if(typingMsg) typingMsg.remove();
            appendMessage("An error occurred while connecting to the AI.", 'ai');
        }

        // Re-enable input
        input.disabled = false;
        document.getElementById('ai-chat-send').disabled = false;
        input.focus();
    }

    function appendMessage(text, sender) {
        const container = document.getElementById('ai-chat-messages');
        const div = document.createElement('div');
        div.className = 'chat-msg ' + sender;
        // Parse basic markdown if needed (just bolding)
        div.innerHTML = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        const id = 'msg-' + Date.now();
        div.id = id;
        container.appendChild(div);
        container.scrollTop = container.scrollHeight;
        return id;
    }
</script>

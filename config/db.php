<?php
// ============================================================
// Database Configuration
// ============================================================
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') ?: '');
define('DB_NAME', getenv('DB_NAME') ?: 'skillbridge_db');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// Auto-migrate: Add profile_image column if it doesn't exist
@$conn->query("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL");

// App Configuration
define('APP_NAME', 'SkillBridge AI');
define('APP_URL', 'http://localhost/1st Project');
define('SESSION_LIFETIME', 3600); // 1 hour
define('OTP_LIFETIME', 300); // 5 minutes

// Email Configuration (Update with your SMTP settings)
define('MAIL_FROM', 'noreply@skillbridge.com');
define('MAIL_FROM_NAME', 'SkillBridge AI Platform');

// Session Security
function initSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        session_start();
    }
}

// Generate Unique Employee UID
function generateUID($conn) {
    do {
        $uid = 'EMP' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
        $result = $conn->query("SELECT id FROM users WHERE uid = '$uid'");
    } while ($result && $result->num_rows > 0);
    return $uid;
}

// Generate 6-digit OTP
function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

// Send OTP Email (Simple PHP mail wrapper)
function sendOTPEmail($to, $name, $otp) {
    $subject = 'SkillBridge AI - Your Login Verification Code';
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . MAIL_FROM_NAME . " <" . MAIL_FROM . ">\r\n";

    $body = "
    <html>
    <body style='font-family: -apple-system, BlinkMacSystemFont, Inter, sans-serif; background:#F5F5F7; padding:40px;'>
        <div style='max-width:480px; margin:auto; background:#fff; border-radius:20px; padding:40px; box-shadow:0 4px 24px rgba(0,0,0,0.08);'>
            <h2 style='color:#1D1D1F; margin-bottom:8px;'>Verification Code</h2>
            <p style='color:#6E6E73; margin-bottom:32px;'>Hi {$name}, use the code below to complete your login to SkillBridge AI.</p>
            <div style='background:#F5F5F7; border-radius:12px; padding:24px; text-align:center; margin-bottom:24px;'>
                <span style='font-size:40px; font-weight:700; letter-spacing:12px; color:#007AFF;'>{$otp}</span>
            </div>
            <p style='color:#86868B; font-size:14px;'>This code expires in <strong>5 minutes</strong>. Do not share it with anyone.</p>
            <hr style='border:none; border-top:1px solid #F5F5F7; margin:24px 0;'>
            <p style='color:#86868B; font-size:12px;'>SkillBridge AI Platform &bull; Automated Security Email</p>
        </div>
    </body>
    </html>";

    return @mail($to, $subject, $body, $headers);
}

// Auth check: Require login
function requireLogin($role = null) {
    initSecureSession();
    if (!isset($_SESSION['user_id'])) {
        header('Location: /1stProject/index.php');
        exit;
    }
    if ($role && $_SESSION['role'] !== $role) {
        header('Location: /1stProject/index.php');
        exit;
    }
}

// Sanitize input
function sanitize($conn, $input) {
    return $conn->real_escape_string(trim(htmlspecialchars($input)));
}

// JSON Response helper
function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}
?>

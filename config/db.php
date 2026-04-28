<?php
// ============================================================
// Database Configuration (Hosting Friendly)
// ============================================================

// If you are on InfinityFree/Shared Hosting, edit these values:
define('DB_HOST', getenv('DB_HOST') ?: 'localhost'); // e.g., sql301.epizy.com
define('DB_USER', getenv('DB_USER') ?: 'root');      // e.g., if0_41774245
define('DB_PASS', getenv('DB_PASS') ?: '');          // Your DB Password
define('DB_NAME', getenv('DB_NAME') ?: 'skillbridge_db'); // e.g., if0_41774245_db

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

$conn->set_charset('utf8mb4');

// App Configuration
define('APP_NAME', 'SkillBridge AI');
define('APP_URL', (isset($_SERVER['HTTPS']) ? "https" : "http") . "://$_SERVER[HTTP_HOST]");
define('SESSION_LIFETIME', 3600);
define('OTP_LIFETIME', 300);

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

// Helpers
function sanitize($conn, $input) {
    return $conn->real_escape_string(trim(htmlspecialchars($input)));
}

function jsonResponse($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function generateOTP() {
    return str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
}

function sendOTPEmail($to, $name, $otp) {
    // Note: Most shared hosting requires SMTP for mail.
    // For now, we continue to log OTP to error_log for testing.
    error_log("OTP for $name: $otp");
    return true; 
}
?>

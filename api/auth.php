<?php
error_reporting(0);
ini_set('display_errors', 0);
require_once __DIR__ . '/../config/db.php';
initSecureSession();

header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

switch($action) {
    case 'login':       handleLogin($conn, $input);    break;
    case 'verify_2fa':  handleVerify2FA($conn, $input); break;
    case 'resend_otp':  handleResendOTP($conn, $input); break;
    case 'logout':      handleLogout();                break;
    default:
        jsonResponse(['success'=>false,'message'=>'Invalid action.'], 400);
}

// ============================================================
// LOGIN HANDLER
// ============================================================
function handleLogin($conn, $input) {
    $uid        = sanitize($conn, $input['uid'] ?? '');
    $password   = $input['password'] ?? '';
    $login_type = $input['login_type'] ?? ''; // 'admin' or 'employee'

    if (!$uid || !$password) {
        jsonResponse(['success'=>false,'message'=>'All fields are required.']);
    }

    // ⚠️ Clear any previously verified session so stale admin/employee data doesn't interfere
    unset($_SESSION['user_id'], $_SESSION['uid'], $_SESSION['role'],
          $_SESSION['name'], $_SESSION['verified'],
          $_SESSION['pre_auth_id'], $_SESSION['pre_auth_uid'],
          $_SESSION['pre_auth_role'], $_SESSION['pre_auth_name']);

    $stmt = $conn->prepare("SELECT u.*, ep.first_name, ep.last_name FROM users u 
                            LEFT JOIN employee_profiles ep ON ep.user_id = u.id
                            WHERE u.uid = ? AND u.is_active = 1 LIMIT 1");
    $stmt->bind_param('s', $uid);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();

    if (!$user || !password_verify($password, $user['password_hash'])) {
        // Accept "password" as the default for demo
        $validDemo = ($password === 'password' && $user);
        if(!$validDemo) {
            jsonResponse(['success'=>false,'message'=>'Invalid Employee ID or password.']);
        }
    }

    // Enforce portal-role match
    if ($login_type === 'admin' && $user['role'] !== 'admin') {
        jsonResponse(['success'=>false,'message'=>'Access denied. This portal is for administrators only.']);
    }
    if ($login_type === 'employee' && $user['role'] !== 'employee') {
        jsonResponse(['success'=>false,'message'=>'Access denied. Please use the Admin Portal to sign in.']);
    }

    // Generate & store OTP
    $otp     = generateOTP();
    $expires = date('Y-m-d H:i:s', time() + OTP_LIFETIME);
    $userId  = $user['id'];

    $upd = $conn->prepare("UPDATE users SET two_fa_code=?, two_fa_expires=? WHERE id=?");
    $upd->bind_param('ssi', $otp, $expires, $userId);
    $upd->execute();

    // Store pre-auth in session (NOT verified yet)
    $_SESSION['pre_auth_id']   = $userId;
    $_SESSION['pre_auth_uid']  = $user['uid'];
    $_SESSION['pre_auth_role'] = $user['role'];
    $_SESSION['pre_auth_name'] = ($user['first_name'] ?? '') . ' ' . ($user['last_name'] ?? '');
    $_SESSION['pre_auth_image']= $user['profile_image'] ?? '';

    // Send OTP email
    $name = trim($_SESSION['pre_auth_name']) ?: $user['uid'];
    sendOTPEmail($user['email'], $name, $otp);

    // For dev/demo: also log it
    error_log("2FA OTP for {$uid}: {$otp}");

    jsonResponse(['success'=>true,'message'=>'Verification code sent.']);
}

// ============================================================
// VERIFY 2FA HANDLER
// ============================================================
function handleVerify2FA($conn, $input) {
    $uid = sanitize($conn, $input['uid'] ?? '');
    $otp = sanitize($conn, $input['otp'] ?? '');

    if (!isset($_SESSION['pre_auth_uid']) || $_SESSION['pre_auth_uid'] !== $uid) {
        jsonResponse(['success'=>false,'message'=>'Session expired. Please login again.']);
    }

    $userId = $_SESSION['pre_auth_id'];
    $stmt   = $conn->prepare("SELECT two_fa_code, two_fa_expires FROM users WHERE id=? LIMIT 1");
    $stmt->bind_param('i', $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || $row['two_fa_code'] !== $otp) {
        jsonResponse(['success'=>false,'message'=>'Incorrect verification code.']);
    }
    if (strtotime($row['two_fa_expires']) < time()) {
        jsonResponse(['success'=>false,'message'=>'Code expired. Please request a new one.']);
    }

    // Clear OTP, set session as verified
    $conn->query("UPDATE users SET two_fa_code=NULL, two_fa_expires=NULL, last_login=NOW() WHERE id=$userId");

    $_SESSION['user_id']  = $userId;
    $_SESSION['uid']      = $_SESSION['pre_auth_uid'];
    $_SESSION['role']     = $_SESSION['pre_auth_role'];
    $_SESSION['name']     = $_SESSION['pre_auth_name'];
    $_SESSION['profile_image'] = $_SESSION['pre_auth_image'] ?? '';
    $_SESSION['verified'] = true;

    // Clean up pre-auth
    unset($_SESSION['pre_auth_id'], $_SESSION['pre_auth_uid'], $_SESSION['pre_auth_role'], $_SESSION['pre_auth_name'], $_SESSION['pre_auth_image']);

    $redirect = ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'employee/dashboard.php';
    jsonResponse(['success'=>true,'redirect'=>$redirect]);
}

// ============================================================
// RESEND OTP
// ============================================================
function handleResendOTP($conn, $input) {
    $uid = sanitize($conn, $input['uid'] ?? '');

    if (!isset($_SESSION['pre_auth_uid']) || $_SESSION['pre_auth_uid'] !== $uid) {
        jsonResponse(['success'=>false,'message'=>'Session expired. Please login again.']);
    }

    $userId  = $_SESSION['pre_auth_id'];
    $otp     = generateOTP();
    $expires = date('Y-m-d H:i:s', time() + OTP_LIFETIME);

    $upd = $conn->prepare("UPDATE users SET two_fa_code=?, two_fa_expires=? WHERE id=?");
    $upd->bind_param('ssi', $otp, $expires, $userId);
    $upd->execute();

    $row  = $conn->query("SELECT email FROM users WHERE id=$userId")->fetch_assoc();
    $name = $_SESSION['pre_auth_name'] ?: $uid;
    sendOTPEmail($row['email'], $name, $otp);
    error_log("Resent OTP for {$uid}: {$otp}");

    jsonResponse(['success'=>true,'message'=>'New code sent.']);
}

// ============================================================
// LOGOUT
// ============================================================
function handleLogout() {
    session_destroy();
    jsonResponse(['success'=>true,'redirect'=>'../index.php']);
}
?>

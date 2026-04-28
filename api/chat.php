<?php
require_once __DIR__ . '/../config/db.php';
initSecureSession();

// Only employees or admins can use the chat
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['reply' => 'Unauthorized. Please log in.']);
    exit;
}

header('Content-Type: application/json');
$input = json_decode(file_get_contents('php://input'), true);
$message = strtolower(trim($input['message'] ?? ''));

if (!$message) {
    echo json_encode(['reply' => 'Please ask a question.']);
    exit;
}

// ---------------------------------------------------------
// OPTIONAL: INTEGRATE REAL LLM API HERE (Gemini / OpenAI)
// ---------------------------------------------------------
// $api_key = "YOUR_API_KEY_HERE";
// if ($api_key !== "YOUR_API_KEY_HERE") { ... call API ... }
// ---------------------------------------------------------

// LOCAL CONTEXT-AWARE RESPONSES FOR SKILLBRIDGE AI
$reply = "";

// 1. Leave Management
if (strpos($message, 'leave') !== false || strpos($message, 'vacation') !== false || strpos($message, 'sick') !== false) {
    $reply = "You can manage your leaves in the **Apply for Leave** section. You can apply for Sick Leave, Casual Leave, or Earned Leave. Once submitted, your HR admin will review and approve it.";
} 
// 2. Payroll and Salary
elseif (strpos($message, 'salary') !== false || strpos($message, 'pay') !== false || strpos($message, 'payroll') !== false) {
    $reply = "Your salary is processed at the end of each month. Your base salary and bonuses are determined by your Knowledge Score and Tier (Diamond, Gold, Silver, Bronze). Reach out to Admin for detailed slips.";
} 
// 3. Knowledge Score & Tier
elseif (strpos($message, 'score') !== false || strpos($message, 'tier') !== false || strpos($message, 'diamond') !== false || strpos($message, 'rank') !== false) {
    $reply = "Your Knowledge Score is calculated based on your completed IDP tasks and performance. Higher scores upgrade your tier (Bronze -> Silver -> Gold -> Diamond), which can unlock better perks and salary bumps!";
}
// 4. Progress & IDP
elseif (strpos($message, 'idp') !== false || strpos($message, 'progress') !== false || strpos($message, 'skills') !== false || strpos($message, 'learn') !== false) {
    $reply = "The Individual Development Plan (IDP) helps you grow. Check the **My Progress** tab to see AI-recommended skills. Complete tasks to increase your Knowledge Score.";
}
// 5. Attendance
elseif (strpos($message, 'attendance') !== false || strpos($message, 'time') !== false || strpos($message, 'late') !== false) {
    $reply = "Attendance is tracked daily. Ensure you log in on time. Consistently being late or absent without applying for leave may negatively impact your performance metrics.";
}
// 6. Profile & ID
elseif (strpos($message, 'profile') !== false || strpos($message, 'id') !== false || strpos($message, 'email') !== false || strpos($message, 'password') !== false) {
    $reply = "Your Employee ID and Email are set by the Admin. You cannot change them yourself. If you need to update your password or profile picture, please contact your HR Administrator.";
}
// Greetings
elseif (strpos($message, 'hi') === 0 || strpos($message, 'hello') === 0 || strpos($message, 'hey') === 0) {
    $reply = "Hello there! 👋 I am your SkillBridge AI Assistant. I can help you with questions about your Leaves, Payroll, Knowledge Score, IDP Progress, and general HR policies. How can I assist you today?";
}
// Default Fallback
else {
    $reply = "I'm the SkillBridge AI Assistant. I'm currently trained to help you with HR-related topics like Leaves, Payroll, Attendance, Skills/IDP, and your Profile. Could you please rephrase your question regarding these topics?";
}

// Simulate slight AI typing delay for realism
usleep(600000); // 0.6 seconds

echo json_encode(['reply' => $reply]);
exit;


<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register_doctor.html");
    exit;
}

// Inputs ලබාගන්න
$full_name      = trim($_POST['full_name'] ?? '');
$email          = trim($_POST['email'] ?? '');
$password       = $_POST['password'] ?? '';
$confirm        = $_POST['confirm_password'] ?? '';
$phone          = trim($_POST['phone'] ?? '');
$specialization = trim($_POST['specialization'] ?? '');
$department     = trim($_POST['department'] ?? '');
$experience     = (int)($_POST['experience'] ?? 0);

// ✅ Validation
if (empty($full_name) || empty($email) || empty($password)) {
    $_SESSION['error'] = "Full Name, Email, සහ Password අනිවාර්යයි.";
    header("Location: register_doctor.html");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Email address එක වැරදියි.";
    header("Location: register_doctor.html");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password අවම වශයෙන් අකුරු 6ක් වෙන්න ඕනේ.";
    header("Location: register_doctor.html");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = "Password දෙක එකිනෙකට වෙනස්.";
    header("Location: register_doctor.html");
    exit;
}

// ✅ Email දැනටමත් doctors table එකේ තියෙනවද check කරන්න
$check = $pdo->prepare("SELECT id FROM doctors WHERE email = ?");
$check->execute([$email]);

if ($check->rowCount() > 0) {
    $_SESSION['error'] = "මේ Email එක දැනටමත් register කරලා තියෙනවා.";
    header("Location: register_doctor.html");
    exit;
}

// ✅ Database එකට Save කරන්න (doctors table එකට)
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);

$stmt = $pdo->prepare("INSERT INTO doctors 
    (full_name, email, password, phone, specialization, department, experience) 
    VALUES (?, ?, ?, ?, ?, ?, ?)");

$stmt->execute([
    $full_name,
    $email,
    $hashedPassword,
    $phone,
    $specialization,
    $department,
    $experience
]);

// ✅ Session set කරන්න (login වෙච්ච වගේ)
$_SESSION['user_id']   = $pdo->lastInsertId();
$_SESSION['user_name'] = $full_name;
$_SESSION['user_role'] = 'doctor';

header("Location: doctor_dashboard.php");
exit;
?>
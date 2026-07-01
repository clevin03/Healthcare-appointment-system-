<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data     = json_decode(file_get_contents('php://input'), true);
$first    = trim($data['first']    ?? '');
$last     = trim($data['last']     ?? '');
$username = trim(strtolower($data['username']  ?? ''));
$email    = trim($data['email']    ?? '');
$phone    = trim($data['phone']    ?? '');
$specialty= trim($data['specialty']?? '');
$password = $data['password']      ?? '';

if (empty($first)||empty($last)||empty($username)||empty($email)||empty($password)) {
    echo json_encode(["success"=>false,"message"=>"සියලුම fields අනිවාර්යයි."]);
    exit;
}

// Username හෝ Email දැනටමත් තියෙනවද check
$check = $pdo->prepare("SELECT id FROM doctors WHERE username = ? OR email = ?");
$check->execute([$username, $email]);
if ($check->rowCount() > 0) {
    echo json_encode(["success"=>false,"message"=>"Username හෝ Email දැනටමත් register කරලා තියෙනවා."]);
    exit;
}

$fullName       = "Dr. $first $last";
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
$initials       = strtoupper(substr($first,0,1).substr($last,0,1));
$colors         = ["#6C4FC4","#0EA5E9","#10B981","#F59E0B","#EF4444","#8B5CF6","#EC4899"];
$color          = $colors[array_rand($colors)];

$stmt = $pdo->prepare("INSERT INTO doctors 
    (full_name, username, password, email, phone, specialization, department, experience) 
    VALUES (?, ?, ?, ?, ?, ?, ?, 0)");
$stmt->execute([$fullName, $username, $hashedPassword, $email, $phone, $specialty, $specialty]);

echo json_encode([
    "success" => true,
    "doctor"  => [
        "username" => $username,
        "name"     => $fullName,
        "specialty"=> $specialty,
        "initials" => $initials,
        "color"    => $color
    ]
]);
?>
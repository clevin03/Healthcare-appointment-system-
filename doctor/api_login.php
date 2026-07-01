<?php
header('Content-Type: application/json');
require_once '../config/db.php';

$data = json_decode(file_get_contents('php://input'), true);
$username = trim(strtolower($data['username'] ?? ''));
$password = $data['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM doctors WHERE username = ?");
$stmt->execute([$username]);
$doc = $stmt->fetch(PDO::FETCH_ASSOC);

if ($doc && password_verify($password, $doc['password'])) {
    $names = explode(" ", $doc['full_name']);
    $initials = strtoupper(substr($names[0],0,1) . substr(end($names),0,1));
    echo json_encode([
        "success" => true,
        "doctor" => [
            "username" => $doc['username'],
            "name"     => $doc['full_name'],
            "specialty"=> $doc['specialization'],
            "initials" => $initials,
            "color"    => "#6C4FC4"
        ]
    ]);
} else {
    echo json_encode(["success" => false]);
}
?>
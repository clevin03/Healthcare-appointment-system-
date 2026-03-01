<?php
session_start();
require_once 'config/db_connection.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}


$first_name = trim($_POST['first_name'] ?? '');
$last_name = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$date_of_birth = trim($_POST['date_of_birth'] ?? '');
$gender = trim($_POST['gender'] ?? '');
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$terms = isset($_POST['terms']);

$errors = [];

if (empty($first_name)) {
    $errors[] = 'First name is required';
}

if (empty($last_name)) {
    $errors[] = 'Last name is required';
}

if (empty($email)) {
    $errors[] = 'Email is required';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Invalid email format';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
} elseif (strlen($phone) < 10) {
    $errors[] = 'Phone number must be at least 10 digits';
}

if (empty($date_of_birth)) {
    $errors[] = 'Date of birth is required';
}

if (empty($gender)) {
    $errors[] = 'Gender is required';
}

if (empty($address)) {
    $errors[] = 'Address is required';
}

if (empty($password)) {
    $errors[] = 'Password is required';
} elseif (strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters';
} elseif (!preg_match('/[a-zA-Z]/', $password) || !preg_match('/\d/', $password)) {
    $errors[] = 'Password must contain both letters and numbers';
}

if ($password !== $confirm_password) {
    $errors[] = 'Passwords do not match';
}

if (!$terms) {
    $errors[] = 'You must agree to the terms and conditions';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => implode(', ', $errors)]);
    exit;
}

try {
    if (!isset($conn)) {
        throw new Exception('Database connection not available');
    }

    $stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'Email already registered']);
        exit;
    }
    }

    $conn->begin_transaction();
    
    try {

        $password_hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $conn->prepare("INSERT INTO users (email, password, user_type, is_active) VALUES (?, ?, 'patient', 1)");
        $stmt->bind_param("ss", $email, $password_hash);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create user account');
        }
        
        $user_id = $conn->insert_id;
        
        $stmt = $conn->prepare("INSERT INTO patients (user_id, first_name, last_name, phone, date_of_birth, gender, address) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssss", $user_id, $first_name, $last_name, $phone, $date_of_birth, $gender, $address);
        
        if (!$stmt->execute()) {
            throw new Exception('Failed to create patient profile');
        }
        
        $patient_id = $conn->insert_id;

        $conn->commit();

        $_SESSION['user_id'] = $user_id;
        $_SESSION['patient_id'] = $patient_id;
        $_SESSION['user_email'] = $email;
        $_SESSION['user_type'] = 'patient';
        $_SESSION['user_name'] = $first_name . ' ' . $last_name;
        
        http_response_code(201);
        echo json_encode([
            'success' => true, 
            'message' => 'Registration successful!',
            'redirect' => 'patient/patient_dashboard.php'
        ]);
        
    } catch (Exception $e) {

        $conn->rollback();
        throw $e;
}

if (isset($stmt)) {
    $stmt->close();
}
if (isset($conn)) {
    $conn->close();
}
?>

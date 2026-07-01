<?php
session_start();
require_once '../config/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.html");
    exit;
}

// Get and sanitize inputs
$full_name = trim($_POST['full_name'] ?? '');
$email     = trim($_POST['email'] ?? '');
$password  = $_POST['password'] ?? '';
$confirm   = $_POST['confirm_password'] ?? '';
$terms     = $_POST['terms'] ?? '';

// Validation
if (empty($full_name) || empty($email) || empty($password)) {
    $_SESSION['error'] = "All fields are required.";
    header("Location: register.html");
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['error'] = "Invalid email address.";
    header("Location: register.html");
    exit;
}

if (strlen($password) < 6) {
    $_SESSION['error'] = "Password must be at least 6 characters.";
    header("Location: register.html");
    exit;
}

if ($password !== $confirm) {
    $_SESSION['error'] = "Passwords do not match.";
    header("Location: register.html");
    exit;
}

if (empty($terms)) {
    $_SESSION['error'] = "You must agree to the Terms and Conditions.";
    header("Location: register.html");
    exit;
}

// Check if email already exists
$stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
$stmt->execute([$email]);

if ($stmt->rowCount() > 0) {
    $_SESSION['error'] = "Email is already registered.";
    header("Location: register.html");
    exit;
}

// Save to database
$hashed = password_hash($password, PASSWORD_BCRYPT);
$stmt = $pdo->prepare("INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)");
$stmt->execute([$full_name, $email, $hashed]);

$_SESSION['success'] = "Account created successfully! Please log in.";
header("Location: login.php");
exit;
?>
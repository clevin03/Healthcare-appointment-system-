<?php
session_start();
require_once '../config/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name      = trim($_POST['full_name']);
    $email          = trim($_POST['email']);
    $phone          = trim($_POST['phone']);
    $specialization = trim($_POST['specialization']);
    $department     = trim($_POST['department']);
    $experience     = (int)$_POST['experience'];

    // Email already exists check
    $check = $pdo->prepare("SELECT id FROM doctors WHERE email = ?");
    $check->execute([$email]);

    if ($check->rowCount() > 0) {
        $error = "මේ email එක දැනටමත් register වෙලා තියෙනවා!";
    } else {
        $stmt = $pdo->prepare("INSERT INTO doctors 
            (full_name, email, phone, specialization, department, experience) 
            VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$full_name, $email, $phone, $specialization, $department, $experience]);
        $success = "Doctor කෙනා සාර්ථකව add කරන ලදී!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Add Doctor</title>
    <link rel="stylesheet" href="../static/style.css">
</head>
<body>
<div class="container">
    <h2>Add New Doctor</h2>

    <?php if ($error): ?>
        <div class="alert error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="alert success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST" action="add_doctor.php">
        <div class="form-group">
            <label>Full Name</label>
            <input type="text" name="full_name" required placeholder="Dr. Kamal Perera">
        </div>
        <div class="form-group">
            <label>Email</label>
            <input type="email" name="email" required placeholder="doctor@healthcare.com">
        </div>
        <div class="form-group">
            <label>Phone</label>
            <input type="text" name="phone" placeholder="0771234567">
        </div>
        <div class="form-group">
            <label>Specialization</label>
            <input type="text" name="specialization" placeholder="Cardiologist">
        </div>
        <div class="form-group">
            <label>Department</label>
            <input type="text" name="department" placeholder="Cardiology">
        </div>
        <div class="form-group">
            <label>Experience (years)</label>
            <input type="number" name="experience" min="0" placeholder="5">
        </div>
        <button type="submit" class="btn-submit">Add Doctor</button>
        <a href="doctors_list.php" class="btn-back">← Back to List</a>
    </form>
</div>
</body>
</html>
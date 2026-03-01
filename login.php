<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Appointment - Login</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="../static/login.css">
</head>
<body>
    <?php
session_start();

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_type']) {
        case 'admin':
            header("Location: admin/admin_dashboard.php");
            break;
        case 'doctor':
            header("Location: doctor/doctor_dashboard.php");
            break;
        case 'patient':
            header("Location: patient/patient_dashboard.php");
            break;
    }
    exit;
}

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $user_type = isset($_POST['user_type']) ? $_POST['user_type'] : 'patient';

    if (empty($email) || empty($password)) {
        $error_message = "Please fill in all fields.";
    } else {
        $valid_users = array(
            'admin' => array('admin@healthcare.com' => 'admin123'),
            'doctor' => array('doctor@healthcare.com' => 'doctor123'),
            'patient' => array('patient@healthcare.com' => 'patient123')
        );

        if (isset($valid_users[$user_type][$email]) && $valid_users[$user_type][$email] === $password) {
            $_SESSION['user_id'] = uniqid();
            $_SESSION['user_email'] = $email;
            $_SESSION['user_type'] = $user_type;

            switch ($user_type) {
                case 'admin':
                    header("Location: admin/admin_dashboard.php");
                    break;
                case 'doctor':
                    header("Location: doctor/doctor_dashboard.php");
                    break;
                case 'patient':
                    header("Location: patient/patient_dashboard.php");
                    break;
            }
            exit;
        } else {
            $error_message = "Invalid email or password.";
        }
    }
}
?>
    <div class="container">
        <div class="login-box">
            <div class="header">
                <h1>Healthcare</h1>
                <p>Appointment System</p>
            </div>

            <?php if (!empty($error_message)): ?>
                <div class="error-message"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                <div class="success-message"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label for="user_type">Login As</label>
                    <select id="user_type" name="user_type" required>
                        <option value="patient">Patient</option>
                        <option value="doctor">Doctor</option>
                        <option value="admin">Administrator</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Enter your email" 
                        required
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Enter your password" 
                        required
                    >
                </div>

                <button type="submit" class="submit-btn">Login</button>
            </form>

            <div class="demo-credentials">
                <h4><i class="fas fa-info-circle"></i> Demo Credentials</h4>
                <p><strong>Patient:</strong> <code>patient@healthcare.com</code> / <code>patient123</code></p>
                <p><strong>Doctor:</strong> <code>doctor@healthcare.com</code> / <code>doctor123</code></p>
                <p><strong>Admin:</strong> <code>admin@healthcare.com</code> / <code>admin123</code></p>
            </div>
        </div>
    </div>
    <script src="static/cursor_effect.js"></script>
</body>
</html>
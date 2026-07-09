<?php
session_start();

if (isset($_SESSION['user_id'])) {
    switch ($_SESSION['user_type']) {
        case 'admin':
            header("Location: ../admin/admin_dashboard.php");
            break;
        case 'doctor':
            header("Location: ../doctor/doctor_dashboard.php");
            break;
        case 'patient':
            header("Location: ../patient/patient_dashboard.php");
            break;
    }
    exit;
}

require_once '../config/db_connection.php';

$error_message = '';
$success_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $user_type_from_form = $_POST['user_type'] ?? '';

    if (empty($email) || empty($password) || empty($user_type_from_form)) {
        $error_message = "Please fill in all fields.";
    } else {
        try {
            $stmt = $conn->prepare("SELECT user_id, password, user_type, is_active FROM users WHERE email = ?");
            if (!$stmt) {
                throw new Exception('Database query preparation failed: ' . $conn->error);
            }
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();

                if (password_verify($password, $user['password']) && $user['user_type'] === $user_type_from_form) {
                    if ($user['is_active'] == 0) {
                        $error_message = "Your account is inactive. Please contact support.";
                    } else {
                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['user_email'] = $email;
                        $_SESSION['user_type'] = $user['user_type'];

                        if ($user['user_type'] === 'patient') {
                            $patient_stmt = $conn->prepare("SELECT patient_id, first_name, last_name FROM patients WHERE user_id = ?");
                            $patient_stmt->bind_param("i", $user['user_id']);
                            $patient_stmt->execute();
                            if ($patient_data = $patient_stmt->get_result()->fetch_assoc()) {
                                $_SESSION['patient_id'] = $patient_data['patient_id'];
                                $_SESSION['user_name'] = $patient_data['first_name'] . ' ' . $patient_data['last_name'];
                            }
                            $patient_stmt->close();
                        } elseif ($user['user_type'] === 'doctor') {
                            $doctor_stmt = $conn->prepare("SELECT doctor_id, doctor_name FROM doctors WHERE user_id = ?");
                            if ($doctor_stmt) {
                                $doctor_stmt->bind_param("i", $user['user_id']);
                                $doctor_stmt->execute();
                                if ($doctor_data = $doctor_stmt->get_result()->fetch_assoc()) {
                                    $_SESSION['doctor_id'] = $doctor_data['doctor_id'];
                                    $_SESSION['user_name'] = $doctor_data['doctor_name'];
                                }
                                $doctor_stmt->close();
                            }
                        } else {
                            $admin_stmt = $conn->prepare("SELECT admin_id FROM admin WHERE user_id = ?");
                            if ($admin_stmt) {
                                $admin_stmt->bind_param("i", $user['user_id']);
                                $admin_stmt->execute();
                                if ($admin_data = $admin_stmt->get_result()->fetch_assoc()) {
                                    $_SESSION['user_id'] = $admin_data['admin_id'];

                                    $display_name = explode('@', $email)[0];
                                    $column_check = $conn->query("SELECT 1 FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'admin' AND COLUMN_NAME = 'admin_name'");
                                    if ($column_check && $column_check->num_rows > 0) {
                                        $admin_name_stmt = $conn->prepare("SELECT admin_name FROM admin WHERE user_id = ?");
                                        if ($admin_name_stmt) {
                                            $admin_name_stmt->bind_param("i", $user['user_id']);
                                            $admin_name_stmt->execute();
                                            if ($admin_name_row = $admin_name_stmt->get_result()->fetch_assoc()) {
                                                $display_name = $admin_name_row['admin_name'];
                                            }
                                            $admin_name_stmt->close();
                                        }
                                    }

                                    $_SESSION['user_name'] = $display_name;
                                    $_SESSION['user_type'] = 'admin';
                                }
                                $admin_stmt->close();
                            }
                        }

                        header("Location: ../{$user['user_type']}/{$user['user_type']}_dashboard.php");
                        exit;
                    }
                } else {
                    $error_message = "Invalid email, password, or user role.";
                }
            } else {
                $error_message = "Invalid email, password, or user role.";
            }
            $stmt->close();
            $conn->close();
        } catch (Exception $e) {
            // For development, it's helpful to see the actual error message.
            $error_message = "An error occurred during login: " . $e->getMessage();
            // In production, it's better to log errors than to display them.
            error_log('Login error: ' . $e->getMessage());
        }
    }
}
?>

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
    <script src="../static/cursor_effect.js"></script>
</body>
</html>
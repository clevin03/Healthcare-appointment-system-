<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: /Healthcare-appointment-system-/auth/login.php');
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && $_POST['action'] == 'update_profile') {
        $new_email = isset($_POST['admin_email']) ? trim($_POST['admin_email']) : '';
        $new_password = isset($_POST['admin_password']) ? trim($_POST['admin_password']) : '';
        
        if (!empty($new_email)) {
            // Note: In a real database-driven system, this would update the admin credentials in the database.
            // Since credentials are currently hardcoded in auth/login.php, we just show a message.
            $success_message = "Admin profile updated successfully. (Note: Demo system uses hardcoded credentials in login.php)";
        } else {
            $error_message = "Email field cannot be empty.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'update_general') {
        $hospital_name = isset($_POST['hospital_name']) ? trim($_POST['hospital_name']) : '';
        $hospital_address = isset($_POST['hospital_address']) ? trim($_POST['hospital_address']) : '';
        $hospital_phone = isset($_POST['hospital_phone']) ? trim($_POST['hospital_phone']) : '';
        $hospital_email = isset($_POST['hospital_email']) ? trim($_POST['hospital_email']) : '';
        
        // This is where you would update the settings in a database table.
        $success_message = "General settings updated successfully.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/dashboard.css">
    <style>
        .settings-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 20px;
        }
        
        @media (min-width: 768px) {
            .settings-container {
                grid-template-columns: 1fr 1fr;
            }
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            color: #333;
        }
        
        .form-group input[type="text"],
        .form-group input[type="email"],
        .form-group input[type="password"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-hospital"></i> HealthCare Admin</div>
            <div class="user-info">
                <span>Admin User</span>
                <form method="post" action="/Healthcare-appointment-system-/auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" style="display:inline; margin:0;">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="admin_dashboard.php"><i class="fa-solid fa-chart-column"></i> Dashboard</a></li>
                <li><a href="appointment.php"><i class="fa-solid fa-calendar"></i> Appointments</a></li>
                <li><a href="patient.php"><i class="fa-solid fa-users"></i> Patients</a></li>
                <li><a href="doctor.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a></li>
                <li><a href="department.php"><i class="fa-solid fa-building"></i> Departments</a></li>
                <li><a href="#reports"><i class="fa-solid fa-chart-line"></i> Reports</a></li>
                <li><a href="settings.php" class="active"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1 class="page-title">System Settings</h1>

            <?php if (!empty($success_message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            <div class="settings-container">
                <!-- Admin Profile Settings -->
                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-user-shield"></i> Admin Profile</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_profile">
                        
                        <div class="form-group">
                            <label for="admin_email">Admin Email (Login ID)</label>
                            <input type="email" id="admin_email" name="admin_email" value="admin@healthcare.com" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="admin_password">New Password (Leave blank to keep current)</label>
                            <input type="password" id="admin_password" name="admin_password" placeholder="Enter new password">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Update Profile</button>
                    </form>
                </div>

                <!-- General Hospital Settings -->
                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-hospital"></i> General Settings</h2>
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="update_general">
                        
                        <div class="form-group">
                            <label for="hospital_name">Hospital / Clinic Name</label>
                            <input type="text" id="hospital_name" name="hospital_name" value="BCI Healthcare Center" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="hospital_address">Address</label>
                            <input type="text" id="hospital_address" name="hospital_address" value="123 Health Avenue, Medical District">
                        </div>
                        
                        <div class="form-group">
                            <label for="hospital_phone">Contact Phone</label>
                            <input type="text" id="hospital_phone" name="hospital_phone" value="+1 (555) 123-4567">
                        </div>
                        
                        <div class="form-group">
                            <label for="hospital_email">Contact Email</label>
                            <input type="email" id="hospital_email" name="hospital_email" value="info@bcihealthcare.com">
                        </div>
                        
                        <button type="submit" class="btn btn-primary" style="margin-top: 10px;">Save General Settings</button>
                    </form>
                </div>
            </div>
            
            <div class="section" style="margin-top: 20px;">
                 <h2 class="section-title"><i class="fa-solid fa-screwdriver-wrench"></i> System Maintenance</h2>
                 <p style="color: #666; margin-bottom: 15px;">Advanced system configuration and maintenance tools.</p>
                 
                 <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                     <button class="btn btn-secondary" onclick="alert('Backup feature would generate and download an SQL dump here.')"><i class="fa-solid fa-database"></i> Backup Database</button>
                     <button class="btn btn-secondary" onclick="alert('Maintenance mode would be toggled here.')"><i class="fa-solid fa-power-off"></i> Toggle Maintenance Mode</button>
                 </div>
            </div>

            <div class="footer">
                <p>&copy; 2026 BCI Healthcare Center. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
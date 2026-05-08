<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db_connection.php';

$patient_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['patient_name']) ? $_SESSION['patient_name'] : 'Test Patient');
$patient_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'patient@edoc.com';
$patient_id = $_SESSION['patient_id'] ?? $_SESSION['user_id'];

// Get all doctors with their departments
$sql = "SELECT d.*, dep.department_name 
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.department_id 
        WHERE d.status = 'ACTIVE'
        ORDER BY d.doctor_name ASC";
$result = $conn->query($sql);
$doctors = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
}

$current_date = date('Y-m-d');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Doctors - Healthcare System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/patient_dashboard.css">
    <link rel="stylesheet" href="static/allDoctors.css">
</head>
<body>
<div class="top-header" style="background-color: #007bff; color: white;">
    <div class="header-content">
        <div class="user-profile">
            <div class="user-avatar">
                <span class="avatar-icon"><i class="fas fa-user"></i></span>
            </div>
            <div class="user-info">
                <h3 class="user-name"><?php echo htmlspecialchars($patient_name); ?></h3>
                <p class="user-email"><?php echo htmlspecialchars($patient_email); ?></p>
            </div>
        </div>
        <form method="post" action="../auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" class="logout-form">
            <button type="submit" class="logout-btn">Logout</button>
        </form>
    </div>
</div>

<div class="container">
    <div class="sidebar">
        <nav class="sidebar-menu">
            <ul>
                <li>
                    <a href="#home" class="menu-item">
                        <span class="menu-icon"><i class="fas fa-home"></i></span>
                        <span class="menu-text">Home</span>
                    </a>
                </li>
                <li>
                    <a href="#chatbot" class="menu-item">
                        <span class="menu-icon"><i class="fas fa-robot"></i></span>
                        <span class="menu-text">Health Assistant</span>
                    </a>
                </li>
                <li>
                    <a href="#doctors" class="menu-item active">
                        <span class="menu-icon"><i class="fas fa-user-md"></i></span>
                        <span class="menu-text">All Doctors</span>
                    </a>
                </li>
                <li>
                    <a href="#sessions" class="menu-item">
                        <span class="menu-icon"><i class="fas fa-calendar"></i></span>
                        <span class="menu-text">Scheduled Sessions</span>
                    </a>
                </li>
                <li>
                    <a href="#bookings" class="menu-item">
                        <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                        <span class="menu-text">My Bookings</span>
                    </a>
                </li>
                <li>
                    <a href="#settings" class="menu-item">
                        <span class="menu-icon"><i class="fas fa-cog"></i></span>
                        <span class="menu-text">Settings</span>
                    </a>
                </li>
            </ul>
        </nav>
    </div>

    <div class="main-content">
        <div class="header">
            <h1>All Doctors</h1>
            <div class="today-date">
                <span class="date-label">Today's Date</span>
                <span class="date-value"><?php echo $current_date; ?></span>
                <span class="calendar-icon"><i class="fas fa-calendar-alt"></i></span>
            </div>
        </div>

        <div class="doctors-section">
            <?php if (count($doctors) > 0): ?>
                <div class="doctors-grid">
                    <?php foreach ($doctors as $doctor): ?>
                        <div class="doctor-card">
                            <div class="doctor-avatar">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div class="doctor-info">
                                <h3><?php echo htmlspecialchars($doctor['doctor_name']); ?></h3>
                                <p class="doctor-specialty"><?php echo htmlspecialchars($doctor['department_name'] ?? 'General Practitioner'); ?></p>
                                <div class="doctor-contact">
                                    <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($doctor['email']); ?></p>
                                    <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($doctor['phone']); ?></p>
                                </div>
                                <a href="book_appointment.php?doctor_id=<?php echo $doctor['doctor_id']; ?>" class="book-btn">Book Appointment</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="no-doctors">
                    <i class="fas fa-user-slash fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                    <p>No doctors found at the moment.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="static/patient_dashboard.js"></script>
</body>
</html>
<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db_connection.php';

$patient_name = $_SESSION['user_name'] ?? 'Patient';
$patient_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'patient@edoc.com';
$patient_id = $_SESSION['patient_id'] ?? $_SESSION['user_id'];

// Get all active doctors count
$sql_doctors = "SELECT COUNT(*) AS total FROM doctors WHERE status = 'ACTIVE'";
$result_doctors = $conn->query($sql_doctors);
$all_doctors = $result_doctors->fetch_assoc()['total'] ?? 0;

// Get all patients count
$sql_patients = "SELECT COUNT(*) AS total FROM patients";
$result_patients = $conn->query($sql_patients);
$all_patients = $result_patients->fetch_assoc()['total'] ?? 0;

// Get new pending bookings count for this patient
$sql_new_bookings = "SELECT COUNT(*) AS total FROM appointments WHERE patient_id = ? AND status = 'PENDING'";
$stmt = $conn->prepare($sql_new_bookings);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$new_bookings = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Get today's sessions count for this patient
/* To get the date from the `sessions` table, we must join it.
   This requires a `session_id` column in the `appointments` table. */
$sql_today = "SELECT COUNT(*) AS total FROM appointments a
              JOIN sessions s ON a.session_id = s.session_id
              WHERE a.patient_id = ? AND DATE(s.session_day) = CURDATE() AND a.status != 'CANCELLED'";
$stmt = $conn->prepare($sql_today);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$today_sessions = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
$stmt->close();

// Get upcoming bookings by joining with sessions
$upcoming_bookings = [];
$sql_upcoming = "SELECT a.appointment_number, d.doctor_name, s.session_day, s.start_time
                 FROM appointments a 
                 JOIN sessions s ON a.session_id = s.session_id
                 LEFT JOIN doctors d ON s.doctor_id = d.doctor_id 
                  
                 WHERE a.patient_id = ? AND s.session_day >= CURDATE() AND a.status != 'CANCELLED' 
                 ORDER BY s.session_day ASC, s.start_time ASC 
                 LIMIT 5";
$stmt = $conn->prepare($sql_upcoming);
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result_upcoming = $stmt->get_result();
if ($result_upcoming && $result_upcoming->num_rows > 0) {
    while ($row = $result_upcoming->fetch_assoc()) {
        $upcoming_bookings[] = [
            'appt_number' => $row['appointment_number'],
            'session_title' => ($row['department_name'] ?? 'General') . ' Consultation',
            'doctor' => $row['doctor_name'],
            'scheduled_date_time' => date('Y-m-d H:i', strtotime($row['session_day'] . ' ' . $row['start_time']))
        ];
    }
}
$stmt->close();

$current_date = date('Y-m-d');
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/patient_dashboard.css">
</head>
<body>


    <div class="top-header" style="background-color: #007bff; color: white;"> <!-- Added unique class and inline style -->
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
                        <a href="#home" class="menu-item active">
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
                        <a href="#doctors" class="menu-item">
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
                        <a href="#records" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="menu-text">My Records</span>
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
                <h1>Home</h1>
                <div class="today-date">
                    <span class="date-label">Today's Date</span>
                    <span class="date-value"><?php echo date('Y-m-d'); ?></span>
                    <span class="calendar-icon"><i class="fas fa-calendar-alt"></i></span>
                </div>
            </div>
            <div class="welcome-banner">
                <div class="banner-content">
                    <h2>Welcome!</h2>
                    <h3><?php echo htmlspecialchars($patient_name); ?>.</h3>
                    <p>Don't know which doctor to choose? No problem! Jump to the <strong>"All Doctors"</strong> section or check <strong>"Scheduled Sessions"</strong>. You can also track your past and future appointments.<br>
                    Also find out the expected arrival time of your doctor or medical consultant.</p>
                    
                    <div class="search-section">
                        <h4>Channel a Doctor Here</h4>
                        <form class="search-form">
                            <div class="search-box">
                                <span class="search-icon"><i class="fas fa-search"></i></span>
                                <input type="text" placeholder="Search Doctor and We will Find The Session Available" id="doctor-search">
                            </div>
                            <button type="submit" class="search-btn">Search</button>
                        </form>
                    </div>
                </div>
                
            </div>

            <div class="status-section">
                <h3>Status</h3>
                <div class="status-grid blue">
                    <div class="status-card line">
                        <div class="status-number"><?php echo $all_doctors; ?></div>
                        <div class="status-label">All Doctors</div>
                        <!--<div class="status-icon"><i class="fas fa-user-md"></i></div>-->
                    </div>

                    <div class="status-card green">
                        <div class="status-number"><?php echo $all_patients; ?></div>
                        <div class="status-label">All Patients</div>
                        <!--<div class="status-icon"><i class="fas fa-users"></i></div>-->
                    </div>

                    <div class="status-card orange">
                        <div class="status-number"><?php echo $new_bookings; ?></div>
                        <div class="status-label">NewBooking</div>
                        <!--<div class="status-icon"><i class="fas fa-file-medical"></i></div>-->
                    </div>

                    <div class="status-card red">
                        <div class="status-number"><?php echo $today_sessions; ?></div>
                        <div class="status-label">Today Sessions</div>
                        <!--<div class="status-icon"><i class="fas fa-tv"></i></div>-->
                    </div>
                </div>
            </div>

            <div class="bookings-section">
                <h3>Your Upcoming Booking</h3>
                <div class="bookings-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Appoint. Number</th>
                                <th>Session Title</th>
                                <th>Doctor</th>
                                <th>Scheduled Date & Time</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($upcoming_bookings) > 0): ?>
                                <?php foreach ($upcoming_bookings as $booking): ?>
                                    <tr>
                                        <td><?php echo $booking['appt_number']; ?></td>
                                        <td><?php echo htmlspecialchars($booking['session_title']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['doctor']); ?></td>
                                        <td><?php echo htmlspecialchars($booking['scheduled_date_time']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="4" class="no-data">No upcoming bookings</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Chatbot -->
    <div class="chatbot-icon" id="chatbotIcon">
        <i class="fas fa-comments"></i>
    </div>

    <div class="chatbot-modal" id="chatbotModal">
        <div class="chatbot-container">
            <div class="chatbot-header">
                <h3><i class="fas fa-robot"></i> Healthcare Assistant</h3>
                <button class="close-chatbot" id="closeChatbot">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="chatbot-messages" id="chatbotMessages">
                <div class="bot-message">
                    <div class="message-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        <p>Hello! I'm your healthcare assistant. How can I help you today?</p>
                        <span class="message-time"><?php echo date('H:i'); ?></span>
                    </div>
                </div>
            </div>
            <div class="chatbot-input">
                <input type="text" id="chatbotInput" placeholder="Type your message here...">
                <button id="sendMessage">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
    </div>

    <script src="static/patient_dashboard.js"></script>
</body>
</html>

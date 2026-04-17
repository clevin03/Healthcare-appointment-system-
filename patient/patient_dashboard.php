<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../login.php");
    exit();
}

$patient_name = isset($_SESSION['patient_name']) ? $_SESSION['patient_name'] : 'Test Patient';
$patient_email = isset($_SESSION['patient_email']) ? $_SESSION['patient_email'] : 'patient@edoc.com';
$patient_id = $_SESSION['user_id'];


$all_doctors = 1;
$all_patients = 2;
$new_bookings = 1;
$today_sessions = 0;

$upcoming_bookings = [
    [
        'appt_number' => 1,
        'session_title' => 'Test Session',
        'doctor' => 'Test Doctor',
        'scheduled_date_time' => '2050-01-01 18:00'
    ]
];

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
    <div class="container">
        <div class="sidebar">
            <div class="user-profile">
                <div class="user-avatar">
                    <span class="avatar-icon"><i class="fas fa-user"></i></span>
                </div>
                <div class="user-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($patient_name); ?></h3>
                    <p class="user-email"><?php echo htmlspecialchars($patient_email); ?></p>
                </div>
            </div>

            <a href="../logout.php" class="logout-btn">Log out</a>

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
                    <h3>Test Patient.</h3>
                    <p>Haven't any idea about doctors? no problem let's jumping to <strong>"All Doctors"</strong> section or <strong>"Sessions"</strong> Track your past and future appointments history.<br>
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
                <div class="banner-image">
                    <div class="image-placeholder"><i class="fas fa-heartbeat"></i></div>
                </div>
            </div>

            <div class="status-section">
                <h3>Status</h3>
                <div class="status-grid">
                    <div class="status-card">
                        <div class="status-number"><?php echo $all_doctors; ?></div>
                        <div class="status-label">All Doctors</div>
                        <div class="status-icon"><i class="fas fa-user-md"></i></div>
                    </div>

                    <div class="status-card">
                        <div class="status-number"><?php echo $all_patients; ?></div>
                        <div class="status-label">All Patients</div>
                        <div class="status-icon"><i class="fas fa-users"></i></div>
                    </div>

                    <div class="status-card">
                        <div class="status-number"><?php echo $new_bookings; ?></div>
                        <div class="status-label">NewBooking</div>
                        <div class="status-icon"><i class="fas fa-file-medical"></i></div>
                    </div>

                    <div class="status-card">
                        <div class="status-number"><?php echo $today_sessions; ?></div>
                        <div class="status-label">Today Sessions</div>
                        <div class="status-icon"><i class="fas fa-tv"></i></div>
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

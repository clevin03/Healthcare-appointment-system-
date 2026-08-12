<?php
session_start();

if (!isset($_SESSION['user_id']) && $_SESSION['user_type'] != 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db_connection.php';
$patient_name = isset($_SESSION['user_name']) ? $_SESSION['user_name'] : (isset($_SESSION['patient_name']) ? $_SESSION['patient_name'] : 'Test Patient');
$patient_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : 'patient@edoc.com';
$patient_id = $_SESSION['patient_id']; //?? $_SESSION['user_id'];

$sql = "SELECT s.session_id, s.doctor_id, d.doctor_name, s.session_day, s.start_time, s.end_time, s.max_patients, s.status, s.current_count 
        FROM sessions s JOIN doctors d ON s.doctor_id = d.doctor_id WHERE s.session_day >= CURDATE() AND s.status = 'active'";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$sessions = [];
if($result && $result->num_rows>0){
    while($row = $result->fetch_assoc()){
        $sessions[] = $row;
    }
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!--<link rel="stylesheet" href="static/patient_dashboard.css">-->
    <link rel="stylesheet" href="static/sessions.css">
    <link rel="stylesheet" href="static/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <title>Scheduled Sessions</title>
</head>
<body>
    <script src="static/sessions.js"></script>
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
                        <a href="patient_dashboard.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-home"></i></span>
                            <span class="menu-text">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="chatbot.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-robot"></i></span>
                            <span class="menu-text">Health Assistant</span>
                        </a>
                    </li>
                    <li>
                        <a href="allDoctors.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-user-md"></i></span>
                            <span class="menu-text">All Doctors</span>
                        </a>
                    </li>
                    <li>
                        <a href="sessions.php" class="menu-item active">
                            <span class="menu-icon"><i class="fas fa-calendar"></i></span>
                            <span class="menu-text">Scheduled Sessions</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_bookings.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="menu-text">My Bookings</span>
                        </a>
                    </li>
                    <li>
                        <a href="patientSettings.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-cog"></i></span>
                            <span class="menu-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <div class="header">
                <h1>Scheduled Sessions</h1>
            </div>
        
            <div class="sessions-section">
                <?php if(count($sessions)>0): ?>
                    <div class="session-grid">
                        <?php foreach ($sessions as $session): ?>
                            <div class="session-card">
                                <div class="session-info">
                                    <h3><?php echo htmlspecialchars($session['doctor_name']); ?></h3>
                                    <!--<p><strong>doctor ID:</strong> <?php echo htmlspecialchars($session['doctor_id']); ?></p> -->
                                    <p><strong>Date:</strong> <?php echo htmlspecialchars($session['session_day']); ?></p>
                                    <p><strong>Time:</strong> <?php echo htmlspecialchars(date('h:i A', strtotime($session['start_time']))); ?> - <?php echo htmlspecialchars(date('h:i A', strtotime($session['end_time']))); ?></p>
                                    <p><strong>Status:</strong> <span class="status-<?php echo strtolower(htmlspecialchars($session['status'])); ?>"><?php echo htmlspecialchars($session['status']); ?></span></p>
                                    <p><strong>Appointments:</strong> <?php echo htmlspecialchars($session['current_count']); ?> / <?php echo htmlspecialchars($session['max_patients']); ?></p>
                                </div>
                                <div class="session-actions">
                                    <?php if ($session['current_count'] >= $session['max_patients']): ?>
                                        <button class="book-btn disabled" disabled>Session Full</button>
                                    <?php else: ?>
                                        <form class="session-book-form">
                                            <input type="hidden" name="doctor_id" value="<?php echo $session['doctor_id']; ?>">
                                            <input type="hidden" name="session_id" value="<?php echo $session['session_id']; ?>">
                                            <button type="submit" class="book-btn">Book Now</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="no-sessions"><p>No scheduled sessions found.</p></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="model" id="sessionModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Your Appointment</h2>
                    <span class="close" onclick="closeModal()">&times;</span>
                </div>
                <div class="modal-body" id="modalBody">
                    <!-- Session details will be loaded here via JavaScript -->
                </div>
            </div>
        </div>
    </div>
</body>
</html>
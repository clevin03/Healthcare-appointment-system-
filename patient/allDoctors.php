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

$search_term = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get all doctors with their departments
$sql = "SELECT d.*, dep.department_name, u.email
        FROM doctors d 
        LEFT JOIN departments dep ON d.department_id = dep.department_id 
        LEFT JOIN users u ON d.user_id = u.user_id
        WHERE d.status = 'ACTIVE'";

if (!empty($search_term)) {
    $sql .= " AND d.doctor_name LIKE ?";
}
$sql .= " ORDER BY d.doctor_name ASC";

$stmt = $conn->prepare($sql);

if (!empty($search_term)) {
    $like_term = "%{$search_term}%";
    $stmt->bind_param('s', $like_term);
}

$stmt->execute();
$result = $stmt->get_result();
$doctors = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
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
    <title>All Doctors - Healthcare System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/patient_dashboard.css">
    <!--<link rel="stylesheet" href="static/main.css">-->
    <link rel="stylesheet" href="static/allDoctors.css">
    <link rel="stylesheet" href="static/sidebar.css">
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
        <a href="#home" class="active mobile-menu">Menu</a>
        <nav class="sidebar-menu" id="patientSidebarMenu">
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
        <a href="javascript:void(0);" class="icon mobile-menu" onclick="toggle()">
            <i class="fa fa-bars"></i>
        </a>
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
        <div class="search-section">
            <h4>Search for a Doctor</h4>
            <form class="search-form" method="get" action="allDoctors.php">
                <div class="search-box">
                    <span class="search-icon"><i class="fas fa-search"></i></span>
                    <input type="text" placeholder="Enter Doctor name" name="search" class="search-input" id="doctor-search" value="<?php echo htmlspecialchars($search_term); ?>">
                </div>
                <button type="submit" class="search-btn"><i class="fas fa-search"></i></button>
            </form>
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
                                <button type="button" class="book-btn book-appointment-btn" data-doctor-id="<?php echo (int) $doctor['doctor_id']; ?>" data-doctor-name="<?php echo htmlspecialchars($doctor['doctor_name'], ENT_QUOTES, 'UTF-8'); ?>">Book Appointment</button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="no-doctors" id="client-no-results" style="display: none;">
                    <i class="fas fa-user-slash fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                    <p>No doctors found matching your filter.</p>
                </div>
            <?php else: ?>
                <div class="no-doctors">
                    <i class="fas fa-user-slash fa-3x" style="color: #ccc; margin-bottom: 15px;"></i>
                    <p><?php echo !empty($search_term) ? 'No doctors found matching "' . htmlspecialchars($search_term) . '".' : 'No doctors found at the moment.'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="model" id="sessionModal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Available sessions</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Session details will be loaded here via JavaScript -->
            </div>
        </div>
    </div>
</div>
<script src="static/sidebar.js"></script>
<script src="static/allDoctors.js"></script>
<script src="static/patient_dashboard.js"></script>
</body>
</html>
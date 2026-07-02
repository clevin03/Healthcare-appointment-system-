<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'doctor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/db_connection.php';

$doctor_id = $_SESSION['doctor_id'] ?? 0;
$doctor_name = $_SESSION['user_name'] ?? 'Doctor';

$today_appointments = 0;
$pending_appointments = 0;
$upcoming_appointments = [];

if ($doctor_id > 0) {
    // Fetch stats for the doctor
    $sql_today_appointments = "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND DATE(appointment_date) = CURDATE()";
    $stmt = $conn->prepare($sql_today_appointments);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $today_appointments = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    $sql_pending_appointments = "SELECT COUNT(*) AS total FROM appointments WHERE doctor_id = ? AND status = 'PENDING'";
    $stmt = $conn->prepare($sql_pending_appointments);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $pending_appointments = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $stmt->close();

    // Fetch upcoming appointments
    $sql_upcoming = "SELECT a.appointment_number, a.appointment_date, a.appointment_time, a.status, p.first_name, p.last_name
                     FROM appointments a
                     JOIN patients p ON a.patient_id = p.patient_id
                     WHERE a.doctor_id = ? AND a.appointment_date >= CURDATE()
                     ORDER BY a.appointment_date ASC, a.appointment_time ASC
                     LIMIT 5";
    $stmt = $conn->prepare($sql_upcoming);
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $upcoming_appointments_result = $stmt->get_result();
    if ($upcoming_appointments_result->num_rows > 0) {
        while ($row = $upcoming_appointments_result->fetch_assoc()) {
            $upcoming_appointments[] = $row;
        }
    }
    $stmt->close();
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="../admin/static/dashboard.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-user-doctor"></i> Doctor's Portal</div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($doctor_name); ?></span>
                <form method="post" action="../auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" style="display:inline; margin:0;">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="#" class="active"><i class="fa-solid fa-chart-column"></i> Dashboard</a></li>
                <li><a href="#"><i class="fa-solid fa-calendar"></i> My Appointments</a></li>
                <li><a href="#"><i class="fa-solid fa-users"></i> My Patients</a></li>
                <li><a href="#"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1 class="page-title">Doctor Dashboard</h1>

            <div class="stats-container">
                <div class="stat-card green">
                    <div class="stat-label">Today's Appointments</div>
                    <div class="stat-number"><?php echo $today_appointments; ?></div>
                </div>
                <div class="stat-card red">
                    <div class="stat-label">Pending Appointments</div>
                    <div class="stat-number"><?php echo $pending_appointments; ?></div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title"><i class="fa-solid fa-calendar-days"></i> Upcoming Appointments</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Appointment ID</th>
                                <th>Patient Name</th>
                                <th>Date & Time</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($upcoming_appointments)): ?>
                                <?php foreach ($upcoming_appointments as $apt): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($apt['appointment_number']); ?></td>
                                    <td><?php echo htmlspecialchars($apt['first_name'] . ' ' . $apt['last_name']); ?></td>
                                    <td><?php echo date('M d, Y - h:i A', strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time'])); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower(htmlspecialchars($apt['status'])); ?>"><?php echo htmlspecialchars($apt['status']); ?></span></td>
                                    <td><div class="btn-group"><button class="btn btn-secondary">View</button></div></td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr><td colspan="5" style="text-align: center; padding: 20px; color: #999;">No upcoming appointments</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="footer">
                <p>&copy; <?php echo date('Y'); ?> BCI Healthcare Center. All rights reserved.</p>
            </div>
        </div>
    </div>
</body>
</html>
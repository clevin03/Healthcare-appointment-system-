<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/dashboard.css">
</head>
<body>
    <?php
    require_once '../config/db_connection.php';

    $sql = "SELECT COUNT(*) AS total_patients FROM patients";
    $sql_result = $conn->query($sql);
    $total_patients = $sql_result->fetch_assoc()['total_patients'];
    
    $sql = "SELECT COUNT(*) AS today_appointments FROM appointments WHERE DATE(appointment_date) = CURDATE()";
    $sql_result = $conn->query($sql);
    $today_appointments = $sql_result->fetch_assoc()['today_appointments'];

    $sql = "SELECT COUNT(*) AS active_doctors FROM doctors WHERE status = 'ACTIVE'";
    $sql_result = $conn->query($sql);
    $active_doctors = $sql_result->fetch_assoc()['active_doctors'];

    $sql = "SELECT COUNT(*) AS pending_appointments FROM appointments WHERE status = 'PENDING'";
    $sql_result = $conn->query($sql);
    $pending_appointments = $sql_result->fetch_assoc()['pending_appointments'];

    $sql = "SELECT a.appointment_id, a.appointment_number, a.appointment_date, a.appointment_time, a.status,
                   CONCAT(p.first_name, ' ', p.last_name) AS patient_name, d.doctor_name, dep.department_name
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
            LEFT JOIN departments dep ON a.department_id = dep.department_id
            WHERE a.appointment_date >= CURDATE()
            ORDER BY a.appointment_date ASC, a.appointment_time ASC
            LIMIT 5";
    $upcoming_appointments = [];
    $result = $conn->query($sql);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $upcoming_appointments[] = $row;
        }
    }

    $sql = "SELECT patient_id, CONCAT(first_name, ' ', last_name) AS patient_name, phone FROM patients ORDER BY created_at DESC LIMIT 5";
    $recent_patients = [];
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $recent_patients[] = $row;
        }
    }

    $sql = "SELECT d.doctor_id, d.doctor_name, dep.department_name, d.status
            FROM doctors d
            LEFT JOIN departments dep ON d.department_id = dep.department_id
            WHERE d.status = 'ACTIVE'
            LIMIT 5";
    $active_doctors_list = [];
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $active_doctors_list[] = $row;
        }
    }
    ?>

    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-hospital"></i> HealthCare Admin</div>
            <div class="user-info">
                <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Admin User'); ?></span>
                <form method="post" action="../auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" style="display:inline; margin:0;">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="#dashboard" class="active"><i class="fa-solid fa-chart-column"></i> Dashboard</a></li>
                <li><a href="appointment.php"><i class="fa-solid fa-calendar"></i> Appointments</a></li>
                <li><a href="sessions.php"><i class="fa-solid fa-clock"></i> Sessions</a></li>
                <li><a href="patient.php"><i class="fa-solid fa-users"></i> Patients</a></li>
                <li><a href="doctor.php"><i class="fa-solid fa-user-doctor"></i> Doctors</a></li>
                <li><a href="department.php"><i class="fa-solid fa-building"></i> Departments</a></li> <!-- New Departments link -->
                <li><a href="#reports"><i class="fa-solid fa-chart-line"></i> Reports</a></li> <!-- New Reports link -->
                <li><a href="settings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1 class="page-title">Dashboard</h1>

            <div class="stats-container">
                <div class="stat-card blue">
                    <div class="stat-label">Total Patients</div> <!-- Updated to show total patients -->
                    <div class="stat-number">
                        <?php echo isset($total_patients) ? $total_patients : '324'; ?>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-label">Today's Appointments</div> <!-- Updated to show today's appointments -->
                    <div class="stat-number">
                        <?php echo isset($today_appointments) ? $today_appointments : '12'; ?>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-label">Active Doctors</div> <!-- Updated to show active doctors -->
                    <div class="stat-number">
                        <?php echo isset($active_doctors) ? $active_doctors : '18'; ?>
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-label">Pending Appointments</div> <!-- Updated to show pending appointments -->
                    <div class="stat-number">
                        <?php echo isset($pending_appointments) ? $pending_appointments : '5'; ?>
                    </div>
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
                                <th>Doctor Name</th>
                                <th>Date & Time</th>
                                <th>Department</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if (!empty($upcoming_appointments)) {
                                foreach ($upcoming_appointments as $apt) {
                            ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($apt['appointment_number']); ?></td>
                                    <td><?php echo htmlspecialchars($apt['patient_name'] ?? $apt['first_name']); ?></td>
                                    <td><?php echo htmlspecialchars($apt['doctor_name']); ?></td>
                                    <td><?php echo date('M d, Y - h:i A', strtotime($apt['appointment_date'] . ' ' . $apt['appointment_time'])); ?></td>
                                    <td><?php echo htmlspecialchars($apt['department_name']); ?></td>
                                    <td><span class="badge badge-<?php echo strtolower($apt['status']); ?>"><?php echo htmlspecialchars($apt['status']); ?></span></td>
                                    <td>
                                        <div class="btn-group">
                                            <button class="btn btn-secondary" onclick="window.location.href='appointment.php'">View</button>
                                            <button class="btn btn-danger">Cancel</button>
                                        </div>
                                    </td>
                                </tr>
                            <?php
                                }
                            } else {
                            ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 20px; color: #999;">No upcoming appointments</td>
                                </tr>
                            <?php
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 15px;">
                    <a href="appointment.php" class="btn btn-primary" style="text-decoration: none;">View All Appointments</a>
                </div>
            </div>

            <div class="grid-2">
                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-users"></i> Recent Patients</h2> <!-- Updated section title to Recent Patients -->
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Patient ID</th>
                                    <th>Name</th>
                                    <th>Contact</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($recent_patients)) {
                                    foreach ($recent_patients as $patient) {
                                ?>
                                    <tr>
                                        <td>PAT<?php echo str_pad($patient['patient_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($patient['patient_name']); ?></td>
                                        <td><?php echo htmlspecialchars($patient['phone'] ?: 'N/A'); ?></td>
                                        <td><span class="badge badge-success">Active</span></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 20px; color: #999;">No patients found</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 15px;">
                        <a href="patient.php" class="btn btn-primary">View All Patients</a>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-user-doctor"></i> Doctor Status</h2> <!-- Updated section title to Doctor Status -->
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Doctor ID</th>
                                    <th>Name</th>
                                    <th>Specialty</th>
                                    <th>Availability</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                if (!empty($active_doctors_list)) {
                                    foreach ($active_doctors_list as $doctor) {
                                ?>
                                    <tr>
                                        <td>DOC<?php echo str_pad($doctor['doctor_id'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['doctor_name']); ?></td>
                                        <td><?php echo htmlspecialchars($doctor['department_name'] ?: 'N/A'); ?></td>
                                        <td><span class="badge badge-success">Available</span></td>
                                    </tr>
                                <?php
                                    }
                                } else {
                                ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; padding: 20px; color: #999;">No doctors found</td>
                                    </tr>
                                <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 15px;">
                        <a href="doctor.php" class="btn btn-primary">View All Doctors</a>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title"><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px;">
                    <button class="btn btn-secondary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-chart-bar"></i> Generate Report</button> <!-- New Generate Report button -->
                    <a href="message.php" class="btn btn-secondary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-envelope"></i> Send Message</a> <!--  Add New Send Message button -->
                    <button class="btn btn-secondary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-clipboard"></i> View Logs</button> <!-- New View Logs button -->
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2026 BCI Healthcare Center. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script src="static/dashboard.js"></script>
</body>
</html>
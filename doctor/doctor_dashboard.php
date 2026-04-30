<?php
session_start();

header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'doctor') {
    header('Location: /Healthcare-appointment-system-/auth/login.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/doctor_dashboard.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-hospital"></i> HealthCare Doctor</div>
            <div class="user-info">
                <span>Doctor User</span>
                <button onclick="logout()">Logout</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <div class="sidebar-header">
                <h3>Doctor Portal</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="#dashboard" class="active"><i class="fa-solid fa-chart-line"></i> Dashboard</a></li>
                <li><a href="#appointments"><i class="fa-solid fa-calendar-days"></i> My Appointments</a></li>
                <li><a href="#sessions"><i class="fa-solid fa-video"></i> My Sessions</a></li>
                <li><a href="#patients"><i class="fa-solid fa-users"></i> My Patients</a></li>
                <li><a href="#settings"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <div class="welcome-section">
                <div class="welcome-text">
                    <h2>Welcome!</h2>
                    <h3>Doctor Panel.</h3>
                    <p>Thanks for joining with us. We are always trying to get you a complete service<br>
                       You can view your daily schedule. Reach Patients Appointment at home!</p>
                    <button class="btn btn-primary"><i class="fa-solid fa-calendar-check"></i> View My Appointments</button>
                </div>
                <div class="welcome-image">
                    <img src="image/healthcare.webp" alt="Healthcare">
                </div>
            </div>

            <div class="status-section">
                <h3>Status</h3>
                <div class="status-grid">
                    <div class="status-card">
                        <div class="status-number">1</div>
                        <div class="status-label">All Doctors</div>
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>
                    <div class="status-card">
                        <div class="status-number">2</div>
                        <div class="status-label">All Patients</div>
                        <i class="fa-solid fa-user-injured"></i>
                    </div>
                    <div class="status-card">
                        <div class="status-number">1</div>
                        <div class="status-label">New Booking</div>
                        <i class="fa-solid fa-bookmark"></i>
                    </div>
                    <div class="status-card">
                        <div class="status-number">0</div>
                        <div class="status-label">Today Sessions</div>
                        <i class="fa-solid fa-hourglass-end"></i>
                    </div>
                </div>
            </div>

            <div class="sessions-section">
                <h3>Your Up Coming Sessions until Next week</h3>
                <table class="sessions-table">
                    <thead>
                        <tr>
                            <th>Session Title</th>
                            <th>Scheduled Date</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="3" class="no-data">
                                <div class="empty-state">
                                    <i class="fa-solid fa-inbox"></i>
                                    <p>We couldn't find anything related to your sessions</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <p>&copy; 2026 BCI Healthcare Center. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
            });
        });

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '/Healthcare-appointment-system-/auth/logout.php';
            }
        }
    </script>
</body>
</html>
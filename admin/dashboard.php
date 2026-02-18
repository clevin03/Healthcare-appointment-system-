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
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-hospital"></i> HealthCare Admin</div>
            <div class="user-info">
                <span>Admin User</span>
                <button onclick="logout()">Logout</button>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="#dashboard" class="active"><i class="fa-solid fa-chart-column"></i> Dashboard</a></li>
                <li><a href="#appointments"><i class="fa-solid fa-calendar"></i> Appointments</a></li>
                <li><a href="#patients"><i class="fa-solid fa-users"></i> Patients</a></li>
                <li><a href="#doctors"><i class="fa-solid fa-user-doctor"></i> Doctors</a></li>
                <li><a href="#departments"><i class="fa-solid fa-building"></i> Departments</a></li>
                <li><a href="#reports"><i class="fa-solid fa-chart-line"></i> Reports</a></li>
                <li><a href="#settings"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <h1 class="page-title">Dashboard</h1>

            <div class="stats-container">
                <div class="stat-card blue">
                    <div class="stat-label">Total Patients</div>
                    <div class="stat-number">
                        <?php echo isset($total_patients) ? $total_patients : '324'; ?>
                    </div>
                </div>

                <div class="stat-card green">
                    <div class="stat-label">Today's Appointments</div>
                    <div class="stat-number">
                        <?php echo isset($today_appointments) ? $today_appointments : '12'; ?>
                    </div>
                </div>

                <div class="stat-card orange">
                    <div class="stat-label">Active Doctors</div>
                    <div class="stat-number">
                        <?php echo isset($active_doctors) ? $active_doctors : '18'; ?>
                    </div>
                </div>

                <div class="stat-card red">
                    <div class="stat-label">Pending Appointments</div>
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
                            <tr>
                                <td>#APT001</td>
                                <td>John Doe</td>
                                <td>Dr. Sarah Johnson</td>
                                <td>Feb 18, 2026 - 10:30 AM</td>
                                <td>Cardiology</td>
                                <td><span class="badge badge-confirmed">Confirmed</span></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-secondary">Edit</button>
                                        <button class="btn btn-danger">Cancel</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>#APT002</td>
                                <td>Jane Smith</td>
                                <td>Dr. Michael Brown</td>
                                <td>Feb 18, 2026 - 2:00 PM</td>
                                <td>Orthopedic</td>
                                <td><span class="badge badge-pending">Pending</span></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-success">Confirm</button>
                                        <button class="btn btn-secondary">Edit</button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>#APT003</td>
                                <td>Robert Wilson</td>
                                <td>Dr. Emily Davis</td>
                                <td>Feb 19, 2026 - 11:00 AM</td>
                                <td>Neurology</td>
                                <td><span class="badge badge-confirmed">Confirmed</span></td>
                                <td>
                                    <div class="btn-group">
                                        <button class="btn btn-secondary">Edit</button>
                                        <button class="btn btn-danger">Cancel</button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div style="margin-top: 15px;">
                    <button class="btn btn-primary">View All Appointments</button>
                </div>
            </div>

            <div class="grid-2">
                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-users"></i> Recent Patients</h2>
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
                                <tr>
                                    <td>#PAT001</td>
                                    <td>Alice Cooper</td>
                                    <td>555-0101</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>#PAT002</td>
                                    <td>Bob Johnson</td>
                                    <td>555-0102</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                                <tr>
                                    <td>#PAT003</td>
                                    <td>Carol White</td>
                                    <td>555-0103</td>
                                    <td><span class="badge badge-success">Active</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary">View All Patients</button>
                    </div>
                </div>

                <div class="section">
                    <h2 class="section-title"><i class="fa-solid fa-user-doctor"></i> Doctor Status</h2>
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
                                <tr>
                                    <td>#DOC001</td>
                                    <td>Dr. Sarah Johnson</td>
                                    <td>Cardiology</td>
                                    <td><span class="badge badge-success">Available</span></td>
                                </tr>
                                <tr>
                                    <td>#DOC002</td>
                                    <td>Dr. Michael Brown</td>
                                    <td>Orthopedic</td>
                                    <td><span class="badge badge-success">Available</span></td>
                                </tr>
                                <tr>
                                    <td>#DOC003</td>
                                    <td>Dr. Emily Davis</td>
                                    <td>Neurology</td>
                                    <td><span class="badge badge-pending">In Session</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div style="margin-top: 15px;">
                        <button class="btn btn-primary">View All Doctors</button>
                    </div>
                </div>
            </div>

            <div class="section">
                <h2 class="section-title"><i class="fa-solid fa-bolt"></i> Quick Actions</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-top: 20px;">
                    <button class="btn btn-primary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-plus"></i> New Appointment</button>
                    <button class="btn btn-primary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-plus"></i> New Patient</button>
                    <button class="btn btn-primary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-plus"></i> New Doctor</button>
                    <button class="btn btn-secondary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-chart-bar"></i> Generate Report</button>
                    <button class="btn btn-secondary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-envelope"></i> Send Notifications</button>
                    <button class="btn btn-secondary" style="width: 100%; padding: 15px;"><i class="fa-solid fa-clipboard"></i> View Logs</button>
                </div>
            </div>

            <div class="footer">
                <p>&copy; 2026 BCI Healthcare Appointment System. All rights reserved.</p>
            </div>
        </div>
    </div>

    <script>
        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../login.php';
            }
        }

        document.querySelectorAll('.sidebar ul li a').forEach(link => {
            link.addEventListener('click', function() {
                document.querySelectorAll('.sidebar ul li a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
            });
        });
    </script>
</body>
</html>
<?php
// Dummy counts - replace with DB queries
$total_doctors  = 5;
$total_patients = 6;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doctor Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/doctor_dashboard.css">
    <style>
        .status-card.clickable {
            cursor: pointer;
            transition: transform 0.15s, box-shadow 0.15s;
        }
        .status-card.clickable:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 18px rgba(59,130,246,0.18);
        }
        .status-card.clickable.active {
            border: 2px solid #3b82f6;
            background: #eff6ff;
        }

        /* Patient list section */
        .list-section { margin: 24px 0; display: none; }
        .list-section.show { display: block; }
        .list-section h3 {
            font-size: 1.1rem; font-weight: 600; color: #1e293b;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }
        .list-section h3 i { color: #3b82f6; }

        .data-table {
            width: 100%; border-collapse: collapse; background: #fff;
            border-radius: 12px; overflow: hidden;
            box-shadow: 0 1px 4px rgba(0,0,0,0.07);
        }
        .data-table thead { background: #3b82f6; color: #fff; }
        .data-table thead th {
            padding: 12px 16px; text-align: left;
            font-size: 0.85rem; font-weight: 600; letter-spacing: 0.03em;
        }
        .data-table tbody tr { border-bottom: 1px solid #f1f5f9; transition: background 0.15s; }
        .data-table tbody tr:last-child { border-bottom: none; }
        .data-table tbody tr:hover { background: #f0f7ff; }
        .data-table tbody td { padding: 12px 16px; font-size: 0.9rem; color: #374151; vertical-align: middle; }

        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 0.85rem;
            margin-right: 10px; flex-shrink: 0;
        }
        .avatar-green { background: linear-gradient(135deg, #10b981, #059669); }
        .name-cell { display: flex; align-items: center; }

        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 500; }
        .badge-green { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-red   { background: #fff1f2; color: #dc2626; border: 1px solid #fecaca; }

        .status-stable    { color: #2563eb; font-weight: 600; font-size: 0.82rem; }
        .status-critical  { color: #dc2626; font-weight: 600; font-size: 0.82rem; }
        .status-recovered { color: #16a34a; font-weight: 600; font-size: 0.82rem; }

        .search-bar { display: flex; align-items: center; margin-bottom: 14px; position: relative; width: fit-content; }
        .search-bar input {
            padding: 8px 14px 8px 36px; border: 1px solid #e2e8f0;
            border-radius: 8px; font-size: 0.88rem; outline: none; width: 250px; transition: border 0.2s;
        }
        .search-bar input:focus { border-color: #3b82f6; }
        .search-bar i { position: absolute; left: 11px; color: #94a3b8; font-size: 0.85rem; }

        .back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            margin-bottom: 14px; padding: 6px 14px; background: #f1f5f9;
            border: none; border-radius: 8px; font-size: 0.85rem;
            color: #374151; cursor: pointer; transition: background 0.15s;
        }
        .back-btn:hover { background: #e2e8f0; }
    </style>
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
                <!-- ✅ Opens doctor list in new tab -->
                <li><a href="doctors_list.php" target="_blank"><i class="fa-solid fa-stethoscope"></i> All Doctors <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.7rem;margin-left:4px;"></i></a></li>
                <li><a href="javascript:void(0)" onclick="togglePatients()"><i class="fa-solid fa-users"></i> My Patients</a></li>
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

                    <!-- ✅ Click opens doctor list in new tab -->
                    <div class="status-card clickable" onclick="window.open('doctors_list.php', '_blank')">
                        <div class="status-number"><?= $total_doctors ?></div>
                        <div class="status-label">All Doctors</div>
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>

                    <div class="status-card clickable" id="cardPatients" onclick="togglePatients()">
                        <div class="status-number"><?= $total_patients ?></div>
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

            <!-- All Patients List -->
            <div class="list-section" id="sectionPatients">
                <button type="button" class="back-btn" onclick="togglePatients()">
                    <i class="fa-solid fa-arrow-left"></i> Hide Patient List
                </button>
                <h3><i class="fa-solid fa-user-injured"></i> All Patients</h3>
                <div class="search-bar">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <input type="text" id="patientSearch" placeholder="Search by name or condition..." oninput="filterTable('patientSearch','patientsBody')">
                </div>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient Name</th>
                            <th>Age</th>
                            <th>Condition</th>
                            <th>Assigned Doctor</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="patientsBody">
                        <?php
                        $patients = [
                            ['id'=>1,'initials'=>'JD','name'=>'John Doe','age'=>45,'condition'=>'Heart Disease','condition_type'=>'red','doctor'=>'Dr. Adam Johnson','status'=>'Stable'],
                            ['id'=>2,'initials'=>'MA','name'=>'Maria Ahmed','age'=>32,'condition'=>'Migraine','condition_type'=>'red','doctor'=>'Dr. Sarah Miller','status'=>'Stable'],
                            ['id'=>3,'initials'=>'SP','name'=>'Sam Perera','age'=>58,'condition'=>'Fracture','condition_type'=>'red','doctor'=>'Dr. Raj Kumar','status'=>'Critical'],
                            ['id'=>4,'initials'=>'LB','name'=>'Lily Brown','age'=>7,'condition'=>'Flu','condition_type'=>'green','doctor'=>'Dr. Emily Watson','status'=>'Recovered'],
                            ['id'=>5,'initials'=>'KN','name'=>'Kevin Nair','age'=>29,'condition'=>'Eczema','condition_type'=>'green','doctor'=>'Dr. Tom Lee','status'=>'Stable'],
                            ['id'=>6,'initials'=>'RP','name'=>'Rita Patel','age'=>51,'condition'=>'Diabetes','condition_type'=>'red','doctor'=>'Dr. Adam Johnson','status'=>'Stable'],
                        ];
                        foreach ($patients as $p):
                            $statusClass = match(strtolower($p['status'])) {
                                'stable'    => 'status-stable',
                                'critical'  => 'status-critical',
                                'recovered' => 'status-recovered',
                                default     => 'status-stable'
                            };
                        ?>
                        <tr>
                            <td><?= $p['id'] ?></td>
                            <td>
                                <div class="name-cell">
                                    <span class="avatar avatar-green"><?= htmlspecialchars($p['initials']) ?></span>
                                    <?= htmlspecialchars($p['name']) ?>
                                </div>
                            </td>
                            <td><?= $p['age'] ?></td>
                            <td><span class="badge badge-<?= $p['condition_type'] ?>"><?= htmlspecialchars($p['condition']) ?></span></td>
                            <td><?= htmlspecialchars($p['doctor']) ?></td>
                            <td>
                                <span class="<?= $statusClass ?>">
                                    <i class="fa-solid fa-circle" style="font-size:0.6rem"></i>
                                    <?= htmlspecialchars($p['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
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
                window.location.href = '../login.php';
            }
        }

        function togglePatients() {
            const section = document.getElementById('sectionPatients');
            const card    = document.getElementById('cardPatients');
            const isVisible = section.classList.contains('show');
            section.classList.toggle('show');
            card.classList.toggle('active');
            if (!isVisible) section.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function filterTable(inputId, bodyId) {
            const query = document.getElementById(inputId).value.toLowerCase();
            document.querySelectorAll('#' + bodyId + ' tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        }
    </script>
</body>
</html>
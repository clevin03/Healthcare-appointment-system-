<?php
// ─── Simulate logged-in doctor ───────────────────────────────────────────────
// In production: replace with session data, e.g. $_SESSION['doctor_id']
$logged_doctor_id   = 1;
$logged_doctor_name = 'Dr. Adam Johnson';

// ─── All doctors ─────────────────────────────────────────────────────────────
$all_doctors = [
    ['id' => 1, 'name' => 'Dr. Adam Johnson',  'specialty' => 'Cardiologist'],
    ['id' => 2, 'name' => 'Dr. Sarah Miller',  'specialty' => 'Neurologist'],
    ['id' => 3, 'name' => 'Dr. Raj Kumar',     'specialty' => 'Orthopaedist'],
    ['id' => 4, 'name' => 'Dr. Emily Watson',  'specialty' => 'Paediatrician'],
    ['id' => 5, 'name' => 'Dr. Tom Lee',       'specialty' => 'Dermatologist'],
];
$total_doctors = count($all_doctors);

// ─── All patients ─────────────────────────────────────────────────────────────
$all_patients = [
    ['id'=>1,'initials'=>'JD','name'=>'John Doe',    'age'=>45,'condition'=>'Heart Disease','condition_type'=>'red',  'doctor_id'=>1,'doctor'=>'Dr. Adam Johnson','status'=>'Stable'],
    ['id'=>2,'initials'=>'MA','name'=>'Maria Ahmed',  'age'=>32,'condition'=>'Migraine',     'condition_type'=>'red',  'doctor_id'=>2,'doctor'=>'Dr. Sarah Miller', 'status'=>'Stable'],
    ['id'=>3,'initials'=>'SP','name'=>'Sam Perera',   'age'=>58,'condition'=>'Fracture',      'condition_type'=>'red',  'doctor_id'=>3,'doctor'=>'Dr. Raj Kumar',    'status'=>'Critical'],
    ['id'=>4,'initials'=>'LB','name'=>'Lily Brown',   'age'=>7, 'condition'=>'Flu',           'condition_type'=>'green','doctor_id'=>4,'doctor'=>'Dr. Emily Watson', 'status'=>'Recovered'],
    ['id'=>5,'initials'=>'KN','name'=>'Kevin Nair',   'age'=>29,'condition'=>'Eczema',        'condition_type'=>'green','doctor_id'=>5,'doctor'=>'Dr. Tom Lee',      'status'=>'Stable'],
    ['id'=>6,'initials'=>'RP','name'=>'Rita Patel',   'age'=>51,'condition'=>'Diabetes',      'condition_type'=>'red',  'doctor_id'=>1,'doctor'=>'Dr. Adam Johnson','status'=>'Stable'],
    ['id'=>7,'initials'=>'AN','name'=>'Alice Ng',     'age'=>63,'condition'=>'Hypertension',  'condition_type'=>'red',  'doctor_id'=>1,'doctor'=>'Dr. Adam Johnson','status'=>'Stable'],
];

// Filter patients belonging to the logged-in doctor
$my_patients = array_filter($all_patients, fn($p) => $p['doctor_id'] === $logged_doctor_id);
$total_my_patients = count($my_patients);
$total_all_patients = count($all_patients);

// ─── All appointments ─────────────────────────────────────────────────────────
$all_appointments = [
    ['id'=>1,'patient_id'=>1,'patient'=>'John Doe',   'doctor_id'=>1,'date'=>'2026-05-09','time'=>'09:00 AM','type'=>'Follow-up',    'status'=>'Confirmed'],
    ['id'=>2,'patient_id'=>6,'patient'=>'Rita Patel',  'doctor_id'=>1,'date'=>'2026-05-09','time'=>'11:30 AM','type'=>'Consultation', 'status'=>'Confirmed'],
    ['id'=>3,'patient_id'=>7,'patient'=>'Alice Ng',    'doctor_id'=>1,'date'=>'2026-05-10','time'=>'02:00 PM','type'=>'Check-up',     'status'=>'Pending'],
    ['id'=>4,'patient_id'=>2,'patient'=>'Maria Ahmed', 'doctor_id'=>2,'date'=>'2026-05-09','time'=>'10:00 AM','type'=>'Consultation', 'status'=>'Confirmed'],
    ['id'=>5,'patient_id'=>1,'patient'=>'John Doe',   'doctor_id'=>1,'date'=>'2026-05-13','time'=>'09:30 AM','type'=>'Review',       'status'=>'Confirmed'],
    ['id'=>6,'patient_id'=>6,'patient'=>'Rita Patel',  'doctor_id'=>1,'date'=>'2026-05-15','time'=>'03:00 PM','type'=>'Follow-up',    'status'=>'Pending'],
];

// Filter appointments for logged-in doctor
$my_appointments = array_filter($all_appointments, fn($a) => $a['doctor_id'] === $logged_doctor_id);
$total_my_appointments = count($my_appointments);

// Today's appointments
$today = date('Y-m-d');
$today_appointments = array_filter($my_appointments, fn($a) => $a['date'] === $today);
$today_count = count($today_appointments);

// New / pending bookings
$pending_count = count(array_filter($my_appointments, fn($a) => strtolower($a['status']) === 'pending'));

// Sort appointments by date + time
usort($my_appointments, fn($a, $b) => strcmp($a['date'].$a['time'], $b['date'].$b['time']));
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
        /* ── Clickable cards ─────────────────────────────────────────── */
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

        /* ── Expandable list sections ────────────────────────────────── */
        .list-section { margin: 24px 0; display: none; }
        .list-section.show { display: block; }
        .list-section h3 {
            font-size: 1.1rem; font-weight: 600; color: #1e293b;
            margin-bottom: 16px; display: flex; align-items: center; gap: 8px;
        }
        .list-section h3 i { color: #3b82f6; }

        /* ── Tables ──────────────────────────────────────────────────── */
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

        /* ── Avatars ─────────────────────────────────────────────────── */
        .avatar {
            width: 36px; height: 36px; border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 700; font-size: 0.85rem;
            margin-right: 10px; flex-shrink: 0;
        }
        .avatar-green  { background: linear-gradient(135deg, #10b981, #059669); }
        .avatar-blue   { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .avatar-orange { background: linear-gradient(135deg, #f59e0b, #d97706); }
        .name-cell { display: flex; align-items: center; }

        /* ── Badges ──────────────────────────────────────────────────── */
        .badge { display: inline-block; padding: 3px 10px; border-radius: 20px; font-size: 0.78rem; font-weight: 500; }
        .badge-green  { background: #f0fdf4; color: #16a34a; border: 1px solid #bbf7d0; }
        .badge-red    { background: #fff1f2; color: #dc2626; border: 1px solid #fecaca; }
        .badge-blue   { background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe; }
        .badge-orange { background: #fffbeb; color: #d97706; border: 1px solid #fde68a; }

        /* ── Status text ─────────────────────────────────────────────── */
        .status-stable    { color: #2563eb; font-weight: 600; font-size: 0.82rem; }
        .status-critical  { color: #dc2626; font-weight: 600; font-size: 0.82rem; }
        .status-recovered { color: #16a34a; font-weight: 600; font-size: 0.82rem; }
        .status-confirmed { color: #16a34a; font-weight: 600; font-size: 0.82rem; }
        .status-pending   { color: #d97706; font-weight: 600; font-size: 0.82rem; }

        /* ── Search ──────────────────────────────────────────────────── */
        .search-bar { display: flex; align-items: center; margin-bottom: 14px; position: relative; width: fit-content; }
        .search-bar input {
            padding: 8px 14px 8px 36px; border: 1px solid #e2e8f0;
            border-radius: 8px; font-size: 0.88rem; outline: none; width: 280px; transition: border 0.2s;
        }
        .search-bar input:focus { border-color: #3b82f6; }
        .search-bar i { position: absolute; left: 11px; color: #94a3b8; font-size: 0.85rem; }

        /* ── Back / hide button ──────────────────────────────────────── */
        .back-btn {
            display: inline-flex; align-items: center; gap: 6px;
            margin-bottom: 14px; padding: 6px 14px; background: #f1f5f9;
            border: none; border-radius: 8px; font-size: 0.85rem;
            color: #374151; cursor: pointer; transition: background 0.15s;
        }
        .back-btn:hover { background: #e2e8f0; }

        /* ── Tab bar for Appointments ────────────────────────────────── */
        .tab-bar { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
        .tab-btn {
            padding: 6px 18px; border-radius: 20px; border: 1px solid #e2e8f0;
            font-size: 0.83rem; font-weight: 500; cursor: pointer;
            background: #f8fafc; color: #64748b; transition: all 0.15s;
        }
        .tab-btn:hover { background: #eff6ff; border-color: #93c5fd; color: #2563eb; }
        .tab-btn.active { background: #3b82f6; border-color: #3b82f6; color: #fff; }

        /* ── Today highlight row ─────────────────────────────────────── */
        .row-today { background: #fefce8 !important; }
        .row-today:hover { background: #fef9c3 !important; }

        /* ── Empty state ─────────────────────────────────────────────── */
        .empty-row td {
            text-align: center; padding: 32px; color: #94a3b8;
            font-size: 0.9rem;
        }
        .empty-row td i { display: block; font-size: 2rem; margin-bottom: 8px; color: #cbd5e1; }

        /* ── Section heading with count pill ────────────────────────── */
        .section-title {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 16px; flex-wrap: wrap; gap: 10px;
        }
        .section-title h3 { margin: 0; font-size: 1.1rem; font-weight: 600; color: #1e293b; display: flex; align-items: center; gap: 8px; }
        .section-title h3 i { color: #3b82f6; }
        .count-pill {
            background: #eff6ff; color: #2563eb; border: 1px solid #bfdbfe;
            border-radius: 20px; padding: 2px 12px; font-size: 0.8rem; font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-hospital"></i> HealthCare Doctor</div>
            <div class="user-info">
                <span><?= htmlspecialchars($logged_doctor_name) ?></span>
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
                <li><a href="javascript:void(0)" onclick="showSection('appointments')"><i class="fa-solid fa-calendar-days"></i> My Appointments</a></li>
                <li><a href="#sessions"><i class="fa-solid fa-video"></i> My Sessions</a></li>
                <li><a href="doctors_list.php" target="_blank"><i class="fa-solid fa-stethoscope"></i> All Doctors <i class="fa-solid fa-arrow-up-right-from-square" style="font-size:0.7rem;margin-left:4px;"></i></a></li>
                <li><a href="javascript:void(0)" onclick="showSection('patients')"><i class="fa-solid fa-users"></i> My Patients</a></li>
                <li><a href="#settings"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <div class="main-content">
            <!-- Welcome banner -->
            <div class="welcome-section">
                <div class="welcome-text">
                    <h2>Welcome back!</h2>
                    <h3><?= htmlspecialchars($logged_doctor_name) ?></h3>
                    <p>You have <strong><?= $today_count ?></strong> appointment<?= $today_count !== 1 ? 's' : '' ?> today
                       and <strong><?= $pending_count ?></strong> pending booking<?= $pending_count !== 1 ? 's' : '' ?> awaiting confirmation.</p>
                    <button class="btn btn-primary" onclick="showSection('appointments')">
                        <i class="fa-solid fa-calendar-check"></i> View My Appointments
                    </button>
                </div>
                <div class="welcome-image">
                    <img src="image/healthcare.webp" alt="Healthcare">
                </div>
            </div>

            <!-- Status cards -->
            <div class="status-section">
                <h3>Status</h3>
                <div class="status-grid">

                    <div class="status-card clickable" onclick="window.open('doctors_list.php', '_blank')">
                        <div class="status-number"><?= $total_doctors ?></div>
                        <div class="status-label">All Doctors</div>
                        <i class="fa-solid fa-stethoscope"></i>
                    </div>

                    <div class="status-card clickable" id="cardPatients" onclick="showSection('patients')">
                        <div class="status-number"><?= $total_my_patients ?></div>
                        <div class="status-label">My Patients</div>
                        <i class="fa-solid fa-user-injured"></i>
                    </div>

                    <div class="status-card clickable" id="cardAppointments" onclick="showSection('appointments')">
                        <div class="status-number"><?= $pending_count ?></div>
                        <div class="status-label">New Bookings</div>
                        <i class="fa-solid fa-bookmark"></i>
                    </div>

                    <div class="status-card clickable" onclick="showSection('appointments', 'today')">
                        <div class="status-number"><?= $today_count ?></div>
                        <div class="status-label">Today Sessions</div>
                        <i class="fa-solid fa-hourglass-end"></i>
                    </div>

                </div>
            </div>

            <!-- ════════════════════════════════════════════════════════
                 MY APPOINTMENTS SECTION
            ═════════════════════════════════════════════════════════ -->
            <div class="list-section" id="sectionAppointments">
                <button type="button" class="back-btn" onclick="hideSection('appointments')">
                    <i class="fa-solid fa-arrow-left"></i> Hide Appointments
                </button>

                <div class="section-title">
                    <h3><i class="fa-solid fa-calendar-days"></i> My Appointments</h3>
                    <span class="count-pill"><?= $total_my_appointments ?> total</span>
                </div>

                <div style="display:flex; gap:12px; flex-wrap:wrap; align-items:center; margin-bottom:14px;">
                    <!-- Tab filter -->
                    <div class="tab-bar" style="margin-bottom:0">
                        <button class="tab-btn active" onclick="filterAppointmentTab(this,'all')">All</button>
                        <button class="tab-btn" onclick="filterAppointmentTab(this,'today')">Today</button>
                        <button class="tab-btn" onclick="filterAppointmentTab(this,'upcoming')">Upcoming</button>
                        <button class="tab-btn" onclick="filterAppointmentTab(this,'pending')">Pending</button>
                    </div>
                    <!-- Search -->
                    <div class="search-bar" style="margin-bottom:0">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text" id="apptSearch" placeholder="Search patient or type..." oninput="filterTable('apptSearch','appointmentsBody')">
                    </div>
                </div>

                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Type</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="appointmentsBody">
                        <?php if (empty($my_appointments)): ?>
                        <tr class="empty-row">
                            <td colspan="6">
                                <i class="fa-solid fa-calendar-xmark"></i>
                                No appointments found.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($my_appointments as $a):
                            $isToday   = $a['date'] === $today;
                            $rowClass  = $isToday ? 'row-today' : '';
                            $statusCls = strtolower($a['status']) === 'confirmed' ? 'status-confirmed' : 'status-pending';
                            $badgeCls  = strtolower($a['type']) === 'follow-up' ? 'badge-blue'
                                       : (strtolower($a['type']) === 'consultation' ? 'badge-red'
                                       : (strtolower($a['type']) === 'check-up'    ? 'badge-green'
                                       : 'badge-orange'));
                            $initials  = implode('', array_map(fn($w) => $w[0], explode(' ', $a['patient'])));
                            $dateLabel = $isToday ? 'Today' : date('d M Y', strtotime($a['date']));
                        ?>
                        <tr class="<?= $rowClass ?>"
                            data-date="<?= $a['date'] ?>"
                            data-status="<?= strtolower($a['status']) ?>">
                            <td><?= $a['id'] ?></td>
                            <td>
                                <div class="name-cell">
                                    <span class="avatar avatar-blue"><?= htmlspecialchars(substr($initials,0,2)) ?></span>
                                    <?= htmlspecialchars($a['patient']) ?>
                                    <?php if ($isToday): ?>
                                        <span class="badge badge-orange" style="margin-left:8px;font-size:0.72rem;">Today</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($dateLabel) ?></td>
                            <td><?= htmlspecialchars($a['time']) ?></td>
                            <td><span class="badge <?= $badgeCls ?>"><?= htmlspecialchars($a['type']) ?></span></td>
                            <td>
                                <span class="<?= $statusCls ?>">
                                    <i class="fa-solid fa-circle" style="font-size:0.6rem"></i>
                                    <?= htmlspecialchars($a['status']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- ════════════════════════════════════════════════════════
                 MY PATIENTS SECTION
            ═════════════════════════════════════════════════════════ -->
            <div class="list-section" id="sectionPatients">
                <button type="button" class="back-btn" onclick="hideSection('patients')">
                    <i class="fa-solid fa-arrow-left"></i> Hide Patient List
                </button>

                <div class="section-title">
                    <h3><i class="fa-solid fa-user-injured"></i> My Patients</h3>
                    <span class="count-pill"><?= $total_my_patients ?> patients</span>
                </div>

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
                            <th>Status</th>
                            <th>Next Appointment</th>
                        </tr>
                    </thead>
                    <tbody id="patientsBody">
                        <?php if (empty($my_patients)): ?>
                        <tr class="empty-row">
                            <td colspan="6">
                                <i class="fa-solid fa-user-slash"></i>
                                No patients assigned to you yet.
                            </td>
                        </tr>
                        <?php else: ?>
                        <?php foreach ($my_patients as $p):
                            $statusClass = match(strtolower($p['status'])) {
                                'stable'    => 'status-stable',
                                'critical'  => 'status-critical',
                                'recovered' => 'status-recovered',
                                default     => 'status-stable'
                            };
                            // Find next appointment for this patient
                            $nextAppt = null;
                            foreach ($my_appointments as $a) {
                                if ($a['patient_id'] === $p['id'] && $a['date'] >= $today) {
                                    $nextAppt = $a;
                                    break; // already sorted ascending
                                }
                            }
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
                            <td>
                                <span class="<?= $statusClass ?>">
                                    <i class="fa-solid fa-circle" style="font-size:0.6rem"></i>
                                    <?= htmlspecialchars($p['status']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($nextAppt): ?>
                                    <span style="font-size:0.85rem;color:#374151;">
                                        <i class="fa-regular fa-calendar" style="color:#3b82f6;margin-right:4px;"></i>
                                        <?= $nextAppt['date'] === $today ? 'Today' : date('d M Y', strtotime($nextAppt['date'])) ?>
                                        &nbsp;<?= htmlspecialchars($nextAppt['time']) ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color:#94a3b8;font-size:0.83rem;">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Upcoming sessions (existing) -->
            <div class="sessions-section">
                <h3>Your Upcoming Sessions until Next Week</h3>
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
                                    <p>No sessions scheduled yet.</p>
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
        // ── Sidebar active link ───────────────────────────────────────
        document.querySelectorAll('.sidebar-menu a').forEach(link => {
            link.addEventListener('click', function () {
                document.querySelectorAll('.sidebar-menu a').forEach(a => a.classList.remove('active'));
                this.classList.add('active');
            });
        });

        function logout() {
            if (confirm('Are you sure you want to logout?')) {
                window.location.href = '../login.php';
            }
        }

        // ── Section visibility helpers ────────────────────────────────
        const sectionMap = {
            patients:     { section: 'sectionPatients',     card: 'cardPatients' },
            appointments: { section: 'sectionAppointments', card: 'cardAppointments' },
        };

        function showSection(key, tab) {
            const m = sectionMap[key];
            if (!m) return;
            const sec  = document.getElementById(m.section);
            const card = document.getElementById(m.card);
            sec.classList.add('show');
            if (card) card.classList.add('active');
            sec.scrollIntoView({ behavior: 'smooth', block: 'start' });

            // If a tab filter is requested, activate it
            if (tab && key === 'appointments') {
                const btn = document.querySelector(`.tab-btn[onclick*="'${tab}'"]`);
                if (btn) filterAppointmentTab(btn, tab);
            }
        }

        function hideSection(key) {
            const m = sectionMap[key];
            if (!m) return;
            document.getElementById(m.section).classList.remove('show');
            const card = document.getElementById(m.card);
            if (card) card.classList.remove('active');
        }

        // ── Text search filter ────────────────────────────────────────
        function filterTable(inputId, bodyId) {
            const query = document.getElementById(inputId).value.toLowerCase();
            document.querySelectorAll('#' + bodyId + ' tr').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(query) ? '' : 'none';
            });
        }

        // ── Appointments tab filter ───────────────────────────────────
        function filterAppointmentTab(btn, filter) {
            // Update active tab button
            document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const today = new Date().toISOString().slice(0, 10);
            document.querySelectorAll('#appointmentsBody tr').forEach(row => {
                if (!row.dataset.date) { row.style.display = ''; return; } // empty-row

                const rowDate   = row.dataset.date;
                const rowStatus = row.dataset.status;

                let show = false;
                if (filter === 'all')      show = true;
                else if (filter === 'today')    show = rowDate === today;
                else if (filter === 'upcoming') show = rowDate >= today;
                else if (filter === 'pending')  show = rowStatus === 'pending';

                row.style.display = show ? '' : 'none';
            });
        }
    </script>
</body>
</html>
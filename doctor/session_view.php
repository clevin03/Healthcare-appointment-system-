<?php
session_start();

// Ensure the user is a logged-in doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

$doctorId = $_SESSION['doctor_id'];
require_once '../config/db_connection.php';

$session_id = isset($_GET['session_id']) ? (int)$_GET['session_id'] : 0;

if ($session_id <= 0) {
    die("Session ID is required.");
}

// Fetch session details
$stmt = $conn->prepare("SELECT s.session_day, s.start_time, s.end_time, s.max_patients FROM sessions s WHERE s.session_id = ? AND s.doctor_id = ?");
$stmt->bind_param("ii", $session_id, $doctorId);
$stmt->execute();
$session_result = $stmt->get_result();
$session = $session_result->fetch_assoc();
$stmt->close();

if (!$session) {
    die("Session not found or you do not have permission to view it. <a href='doctor_dashboard.php'>Go back</a>");
}

// Fetch appointments for this session
$appointments = [];
$sql = "SELECT a.appointment_id, a.appointment_number, a.status, a.patient_id, CONCAT(p.first_name, ' ', p.last_name) AS patient_name, u.email AS patient_email
        FROM appointments a 
        JOIN patients p ON a.patient_id = p.patient_id 
        JOIN users u ON p.user_id = u.user_id
        WHERE a.session_id = ? 
        ORDER BY a.appointment_number ASC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $session_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $appointments[] = $row;
}
$stmt->close();
$conn->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Details</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="static/main.css">
    <link rel="stylesheet" href="static/session_view.css"> 
    
</head>
<body>
    <script src="static/session_view.js"></script>
    <div class="main-content" style="padding: 20px; max-width: 1200px; margin: auto;">
        <input type="hidden" id="sessionDoctorId" value="<?php echo htmlspecialchars($doctorId); ?>">
        <div class="section">
            <div class="session-header">
                <h2 class="section-title" style="margin-bottom: 0;">
                    <i class="fas fa-calendar-check"></i> Session on <?php echo htmlspecialchars($session['session_day']); ?>
                </h2>
            </div>
            <p><strong>Time:</strong> <?php echo htmlspecialchars($session['start_time']) . ' - ' . htmlspecialchars($session['end_time']); ?></p>
            <p><strong>Bookings:</strong> <?php echo count($appointments); ?> / <?php echo htmlspecialchars($session['max_patients']); ?></p>
        </div>

        <div class="section">
            <h2 class="section-title"><i class="fas fa-users"></i> Appointments</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Appt. Number</th>
                            <th>Patient Name</th>
                            <th>Patient Email</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($appointments)): ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No appointments booked for this session yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appointment['appointment_number']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['patient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['patient_email']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['status']); ?></td>
                                    <td>
                                        <div class="btn-group">
                                            <form action="medical_history.php" class="medical-history" method="POST">
                                                <input type="hidden" name="patient_name" value="<?php echo $appointment['patient_name'] ?>">
                                                <input type="hidden" name="patient_id" value="<?php echo $appointment['patient_id']; ?>">
                                                <button type="submit" class="btn" >Medical History</button>
                                            </form>
                                            <form action="" class="medical-report-form">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                <input type="hidden" name="appointment_number" value="<?php echo $appointment['appointment_number']; ?>">
                                                <input type="hidden" name="patient_id" value="<?php echo $appointment['patient_id']; ?>">
                                                <button type="submit" class="btn btn-info open-report-modal" >Medical Report</button>
                                            </form>
                                            
                                            <form action="update_appointment_status.php" method="POST">
                                                <input type="hidden" name="appointment_id" value="<?php echo $appointment['appointment_id']; ?>">
                                                <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                                                <input type="hidden" name="new_status" value="COMPLETED">
                                                <button type="submit" class="btn btn-success">Mark as Completed</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Medical Report Modal -->
        <div id="medicalReportModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Create Medical Record</h2>
                    <span class="close-btn">&times;</span>
                </div>
                
                <form id="medicalReportForm" >
                    <!--
                    <div class="modal-body">
                        <input type="hidden" name="appointment_id" id="modalAppointmentId">
                        <input type="hidden" name="session_id" value="<?php echo $session_id; ?>">
                        <div class="form-group">
                            <label for="diagnosis">Diagnosis</label>
                            <textarea id="diagnosis" name="diagnosis" class="form-control" rows="4" required></textarea>
                        </div>
                        <div class="form-group">
                            <label for="prescription">Prescription</label>
                            <textarea id="prescription" name="prescription" class="form-control" rows="4"></textarea>
                        </div>
                        <div class="form-group">
                            <label for="notes">Notes</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary close-btn">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Record</button>
                    </div>-->
                </form>
            </div>
        </div>
    </div>

</body>
</html>
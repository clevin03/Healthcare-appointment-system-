<?php
session_start();

// Ensure the user is a logged-in doctor
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db_connection.php';

$patientId = $_POST['patient_id'];
$patientName = $_POST['patient_name'];

$stmt = $conn->prepare("SELECT d.doctor_name, m.date, m.diagnosis, m.prescription, m.notes 
                        FROM medical_records m JOIN doctors d ON m.doctor_id = d.doctor_id WHERE m.patient_id = ? ORDER BY m.date DESC");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$reportResults = $stmt->get_result();
$medicalReports = $reportResults->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient's History</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="static/main.css">
    <link rel="stylesheet" href="static/medical_history.css">
</head>
<body>
    <div class="main-content" style="padding: 20px; max-width: 1200px; margin: auto;">
        <div class="section">
            <div class="session-header">
                <h2 class="section-title" style="margin-bottom: 0;">
                    <i class="fas fa-calendar-check"></i> Patient: <?php echo htmlspecialchars($patientName); ?>
                </h2>
            </div>
        </div> 
        <div class="grid" id="record-grid">
        <?php
            if (count($medicalReports) > 0) {
                foreach ($medicalReports as $report) {
                    ?>
                    <div class="record-card">
                        <div class="card-header">
                            <span class="date"><?php echo htmlspecialchars(strval($report['date'])); ?></span>
                        </div>
                        <div class="card-body">
                            <div class="info-row">
                                <div class="info-content">
                                    <label>Doctor Name</label>
                                    <span><?php echo htmlspecialchars($report['doctor_name']); ?></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-content">
                                    <label>Diagnosis</label>
                                    <span><?php echo htmlspecialchars($report['diagnosis']); ?></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-content">
                                    <label>Prescription</label>
                                    <span><?php echo htmlspecialchars($report['prescription']); ?></span>
                                </div>
                            </div>
                            <div class="info-row">
                                <div class="info-content">
                                    <label>Notes</label>
                                    <span><?php echo htmlspecialchars($report['notes']); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
            ?>
            <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 15px;">
                    <i class="fas fa-calendar-times" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <h3 style="color: #666;">No record found</h3>
                    <p style="color: #999;">Go back to session page.</p>
                </div>
            <?php
            }
        ?>
    </div>
    </div>
    
</body>
</html>
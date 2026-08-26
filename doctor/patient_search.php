<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'doctor') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/db_connection.php';

$doctorId = (int) ($_SESSION['doctor_id'] ?? 0);
$doctorName = $_SESSION['user_name'] ?? 'Doctor';
$phone = trim($_GET['phone'] ?? '');
$patient = null;
$error = '';

if ($phone !== '') {
    if (!preg_match('/^[0-9+()\s-]+$/', $phone)) {
        $error = 'Enter a valid patient phone number.';
    } elseif ($doctorId <= 0) {
        $error = 'Doctor account details are unavailable.';
    } else {
        $sql = "SELECT p.patient_id, p.first_name, p.last_name, p.phone, p.gender,
                       p.date_of_birth, p.address, u.email,
                       COUNT(DISTINCT a.appointment_id) AS appointment_count
                FROM patients p
                LEFT JOIN users u ON p.user_id = u.user_id
                INNER JOIN appointments a ON a.patient_id = p.patient_id
                WHERE a.doctor_id = ? AND p.phone = ?
                GROUP BY p.patient_id, p.first_name, p.last_name, p.phone,
                         p.gender, p.date_of_birth, p.address, u.email
                LIMIT 1";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param('is', $doctorId, $phone);
            $stmt->execute();
            $patient = $stmt->get_result()->fetch_assoc();
            $stmt->close();
        } else {
            $error = 'Unable to search for the patient right now.';
        }
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Find Patient - Doctor Portal</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css">
    <link rel="stylesheet" href="static/main.css">
    <link rel="stylesheet" href="static/patient_search.css">
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-user-doctor"></i> Doctor's Portal</div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($doctorName); ?></span>
                <form method="post" action="../auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" style="display:inline; margin:0;">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="doctor_dashboard.php"><i class="fa-solid fa-chart-column"></i> Dashboard</a></li>
                <!--<li><a href="#"><i class="fa-solid fa-calendar"></i> My Sessions</a></li>-->
                <li><a href="patient_search.php" class="active"><i class="fa-solid fa-users"></i> Find Patient</a></li>
                <li><a href="doctorSettings.php"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>

        <main class="main-content">
            <h1 class="page-title"><i class="fa-solid fa-magnifying-glass"></i> Find Patient</h1>
            <section class="section search-section">
                <h2 class="section-title"><i class="fa-solid fa-phone"></i> Search by phone number</h2>
                <form method="get" action="patient_search.php" class="search-form">
                    <label for="phone">Patient phone number</label>
                    <div class="search-controls">
                        <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>" placeholder="Enter phone number" autocomplete="tel" required>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-search"></i> Search</button>
                    </div>
                </form>
                <?php if ($error !== ''): ?>
                    <p class="search-message error"><?php echo htmlspecialchars($error); ?></p>
                <?php elseif ($phone !== '' && !$patient): ?>
                    <p class="search-message">No patient with that phone number was found among your appointments.</p>
                <?php endif; ?>
            </section>

            <?php if ($patient): ?>
                <section class="section patient-result">
                    <div class="patient-heading">
                        <div class="patient-avatar"><i class="fa-solid fa-user"></i></div>
                        <div>
                            <h2><?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name']); ?></h2>
                            <p><?php echo (int) $patient['appointment_count']; ?> appointment(s) with you</p>
                        </div>
                    </div>
                    <div class="patient-details">
                        <div><strong>Phone</strong><span><?php echo htmlspecialchars($patient['phone'] ?? 'N/A'); ?></span></div>
                        <div><strong>Email</strong><span><?php echo htmlspecialchars($patient['email'] ?? 'N/A'); ?></span></div>
                        <div><strong>Date of birth</strong><span><?php echo htmlspecialchars($patient['date_of_birth'] ?? 'N/A'); ?></span></div>
                        <div><strong>Gender</strong><span><?php echo htmlspecialchars($patient['gender'] ?? 'N/A'); ?></span></div>
                        <div><strong>Address</strong><span><?php echo htmlspecialchars($patient['address'] ?? 'N/A'); ?></span></div>
                    </div>
                    <form method="post" action="medical_history.php" class="history-form">
                        <input type="hidden" name="patient_id" value="<?php echo (int) $patient['patient_id']; ?>">
                        <input type="hidden" name="patient_name" value="<?php echo htmlspecialchars($patient['first_name'] . ' ' . $patient['last_name'], ENT_QUOTES, 'UTF-8'); ?>">
                        <button type="submit" class="btn btn-secondary"><i class="fa-solid fa-file-medical"></i> View medical history</button>
                    </form>
                </section>
            <?php endif; ?>
        </main>
    </div>
</body>
</html>

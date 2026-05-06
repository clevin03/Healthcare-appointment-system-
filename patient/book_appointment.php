<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'patient') {
    header('Location: ../auth/login.php');
    exit();
}

require_once '../config/db_connection.php';

$patientName = (string) ($_SESSION['patient_name'] ?? ($_SESSION['user_name'] ?? ''));
$normalizedInitialName = strtolower(trim($patientName));
if ($normalizedInitialName === 'patient' || $normalizedInitialName === 'test patient') {
    $patientName = '';
}
$patientEmail = (string) ($_SESSION['patient_email'] ?? ($_SESSION['user_email'] ?? ''));
$patientPhone = '';
$patientId = 0;
$successMessage = '';
$errorMessage = '';

if ($patientName !== '') {
    $findByName = $conn->prepare('SELECT patient_id, patient_name, email, phone FROM patients WHERE patient_name = ? LIMIT 1');
    if ($findByName) {
        $findByName->bind_param('s', $patientName);
        $findByName->execute();
        $result = $findByName->get_result();
        if ($row = $result->fetch_assoc()) {
            $patientId = (int) $row['patient_id'];
            $patientName = (string) ($row['patient_name'] ?? $patientName);
            $normalizedLoadedName = strtolower(trim($patientName));
            if ($normalizedLoadedName === 'patient' || $normalizedLoadedName === 'test patient') {
                $patientName = '';
            }
            $patientEmail = (string) ($row['email'] ?? $patientEmail);
            $patientPhone = (string) ($row['phone'] ?? '');
        }
        $findByName->close();
    }
}

if ($patientId <= 0 && $patientEmail !== '') {
    $findByEmail = $conn->prepare('SELECT patient_id, patient_name, email, phone FROM patients WHERE email = ? LIMIT 1');
    if ($findByEmail) {
        $findByEmail->bind_param('s', $patientEmail);
        $findByEmail->execute();
        $result = $findByEmail->get_result();
        if ($row = $result->fetch_assoc()) {
            $patientId = (int) $row['patient_id'];
            $patientName = (string) ($row['patient_name'] ?? $patientName);
            $normalizedLoadedName = strtolower(trim($patientName));
            if ($normalizedLoadedName === 'patient' || $normalizedLoadedName === 'test patient') {
                $patientName = '';
            }
            $patientEmail = (string) ($row['email'] ?? $patientEmail);
            $patientPhone = (string) ($row['phone'] ?? '');
        }
        $findByEmail->close();
    }
}

$departments = [];
$departmentQuery = $conn->query("SELECT department_id, department_name FROM departments WHERE is_active = 1 ORDER BY department_name ASC");
if ($departmentQuery) {
    while ($row = $departmentQuery->fetch_assoc()) {
        $departments[] = $row;
    }
}

$doctors = [];
$doctorQuery = $conn->query("SELECT d.doctor_id, d.doctor_name, d.department_id, dep.department_name FROM doctors d LEFT JOIN departments dep ON d.department_id = dep.department_id WHERE d.status = 'ACTIVE' ORDER BY d.doctor_name ASC");
if ($doctorQuery) {
    while ($row = $doctorQuery->fetch_assoc()) {
        $doctors[] = $row;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submittedPatientName = trim((string) ($_POST['patient_name'] ?? ''));
    $departmentId = (int) ($_POST['department_id'] ?? 0);
    $doctorId = (int) ($_POST['doctor_id'] ?? 0);
    $appointmentDate = trim((string) ($_POST['appointment_date'] ?? ''));
    $appointmentTime = trim((string) ($_POST['appointment_time'] ?? ''));

    if ($submittedPatientName !== '') {
        $patientName = $submittedPatientName;
    }

    if ($patientName === '' || $departmentId <= 0 || $doctorId <= 0 || $appointmentDate === '' || $appointmentTime === '') {
        $errorMessage = 'Please fill all fields before booking.';
    } else {
        if ($patientId <= 0) {
            $insertPatient = $conn->prepare('INSERT INTO patients (patient_name, email, phone) VALUES (?, ?, ?)');
            if ($insertPatient) {
                $insertPatient->bind_param('sss', $patientName, $patientEmail, $patientPhone);
                if ($insertPatient->execute()) {
                    $patientId = (int) $conn->insert_id;
                }
                $insertPatient->close();
            }
        } else {
            $updatePatient = $conn->prepare('UPDATE patients SET patient_name = ? WHERE patient_id = ?');
            if ($updatePatient) {
                $updatePatient->bind_param('si', $patientName, $patientId);
                $updatePatient->execute();
                $updatePatient->close();
            }
        }

        if ($patientId <= 0) {
            $errorMessage = 'Unable to load patient profile for booking.';
        } else {
            $doctorCheck = $conn->prepare("SELECT doctor_id FROM doctors WHERE doctor_id = ? AND department_id = ? AND status = 'ACTIVE' LIMIT 1");
            $validDoctor = false;

            if ($doctorCheck) {
                $doctorCheck->bind_param('ii', $doctorId, $departmentId);
                $doctorCheck->execute();
                $doctorCheck->store_result();
                $validDoctor = $doctorCheck->num_rows > 0;
                $doctorCheck->close();
            }

            if (!$validDoctor) {
                $errorMessage = 'Please select a valid doctor from the selected department.';
            } else {
                $appointmentNumber = 'APT-' . date('Ymd-His') . '-' . strtoupper(substr(bin2hex(random_bytes(3)), 0, 4));

                $insertAppointment = $conn->prepare('INSERT INTO appointments (appointment_number, patient_id, doctor_id, department_id, appointment_date, appointment_time, status) VALUES (?, ?, ?, ?, ?, ?, "PENDING")');
                if ($insertAppointment) {
                    $insertAppointment->bind_param('siiiss', $appointmentNumber, $patientId, $doctorId, $departmentId, $appointmentDate, $appointmentTime);
                    if ($insertAppointment->execute()) {
                        $successMessage = 'Appointment booked successfully. Reference: ' . $appointmentNumber;
                    } else {
                        $errorMessage = 'Unable to book the appointment right now. Please try again.';
                    }
                    $insertAppointment->close();
                } else {
                    $errorMessage = 'Unable to prepare booking request.';
                }
            }
        }
    }
}

$cssVer = @filemtime(__DIR__ . '/static/book_appointment.css') ?: time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/book_appointment.css?v=<?php echo (int) $cssVer; ?>">
</head>
<body>
    <div class="booking-page">
        <div class="booking-card">
            <div class="booking-header">
                <a href="chatbot.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Chatbot</a>
                <h1><i class="fas fa-calendar-check"></i> Book Appointment</h1>
                <p>Complete the form and confirm your appointment request.</p>
            </div>

            <?php if ($successMessage !== ''): ?>
                <div class="alert success"><?php echo htmlspecialchars($successMessage); ?></div>
            <?php endif; ?>

            <?php if ($errorMessage !== ''): ?>
                <div class="alert error"><?php echo htmlspecialchars($errorMessage); ?></div>
            <?php endif; ?>

            <form method="POST" class="booking-form">
                <div class="form-group full">
                    <label for="patient_name">Patient Name</label>
                    <input type="text" id="patient_name" name="patient_name" value="<?php echo htmlspecialchars($patientName); ?>" placeholder="Enter patient full name" required>
                </div>

                <div class="form-group">
                    <label for="department_id">Department</label>
                    <select id="department_id" name="department_id" required>
                        <option value="" selected>Select Department</option>
                        <?php foreach ($departments as $department): ?>
                            <option value="<?php echo (int) $department['department_id']; ?>"><?php echo htmlspecialchars($department['department_name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="doctor_id">Doctor</label>
                    <select id="doctor_id" name="doctor_id" required>
                        <option value="" selected>Select Doctor</option>
                        <?php foreach ($doctors as $doctor): ?>
                            <option value="<?php echo (int) $doctor['doctor_id']; ?>" data-department-id="<?php echo (int) $doctor['department_id']; ?>">
                                <?php echo htmlspecialchars($doctor['doctor_name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="appointment_date">Date</label>
                    <input type="date" id="appointment_date" name="appointment_date" required>
                </div>

                <div class="form-group">
                    <label for="appointment_time">Time</label>
                    <input type="time" id="appointment_time" name="appointment_time" required>
                </div>

                <div class="form-actions full">
                    <button type="submit" class="book-btn"><i class="fas fa-paper-plane"></i> Confirm Appointment</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        const departmentEl = document.getElementById('department_id');
        const doctorEl = document.getElementById('doctor_id');
        const appointmentDateEl = document.getElementById('appointment_date');

        if (appointmentDateEl) {
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            appointmentDateEl.min = `${yyyy}-${mm}-${dd}`;
        }

        function filterDoctorsByDepartment() {
            const selectedDepartment = departmentEl.value;
            const options = doctorEl.querySelectorAll('option[data-department-id]');
            let firstVisible = '';

            options.forEach(option => {
                const visible = selectedDepartment === '' || option.dataset.departmentId === selectedDepartment;
                option.hidden = !visible;
                if (visible && !firstVisible) {
                    firstVisible = option.value;
                }
            });

            if (!doctorEl.value || doctorEl.selectedOptions[0]?.hidden) {
                doctorEl.value = firstVisible || '';
            }
        }

        departmentEl.addEventListener('change', filterDoctorsByDepartment);
        filterDoctorsByDepartment();
    </script>
</body>
</html>
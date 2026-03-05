<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

require_once '../../config/db_connection.php';
require_once '../../config/openai_config.php';
require_once '../../config/OpenAIHandler.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    echo json_encode([
        'success' => false,
        'error' => 'Unauthorized access. Please log in.'
    ]);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!isset($input['message'])) {
    echo json_encode([
        'success' => false,
        'error' => 'No message provided'
    ]);
    exit;
}

$message = trim($input['message']);
$patientId = $_SESSION['user_id'];
$conversationHistory = $input['history'] ?? [];
$response = '';
$actions = [];
$useOpenAI = USE_OPENAI && (new OpenAIHandler(OPENAI_API_KEY))->isConfigured();

$dbContext = buildDatabaseContext($conn, $patientId);

if ($useOpenAI) {
    $aiHandler = new OpenAIHandler(OPENAI_API_KEY, OPENAI_MODEL);

    $basePrompt = defined('SYSTEM_PROMPT')
        ? SYSTEM_PROMPT
        : 'You are a helpful healthcare assistant. Provide general wellness information and recommend consulting doctors for diagnosis.';
    $enhancedSystemPrompt = $basePrompt . "\n\n" . $dbContext;
    
    $aiResponse = $aiHandler->chat($message, $conversationHistory, $enhancedSystemPrompt);
    
    if ($aiResponse['success']) {
        $response = $aiResponse['message'];

        $actions = getContextualActions($message, $patientId, $conn);
        if (SAVE_CONVERSATION_HISTORY) {
            saveConversation($conn, $patientId, $message, $response);
        }
    } else {
        $response = handlePatternMatching($message, $patientId, $conn, $actions);
    }
} else {

    $response = handlePatternMatching($message, $patientId, $conn, $actions);
}

echo json_encode([
    'success' => true,
    'response' => $response,
    'actions' => $actions
]);

function buildDatabaseContext($conn, $patientId) {
    $context = "\n\n[AVAILABLE DATA IN SYSTEM]\n";
    
    $doctors = getAllDoctors($conn);
    if (!empty($doctors)) {
        $context .= "Available Doctors:\n";
        foreach ($doctors as $doc) {
            $context .= "- " . $doc['doctor_name'] . " (" . ($doc['department_name'] ?? 'N/A') . ")\n";
        }
    }

    $appointments = getPatientAppointments($conn, $patientId);
    if (!empty($appointments)) {
        $context .= "\nPatient's Upcoming Appointments:\n";
        foreach ($appointments as $apt) {
            $context .= "- " . $apt['appointment_date'] . " at " . $apt['appointment_time'] . " with Dr. " . ($apt['doctor_name'] ?? 'N/A') . "\n";
        }
    } else {
        $context .= "\nPatient has no upcoming appointments.\n";
    }
    
    return $context;
}

function handlePatternMatching($message, $patientId, $conn, &$actions) {
    $messageLower = strtolower($message);
    $response = '';
    
    if (preg_match('/(find|show|looking for|need).*(doctor|physician|cardiologist|neurologist|dermatologist|pediatrician)/i', $message)) {
        preg_match('/(cardiologist|neurologist|dermatologist|pediatrician|surgeon|dentist|ophthalmologist|psychiatrist)/i', $message, $matches);
        $specialty = $matches[0] ?? 'any';
        $doctors = getDoctorsBySpecialty($conn, $specialty);
        
        if (!empty($doctors)) {
            $response = "Great! I found " . count($doctors) . " doctor(s) for you:\n\n";
            $response .= buildDoctorTable($doctors);
            $actions = [
                ['label' => '📅 Book Appointment', 'action' => 'I want to book an appointment'],
                ['label' => '📞 Contact Doctor', 'action' => 'Show me contact details']
            ];
        } else {
            $response = "I couldn't find doctors for '$specialty'. Would you like to view all doctors?";
            $actions = [
                ['label' => '👨‍⚕️ View All Doctors', 'action' => 'Show me all doctors']
            ];
        }
    } else if (preg_match('/(show|list|view).*(all\s)?doctors|all\s+doctors/i', $message)) {
        $doctors = getAllDoctors($conn);
        if (!empty($doctors)) {
            $response = "Here are all available doctors:\n\n";
            $response .= buildDoctorTable($doctors);
            $actions = [
                ['label' => '📅 Book Appointment', 'action' => 'I want to book an appointment']
            ];
        } else {
            $response = "No doctors are currently available.";
        }
    } else if (preg_match('/(view|show|list|my).*(appointment|booking)/i', $message)) {
        $appointments = getPatientAppointments($conn, $patientId);
        if (!empty($appointments)) {
            $response = "Here are your appointments:\n\n";
            $response .= buildAppointmentTable($appointments);
            $actions = [
                ['label' => '📅 Book New', 'action' => 'Book an appointment']
            ];
        } else {
            $response = "You don't have any appointments. Would you like to book one?";
            $actions = [
                ['label' => '📅 Book Appointment', 'action' => 'Book an appointment']
            ];
        }
    } else {
        $response = "I'm here to help! I can assist you with:
• Finding doctors
• Booking appointments
• Viewing your schedule
• Health information

What would you like to do?";
        $actions = [
            ['label' => '👨‍⚕️ Find a Doctor', 'action' => 'Find a doctor'],
            ['label' => '📅 Book Appointment', 'action' => 'Book an appointment'],
            ['label' => '📋 View Appointments', 'action' => 'Show my appointments']
        ];
    }
    
    return $response;
}

function getContextualActions($message, $patientId, $conn) {
    $actions = [];
    $messageLower = strtolower($message);
    
    if (preg_match('/(doctor|specialist|physician|find)/i', $message)) {
        $actions[] = ['label' => '📅 Book Appointment', 'action' => 'I want to book an appointment'];
        $actions[] = ['label' => '🔍 Find Another Doctor', 'action' => 'Show me another doctor'];
    }
    
    if (preg_match('/(appointment|booking|schedule)/i', $message)) {
        $actions[] = ['label' => '✏️ Reschedule', 'action' => 'I need to reschedule'];
        $actions[] = ['label' => '📅 Book Another', 'action' => 'Book another appointment'];
    }
    
    if (preg_match('/(health|symptom|advice|tip|wellness)/i', $message)) {
        $actions[] = ['label' => '👨‍⚕️ Find Doctor', 'action' => 'Find a doctor'];
        $actions[] = ['label' => '📅 Book Appointment', 'action' => 'Book an appointment'];
    }
    
    if (empty($actions)) {
        $appointments = getPatientAppointments($conn, $patientId);
        if (empty($appointments)) {
            $actions[] = ['label' => '👨‍⚕️ Find a Doctor', 'action' => 'Find a doctor'];
            $actions[] = ['label' => '📅 Book Appointment', 'action' => 'Book an appointment'];
        } else {
            $actions[] = ['label' => '📋 View Appointments', 'action' => 'Show my appointments'];
            $actions[] = ['label' => '📅 Book Another', 'action' => 'Book another appointment'];
        }
    }
    
    return array_slice($actions, 0, 3);
}

function saveConversation($conn, $patientId, $userMessage, $botResponse) {

    $createTableSQL = "CREATE TABLE IF NOT EXISTS chat_history (
        chat_id INT AUTO_INCREMENT PRIMARY KEY,
        patient_id INT NOT NULL,
        user_message TEXT,
        bot_response TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (patient_id) REFERENCES patients(patient_id)
    )";
    
    $conn->query($createTableSQL);

    $sql = "INSERT INTO chat_history (patient_id, user_message, bot_response) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    if ($stmt) {
        $stmt->bind_param("iss", $patientId, $userMessage, $botResponse);
        $stmt->execute();
        $stmt->close();
    }
}

function getAllDoctors($conn) {
    $sql = "SELECT d.*, dep.department_name 
            FROM doctors d 
            LEFT JOIN departments dep ON d.department_id = dep.department_id 
            WHERE d.status = 'ACTIVE'
            ORDER BY d.doctor_id DESC
            LIMIT 20";
    
    $result = $conn->query($sql);
    $doctors = [];
    
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $doctors[] = $row;
        }
    }
    
    return $doctors;
}

function getDoctorsBySpecialty($conn, $specialty) {
    $specialty = '%' . $specialty . '%';
    
    $sql = "SELECT d.*, dep.department_name 
            FROM doctors d 
            LEFT JOIN departments dep ON d.department_id = dep.department_id 
            WHERE (dep.department_name LIKE ? OR d.doctor_name LIKE ?)
            AND d.status = 'ACTIVE'
            ORDER BY d.doctor_id DESC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("ss", $specialty, $specialty);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
    $stmt->close();
    return $doctors;
}

function getPatientAppointments($conn, $patientId) {
    $sql = "SELECT a.*, d.doctor_name, dep.department_name 
            FROM appointments a 
            LEFT JOIN doctors d ON a.doctor_id = d.doctor_id 
            LEFT JOIN departments dep ON a.department_id = dep.department_id 
            WHERE a.patient_id = ?
            AND a.status IN ('PENDING', 'CONFIRMED')
            ORDER BY a.appointment_date DESC
            LIMIT 10";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }
    
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    $stmt->close();
    return $appointments;
}

function buildDoctorTable($doctors) {
    $html = '<table class="data-table"><thead><tr>';
    $html .= '<th>Doctor Name</th>';
    $html .= '<th>Department</th>';
    $html .= '<th>Contact</th></tr></thead><tbody>';
    
    foreach ($doctors as $doctor) {
        $html .= '<tr>';
        $html .= '<td><strong>' . htmlspecialchars($doctor['doctor_name']) . '</strong></td>';
        $html .= '<td>' . htmlspecialchars($doctor['department_name'] ?? 'N/A') . '</td>';
        $html .= '<td>' . htmlspecialchars($doctor['email'] ?? 'N/A') . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}

function buildAppointmentTable($appointments) {
    $html = '<table class="data-table"><thead><tr>';
    $html .= '<th>Date & Time</th>';
    $html .= '<th>Doctor</th>';
    $html .= '<th>Department</th>';
    $html .= '<th>Status</th></tr></thead><tbody>';
    
    foreach ($appointments as $apt) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($apt['appointment_date'] . ' ' . $apt['appointment_time']) . '</td>';
        $html .= '<td>' . htmlspecialchars($apt['doctor_name'] ?? 'N/A') . '</td>';
        $html .= '<td>' . htmlspecialchars($apt['department_name'] ?? 'N/A') . '</td>';
        $html .= '<td><strong>' . htmlspecialchars($apt['status']) . '</strong></td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}

closeConnection($conn);
?>

<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

session_start();

require_once __DIR__ . '/bootstrap.php';
require_once __DIR__ . '/../../config/db_connection.php';
require_once __DIR__ . '/../../config/OpenAIHandler.php';

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
$patientId = (int) $_SESSION['user_id'];
$conversationHistory = $input['history'] ?? [];

$aiHandler = new OpenAIHandler(OPENAI_API_KEY, OPENAI_MODEL, OPENAI_API_URL, OPENAI_TIMEOUT);
$result = AgentOrchestrator::handle($message, $conversationHistory, $patientId, $conn, $aiHandler);

echo json_encode($result);

closeConnection($conn);

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

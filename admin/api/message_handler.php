<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once '../../config/db_connection.php';

ob_start();
register_shutdown_function(function () {
    $error = error_get_last();
    if ($error && in_array($error['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR], true)) {
        if (!headers_sent()) {
            header('Content-Type: application/json');
        }
        $output = ob_get_clean();
        $details = $error['message'] . ' in ' . $error['file'] . ':' . $error['line'];
        echo json_encode([
            'success' => false,
            'message' => 'Server error',
            'details' => $details,
            'output' => $output
        ]);
    }
});

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified']);
    exit;
}

switch ($action) {
    case 'send':
        sendMessage($conn);
        break;
    case 'get_inbox':
        getInbox($conn);
        break;
    case 'get_sent':
        getSentMessages($conn);
        break;
    case 'get_recipients':
        getRecipients($conn);
        break;
    case 'get_appointments':
        getAppointments($conn);
        break;
    case 'mark_as_read':
        markAsRead($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
        break;
}

function sendMessage($conn) {
    $required = ['recipient_id', 'recipient_type', 'message', 'message_type'];
    $missing = [];
    
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing)]);
        return;
    }
    
    $recipient_id = intval($_POST['recipient_id']);
    $recipient_type = $_POST['recipient_type'];
    $message = $_POST['message'];
    $message_type = $_POST['message_type'];
    $subject = isset($_POST['subject']) ? $_POST['subject'] : NULL;
    $appointment_id = isset($_POST['appointment_id']) && $_POST['appointment_id'] ? intval($_POST['appointment_id']) : NULL;
    
    if ($recipient_type === 'patient') {
        $sql = "SELECT phone FROM patients WHERE patient_id = ?";
    } elseif ($recipient_type === 'doctor') {
        $sql = "SELECT phone FROM doctors WHERE doctor_id = ?";
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid recipient type']);
        return;
    }
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $recipient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'message' => 'Recipient not found']);
        $stmt->close();
        return;
    }
    
    $row = $result->fetch_assoc();
    $phone = $row['phone'];
    $stmt->close();
    
    $sql = "INSERT INTO messages (sender_id, sender_type, recipient_id, recipient_type, message, subject, message_type, appointment_id, phone, status, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $sender_id = 1;
    $sender_type = 'admin';
    $status = 'sent';
    
    $stmt->bind_param("isissssiss", $sender_id, $sender_type, $recipient_id, $recipient_type, $message, $subject, $message_type, $appointment_id, $phone, $status);
    
    if ($stmt->execute()) {
        $message_id = $stmt->insert_id;
        echo json_encode(['success' => true, 'message' => 'Message sent successfully', 'message_id' => $message_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function getInbox($conn) {
    $sql = "SELECT m.*, 
            CASE 
                WHEN m.sender_type='patient' THEN p.patient_name
                WHEN m.sender_type='doctor' THEN d.doctor_name
                ELSE 'System'
            END as sender_name
            FROM messages m
            LEFT JOIN patients p ON m.sender_id = p.patient_id AND m.sender_type = 'patient'
            LEFT JOIN doctors d ON m.sender_id = d.doctor_id AND m.sender_type = 'doctor'
            WHERE m.recipient_type = 'admin' OR m.recipient_id = 1
            ORDER BY m.created_at DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
}

function getSentMessages($conn) {
    $sql = "SELECT m.*, 
            CASE 
                WHEN m.recipient_type='patient' THEN p.patient_name
                WHEN m.recipient_type='doctor' THEN d.doctor_name
                ELSE 'Unknown'
            END as recipient_name
            FROM messages m
            LEFT JOIN patients p ON m.recipient_id = p.patient_id AND m.recipient_type = 'patient'
            LEFT JOIN doctors d ON m.recipient_id = d.doctor_id AND m.recipient_type = 'doctor'
            WHERE m.sender_type = 'admin'
            ORDER BY m.created_at DESC";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $messages = [];
    while ($row = $result->fetch_assoc()) {
        $messages[] = $row;
    }
    
    echo json_encode(['success' => true, 'messages' => $messages]);
}

function getRecipients($conn) {
    $recipient_type = isset($_GET['type']) ? $_GET['type'] : 'patient';
    
    if ($recipient_type === 'patient') {
        $sql = "SELECT patient_id as id, patient_name as name, phone FROM patients WHERE phone IS NOT NULL ORDER BY patient_name";
    } else {
        $sql = "SELECT doctor_id as id, doctor_name as name, phone FROM doctors WHERE phone IS NOT NULL ORDER BY doctor_name";
    }
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $recipients = [];
    while ($row = $result->fetch_assoc()) {
        $recipients[] = $row;
    }
    
    echo json_encode(['success' => true, 'recipients' => $recipients]);
}

function getAppointments($conn) {
    $sql = "SELECT a.appointment_id, a.appointment_number, p.patient_name, d.doctor_name, a.appointment_date, a.appointment_time
            FROM appointments a
            JOIN patients p ON a.patient_id = p.patient_id
            JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.appointment_date >= CURDATE()
            ORDER BY a.appointment_date, a.appointment_time DESC
            LIMIT 20";
    
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
        return;
    }
    
    $appointments = [];
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode(['success' => true, 'appointments' => $appointments]);
}

function markAsRead($conn) {
    $message_id = isset($_POST['message_id']) ? intval($_POST['message_id']) : 0;
    
    if (!$message_id) {
        echo json_encode(['success' => false, 'message' => 'Message ID required']);
        return;
    }
    
    $sql = "UPDATE messages SET is_read = 1 WHERE message_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $message_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message marked as read']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
}
?>

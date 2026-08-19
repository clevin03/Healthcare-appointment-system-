<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

// All actions in this handler are for admins only.
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'add':
        addPatient($conn);
        break;
    case 'edit':
        editPatient($conn);
        break;
    case 'delete':
        deletePatient($conn);
        break;
    case 'get':
        getPatient($conn);
        break;
    case 'get_all':
        getAllPatients($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getAllPatients($conn) {
    $sql = "SELECT *, users.email, CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name FROM patients JOIN users ON patients.user_id = users.user_id ORDER BY patients.patient_id ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        return;
    }
    
    $patients = [];
    while ($row = $result->fetch_assoc()) {
        $patients[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $patients]);
}

function getPatient($conn) {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Patient ID is required']);
        return;
    }
    
    $patient_id = intval($_GET['id']);
    $sql = "SELECT *, users.email, CONCAT(patients.first_name, ' ', patients.last_name) AS patient_name FROM patients JOIN users ON patients.user_id = users.user_id WHERE patients.patient_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $patient_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $patient = $result->fetch_assoc();
    
    if ($patient) {
        echo json_encode(['success' => true, 'data' => $patient]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Patient not found']);
    }
    
    $stmt->close();
}
?>

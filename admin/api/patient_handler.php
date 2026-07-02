<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

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
    $sql = "SELECT * FROM patients ORDER BY patient_id DESC";
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
    $sql = "SELECT * FROM patients WHERE patient_id = ?";
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

<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');


function getAllSessions($conn) {
    $sql = "SELECT * FROM sessions ORDER BY session_id DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        return;
    }
    
    $sessions = [];
    while ($row = $result->fetch_assoc()) {
        $sessions[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $sessions]);
}

function getSession($conn){
    if (!isset($_GET['doctor_name'])) {
        echo json_encode(['success' => false, 'message' => 'Doctor name is required']);
        return;
    }
    $doctor_name = $_GET['doctor_name'];
    $sql = "SELECT doctor_id FROM doctors WHERE doctor_name = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    $stmt->bind_param("s", $doctor_name);
    $stmt->execute();
    $result = $stmt->get_result();
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        return;
    }
    $session = $result->fetch_assoc();
    echo json_encode(['success' => true, 'data' => $session]);

}

function editSession($conn) {
    if (!isset($_POST['session_id'])) {
        echo json_encode(['success' => false, 'message' => 'Session ID is required']);
        return;
    }
    
    $session_id = intval($_POST['session_id']);
    $doctor_id = intval($_POST['doctor_id']);
    $session_date = $_POST['session_date'];
    $start_time = $_POST['start_time'];
    $end_time = $_POST['end_time'];
    
    $sql = "UPDATE sessions SET doctor_id = ?, session_date = ?, start_time = ?, end_time = ? WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("isssi", $doctor_id, $session_date, $start_time, $end_time, $session_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Session updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Update failed: ' . $stmt->error]);
    }
}

function deleteSession($conn) {
    if (!isset($_POST['session_id'])) {
        echo json_encode(['success' => false, 'message' => 'Session ID is required']);
        return;
    }
    
    $session_id = intval($_POST['session_id']);
    
    $sql = "DELETE FROM sessions WHERE session_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $session_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Session deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Delete failed: ' . $stmt->error]);
    }
}

function getDoctors($conn){
    $sql = "SELECT doctor_id, doctor_name FROM doctors WHERE status = 'ACTIVE' ORDER BY doctor_name ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        return;
    }
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $doctors]);
}

if ($action === 'getAllSessions') {
    getAllSessions($conn);
}

if ($action === 'getDoctors') {
    getDoctors($conn);
}

if ($action === 'getSession') {
    getSession($conn);
}

if ($action === 'editSession') {
    editSession($conn);
}

if ($action === 'deleteSession') {
    deleteSession($conn);
}

<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if(!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if ($action === 'doctorSessions') {
    $doctorId = (int) ($_POST['doctor_id'] ?? $_GET['doctor_id'] ?? 0);

    if ($doctorId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        exit();
    }

    $sql = "SELECT s.session_id, s.doctor_id, d.doctor_name, s.session_day, s.start_time, s.end_time,
                   COALESCE(s.current_count, 0) AS current_count, s.max_patients
            FROM sessions s
            JOIN doctors d ON s.doctor_id = d.doctor_id
            WHERE s.doctor_id = ? AND s.session_day >= CURDATE() AND s.status = 'active'
              AND COALESCE(s.current_count, 0) < s.max_patients
            ORDER BY s.session_day ASC, s.start_time ASC";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
        exit();
    }

    $stmt->bind_param('i', $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = [];
    while ($result && ($row = $result->fetch_assoc())) {
        $sessions[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $sessions]);
    exit();
}

if($action === 'sessionDetails'){
    $sessionId = isset($_POST['session_id']) ? $_POST['session_id'] : (isset($_GET['session_id']) ? $_GET['session_id'] : '');

    $sql = "SELECT s.session_id, s.doctor_id, d.doctor_name, s.session_day, s.start_time, s.end_time, s.current_count, s.max_patients
            FROM sessions s
            JOIN doctors d ON s.doctor_id = d.doctor_id
            WHERE s.session_id = ?";

    $stmt = $conn->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'message'=>'Database query preparation failed']);
        exit();
    }
    $stmt->bind_param("i", $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result && $result->num_rows >0){
        $sessionDetails = $result->fetch_assoc();
        echo json_encode(['success' => true, 'data' => $sessionDetails]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Session not found']);
    }
}
?>
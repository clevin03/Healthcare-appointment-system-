<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if(!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'doctor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

if($action === 'saveRecord'){
    saveRecord($conn);
}

function saveRecord($conn){
    $doctorId = $_SESSION['doctor_id'];
    $appointmentId = $_POST['appointment_id'];
    $patientId = $_POST['patient_id'];
    $doctorId = $_POST['doctor_id'];
    $diagnosis = $_POST['diagnosis'];
    $prescription = $_POST['prescription'];
    $notes = $_POST['notes'];

    if (!$appointmentId || !$patientId || !$doctorId) {
        echo json_encode(['success' => false, 'message' => 'Missing required information.']);
        return;
    }

    $sql = "INSERT INTO medical_records (appointment_id, patient_id, doctor_id, diagnosis, prescription, notes, date) VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query preparation failed.']);
        return;
    }
    $stmt->bind_param("iiisss", $appointmentId, $patientId, $doctorId, $diagnosis, $prescription, $notes);
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Record added successfully!']);
    }else{
        echo json_encode(['success' => false, 'message' => 'Failed to add record: ' . $stmt->error]);
    }
}
?>
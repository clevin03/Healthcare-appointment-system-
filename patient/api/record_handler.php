<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if($action === 'getRecords'){
    $patientId = $_SESSION['patient_id'];

    $sql = "SELECT d.doctor_name, m.record_id, m.diagnosis, m.prescription, m.notes, m.date FROM medical_records m 
            JOIN doctors d ON m.doctor_id = d.doctor_id WHERE m.patient_id = ? ORDER BY m.date DESC";
    
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query preparation failed']);
        exit();
    }
    $stmt->bind_param('i', $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $records = [];
    while ($result && ($row = $result->fetch_assoc())) {
        $records[] = $row;
    }
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $records]);
    exit();
}
?>
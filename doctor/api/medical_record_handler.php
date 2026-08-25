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
} elseif ($action === 'completeAppointment') {
    completeAppointment($conn);
}

function saveRecord($conn){
    $doctorId = (int) $_SESSION['doctor_id'];
    $appointmentId = $_POST['appointment_id'];
    $patientId = $_POST['patient_id'];
    $diagnosis = $_POST['diagnosis'];
    $prescription = $_POST['prescription'];
    $notes = $_POST['notes'];
    $completeAfterSave = ($_POST['complete_after_save'] ?? '0') === '1';

    if (!$appointmentId || !$patientId || !$doctorId || !trim($diagnosis) || !trim($prescription)) {
        echo json_encode(['success' => false, 'message' => 'Missing required information.']);
        return;
    }

    $sql = "INSERT INTO medical_records (appointment_id, patient_id, doctor_id, diagnosis, prescription, notes, date) VALUES (?, ?, ?, ?, ?, ?, NOW())";

    $conn->begin_transaction();
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Database query preparation failed.']);
        return;
    }
    $stmt->bind_param("iiisss", $appointmentId, $patientId, $doctorId, $diagnosis, $prescription, $notes);
    if (!$stmt->execute()) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to add record: ' . $stmt->error]);
        return;
    }

    $stmt->close();

    if ($completeAfterSave) {
        $statusStmt = $conn->prepare(
            "UPDATE appointments a
             INNER JOIN sessions s ON s.session_id = a.session_id
             SET a.status = 'COMPLETED'
             WHERE a.appointment_id = ? AND a.session_id = ? AND s.doctor_id = ?"
        );
        $sessionId = (int) ($_POST['session_id'] ?? 0);
        $statusStmt->bind_param('iii', $appointmentId, $sessionId, $doctorId);
        $statusStmt->execute();

        if ($statusStmt->affected_rows !== 1) {
            $statusStmt->close();
            $conn->rollback();
            echo json_encode(['success' => false, 'message' => 'Could not complete the appointment.']);
            return;
        }
        $statusStmt->close();
    }

    $conn->commit();
    echo json_encode(['success' => true, 'message' => $completeAfterSave ? 'Medical report saved and appointment completed.' : 'Record added successfully!']);
}

function completeAppointment($conn){
    $appointmentId = (int) ($_POST['appointment_id'] ?? 0);
    $sessionId = (int) ($_POST['session_id'] ?? 0);
    $doctorId = (int) ($_SESSION['doctor_id'] ?? 0);

    if ($appointmentId <= 0 || $sessionId <= 0 || $doctorId <= 0) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid appointment or session.']);
        return;
    }

    $recordStmt = $conn->prepare(
        "SELECT record_id
         FROM medical_records
         WHERE appointment_id = ? AND doctor_id = ?
           AND TRIM(COALESCE(diagnosis, '')) <> ''
           AND TRIM(COALESCE(prescription, '')) <> ''
         LIMIT 1"
    );
    $recordStmt->bind_param('ii', $appointmentId, $doctorId);
    $recordStmt->execute();
    $recordExists = $recordStmt->get_result()->num_rows > 0;
    $recordStmt->close();

    if (!$recordExists) {
        http_response_code(422);
        echo json_encode(['success' => false, 'message' => 'Please save the medical report before completing the appointment.']);
        return;
    }

    $stmt = $conn->prepare(
        "UPDATE appointments a
         INNER JOIN sessions s ON s.session_id = a.session_id
         SET a.status = 'COMPLETED'
         WHERE a.appointment_id = ? AND a.session_id = ? AND s.doctor_id = ?"
    );
    $stmt->bind_param('iii', $appointmentId, $sessionId, $doctorId);
    $stmt->execute();

    if ($stmt->affected_rows === 1) {
        echo json_encode(['success' => true, 'message' => 'Appointment marked as completed.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'Appointment not found or already completed.']);
    }

    $stmt->close();
}
?>
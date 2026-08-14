<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action){
    case 'getBookings':
        getBookings($conn);
        break;
    case 'cancelBooking':
        cancelBooking($conn);
        break;
    case 'bookAppointment':
        bookAppointment($conn);
        break;
    case 'checkBooking':
        checkBooking($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getBookings($conn) {
    $patientId = $_SESSION['patient_id'];
    $sql = "SELECT a.appointment_id, a.appointment_number, s.session_day, s.start_time, s.end_time, a.status, d.doctor_name
            FROM appointments a
            JOIN sessions s ON a.session_id = s.session_id
            JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.patient_id = ? AND a.status != 'cancelled'
            ORDER BY s.session_day ASC";

    $stmt = $conn->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'message'=>'Database query preparation failed']);
        return;
    }
    $stmt->bind_param("i", $patientId);
    $stmt->execute();
    $result = $stmt->get_result();
    $bookings = [];
    if($result && $result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $bookings[] = $row;
        }
    }
    $stmt->close();
    echo json_encode(['success' => true, 'data' => $bookings]);
}

function cancelBooking($conn) {

}

function bookAppointment($conn){
    $sessionId = isset($_POST['session_id']) ? $_POST['session_id'] : '';
    //$userId = $_SESSION['user_id'];
    $patientId = $_SESSION['patient_id'];
    $appointmentNumber = isset($_POST['appointment_number']) ? $_POST['appointment_number'] : '';
    $doctorId = isset($_POST['doctor_id']) ? $_POST['doctor_id'] : '';

    // Server-side check to prevent double booking
    $checkSql = "SELECT 1 FROM appointments WHERE patient_id = ? AND session_id = ? AND status != 'cancelled' LIMIT 1";
    $checkStmt = $conn->prepare($checkSql);
    if(!$checkStmt){
        echo json_encode(['success'=>false, 'message'=>'Database query preparation failed for checking existing booking.']);
        return;
    }
    $checkStmt->bind_param("ii", $patientId, $sessionId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    if($checkResult->num_rows > 0){
        echo json_encode(['success'=>false, 'message'=>'You have already booked an appointment for this session.']);
        $checkStmt->close();
        return;
    }
    $checkStmt->close();

    if (empty($sessionId) || empty($doctorId) || empty($appointmentNumber)) {
        echo json_encode(['success' => false, 'message' => 'Missing booking data.']);
        return;
    }

    $conn->begin_transaction();

    try {
        $sql = "INSERT INTO appointments (
            patient_id, session_id, doctor_id, appointment_number, status
        ) VALUES (?, ?, ?, ?, 'CONFIRMED')";

        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database query preparation failed for appointment insert.');
        }

        $stmt->bind_param("iiis", $patientId, $sessionId, $doctorId, $appointmentNumber);
        if (!$stmt->execute()) {
            throw new Exception('Failed to insert appointment.');
        }
        $stmt->close();

        $sql = "UPDATE sessions
                SET current_count = current_count + 1
                WHERE session_id = ? AND current_count < max_patients";
        $stmt = $conn->prepare($sql);
        if (!$stmt) {
            throw new Exception('Database query preparation failed for session update.');
        }

        $stmt->bind_param("i", $sessionId);
        if (!$stmt->execute() || $stmt->affected_rows === 0) {
            throw new Exception('Session is full or does not exist.');
        }
        $stmt->close();

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Appointment booked successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function checkBooking($conn){
    $sessionId = isset($_POST['session_id']) ? $_POST['session_id'] : '';
    $userId = $_SESSION['user_id'];
    $patientId = $_SESSION['patient_id'];

    $sql = "SELECT EXISTS(SELECT 1 FROM appointments WHERE patient_id = ? AND session_id = ? AND status != 'cancelled') AS has_booking";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'message'=>'Database query preparation failed']);
        return;
    }
    $stmt->bind_param("ii", $patientId, $sessionId);
    $stmt->execute();
    $result = $stmt->get_result();
    if($result && $result->num_rows>0){
        $row = $result->fetch_assoc();
        $hasBooking = $row['has_booking'] == 1 ? true : false;
        echo json_encode(['success'=>true, 'has_booking'=>$hasBooking]);
    } else {
        echo json_encode(['success'=>false, 'message'=>'Failed to check booking']);
    }
}

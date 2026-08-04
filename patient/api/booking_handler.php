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
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getBookings($conn) {
    $userId = $_SESSION['user_id'];
    $sql = "SELECT a.appointment_id, a.appointment_number, s.session_day, s.start_time, s.end_time, a.status, d.doctor_name
            FROM appointments a
            JOIN sessions s ON a.session_id = s.session_id
            JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.patient_id = ? AND a.status != 'cancelled'
            ORDER BY s.session_day DESC";

    $stmt = $conn->prepare($sql);
    if(!$stmt){
        echo json_encode(['success'=>false, 'message'=>'Database query preparation failed']);
        return;
    }
    $stmt->bind_param("i", $userId);
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
    
}
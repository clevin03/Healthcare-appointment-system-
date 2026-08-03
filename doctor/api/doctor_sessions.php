<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if(!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'doctor') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}
if($action === 'getAll'){
    getAllSessions($conn);
}

function getAllSessions($conn){
    $doctor_id = $_SESSION['doctor_id'];

    $sql = "SELECT session_day, start_time, end_time, max_patients, current_count, status 
            FROM sessions WHERE session_day >= CURDATE() AND doctor_id = ? ORDER BY session_day ASC, start_time ASC";
    $stmt = $conn->prepare($sql);
    if(!$stmt){
        echo json_encode(['success' =>false, 'message'=> 'Database query preparation failed']);
        return;
    }
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $sessions = [];
    if($result && $result->num_rows > 0){
        while($row = $result->fetch_assoc()){
            $sessions[] = $row;
        }
    }
    echo json_encode(['success' => true, 'sessions' => $sessions]);
}
?>

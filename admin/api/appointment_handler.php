<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'add':
        addAppointment($conn);
        break;
    case 'edit':
        editAppointment($conn);
        break;
    case 'delete':
        deleteAppointment($conn);
        break;
    case 'get':
        getAppointment($conn);
        break;
    case 'get_all':
        getAllAppointments($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function addAppointment($conn) {
    $appointment_number = $_POST['appointment_number'];
    $patient_name = $_POST['patient_name'];
    $doctor_name = $_POST['doctor_name'];
    $phone_number = $_POST['phone_number'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = $_POST['status'];

    $patient_id = getOrCreatePatient($conn, $patient_name, $phone_number);
    $doctor_id = getOrCreateDoctor($conn, $doctor_name);
    
    $sql = "INSERT INTO appointments (appointment_number, patient_id, doctor_id, appointment_date, appointment_time, status) 
            VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("siisss", $appointment_number, $patient_id, $doctor_id, $date, $time, $status);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Appointment added successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function editAppointment($conn) {
    $id = $_POST['id'];
    $appointment_number = $_POST['appointment_number'];
    $patient_name = $_POST['patient_name'];
    $doctor_name = $_POST['doctor_name'];
    $phone_number = $_POST['phone_number'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = $_POST['status'];
    
    $patient_id = getOrCreatePatient($conn, $patient_name, $phone_number);
    $doctor_id = getOrCreateDoctor($conn, $doctor_name);
    
    $sql = "UPDATE appointments 
            SET appointment_number = ?, patient_id = ?, doctor_id = ?, 
                appointment_date = ?, appointment_time = ?, status = ?
            WHERE appointment_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("siisssi", $appointment_number, $patient_id, $doctor_id, $date, $time, $status, $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Appointment updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function deleteAppointment($conn) {
    $id = $_POST['id'];
    
    $sql = "DELETE FROM appointments WHERE appointment_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Appointment deleted successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function getAppointment($conn) {
    $id = $_GET['id'];
    
    $sql = "SELECT a.*, p.patient_name, p.phone, d.doctor_name 
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
            WHERE a.appointment_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        echo json_encode(['success' => true, 'data' => $row]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Appointment not found']);
    }
    
    $stmt->close();
}

function getAllAppointments($conn) {
    $sql = "SELECT a.*, p.patient_name, p.phone, d.doctor_name 
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        return;
    }
    
    $appointments = [];
    
    while ($row = $result->fetch_assoc()) {
        $appointments[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $appointments]);
}

function getOrCreatePatient($conn, $patient_name, $phone) {
    $sql = "SELECT patient_id FROM patients WHERE patient_name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("s", $patient_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['patient_id'];
    }
    $stmt->close();
    
    $sql = "INSERT INTO patients (patient_name, phone) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("ss", $patient_name, $phone);
    $stmt->execute();
    $patient_id = $conn->insert_id;
    $stmt->close();
    
    return $patient_id;
}

function getOrCreateDoctor($conn, $doctor_name) {
    $sql = "SELECT doctor_id FROM doctors WHERE doctor_name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("s", $doctor_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['doctor_id'];
    }
    $stmt->close();
    
    $sql = "INSERT INTO doctors (doctor_name) VALUES (?)";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("s", $doctor_name);
    $stmt->execute();
    $doctor_id = $conn->insert_id;
    $stmt->close();
    
    return $doctor_id;
}
?>

<?php
session_start();
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);
require_once '../../config/db_connection.php';

// All actions in this handler are for admins only.
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit();
}

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

if (!$action) {
    echo json_encode(['success' => false, 'message' => 'No action specified', 'debug' => $_POST]);
    exit;
}

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
        echo json_encode(['success' => false, 'message' => 'Invalid action: ' . $action]);
        break;
}

function addAppointment($conn) {
    $required = ['appointment_number', 'patient_name', 'doctor_name', 'phone_number', 'department', 'date', 'time', 'status'];
    $missing = [];
    foreach ($required as $field) {
        if (!isset($_POST[$field]) || empty(trim($_POST[$field]))) {
            $missing[] = $field;
        }
    }
    
    if (!empty($missing)) {
        echo json_encode(['success' => false, 'message' => 'Missing required fields: ' . implode(', ', $missing), 'received' => array_keys($_POST)]);
        return;
    }
    
    $appointment_number = $_POST['appointment_number'];
    $patient_name = $_POST['patient_name'];
    $doctor_name = $_POST['doctor_name'];
    $phone_number = $_POST['phone_number'];
    $department = $_POST['department'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = $_POST['status'];

    $patient_id = getOrCreatePatient($conn, $patient_name, $phone_number);
    if (!$patient_id) {
        echo json_encode(['success' => false, 'message' => 'Failed to create or find patient record']);
        return;
    }
    
    $doctor_id = getOrCreateDoctor($conn, $doctor_name);
    if (!$doctor_id) {
        echo json_encode(['success' => false, 'message' => 'Failed to create or find doctor record']);
        return;
    }
    
    $department_id = getDepartmentId($conn, $department);
    if (!$department_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid department selected']);
        return;
    }
    
    $sql = "INSERT INTO appointments (appointment_number, patient_id, doctor_id, department_id, appointment_date, appointment_time, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("siiisss", $appointment_number, $patient_id, $doctor_id, $department_id, $date, $time, $status);
    
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
    $department = $_POST['department'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $status = $_POST['status'];
    
    $patient_id = getOrCreatePatient($conn, $patient_name, $phone_number);
    if (!$patient_id) {
        echo json_encode(['success' => false, 'message' => 'Failed to create or find patient record']);
        return;
    }
    
    $doctor_id = getOrCreateDoctor($conn, $doctor_name);
    if (!$doctor_id) {
        echo json_encode(['success' => false, 'message' => 'Failed to create or find doctor record']);
        return;
    }
    
    $department_id = getDepartmentId($conn, $department);
    if (!$department_id) {
        echo json_encode(['success' => false, 'message' => 'Invalid department selected']);
        return;
    }
    
    $sql = "UPDATE appointments 
            SET appointment_number = ?, patient_id = ?, doctor_id = ?, department_id = ?,
                appointment_date = ?, appointment_time = ?, status = ?
            WHERE appointment_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("siiisssi", $appointment_number, $patient_id, $doctor_id, $department_id, $date, $time, $status, $id);
    
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
    
    $sql = "SELECT a.*, p.patient_name, p.phone, d.doctor_name, dept.department_name
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
            LEFT JOIN departments dept ON a.department_id = dept.department_id
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
    $sql = "SELECT a.*, p.patient_name, p.phone, d.doctor_name, dept.department_name
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
            LEFT JOIN departments dept ON a.department_id = dept.department_id
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

function getDepartmentId($conn, $department_name) {
    $sql = "SELECT department_id FROM departments WHERE department_name = ? LIMIT 1";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }
    
    $stmt->bind_param("s", $department_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stmt->close();
        return $row['department_id'];
    }
    $stmt->close();
    
    return null;
}
?>

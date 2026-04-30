<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = isset($_POST['action']) ? $_POST['action'] : (isset($_GET['action']) ? $_GET['action'] : '');

switch ($action) {
    case 'add':
        addDoctor($conn);
        break;
    case 'edit':
        editDoctor($conn);
        break;
    case 'delete':
        deleteDoctor($conn);
        break;
    case 'get':
        getDoctor($conn);
        break;
    case 'get_all':
        getAllDoctors($conn);
        break;
    case 'get_departments':
        getDepartments($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
        break;
}

function getAllDoctors($conn) {
    $sql = "SELECT d.*, dep.department_name 
            FROM doctors d 
            LEFT JOIN departments dep ON d.department_id = dep.department_id 
            ORDER BY d.doctor_id DESC";
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        return;
    }
    
    $doctors = [];
    while ($row = $result->fetch_assoc()) {
        $doctors[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $doctors]);
}

function getDoctor($conn) {
    if (!isset($_GET['id'])) {
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    $doctor_id = intval($_GET['id']);
    $sql = "SELECT d.*, dep.department_name 
            FROM doctors d 
            LEFT JOIN departments dep ON d.department_id = dep.department_id 
            WHERE d.doctor_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $doctor_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctor = $result->fetch_assoc();
    
    if ($doctor) {
        echo json_encode(['success' => true, 'data' => $doctor]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
    }
    
    $stmt->close();
}

function addDoctor($conn) {
    $doctor_name = $_POST['doctor_name'] ?? '';
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $status = $_POST['status'] ?? 'ACTIVE';
    
    if (empty($doctor_name)) {
        echo json_encode(['success' => false, 'message' => 'Doctor name is required']);
        return;
    }
    
    if (empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'Department is required']);
        return;
    }
    
    if (!empty($email)) {
        $check_sql = "SELECT doctor_id FROM doctors WHERE email = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("s", $email);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists']);
            $check_stmt->close();
            return;
        }
        $check_stmt->close();
    }
    
    $sql = "INSERT INTO doctors (doctor_name, email, phone, department_id, status) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("sssis", $doctor_name, $email, $phone, $department_id, $status);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Doctor added successfully', 'doctor_id' => $stmt->insert_id]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to add doctor: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function editDoctor($conn) {
    $doctor_id = $_POST['doctor_id'] ?? null;
    $doctor_name = $_POST['doctor_name'] ?? '';
    $email = $_POST['email'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $status = $_POST['status'] ?? 'ACTIVE';
    
    if (empty($doctor_id)) {
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    if (empty($doctor_name)) {
        echo json_encode(['success' => false, 'message' => 'Doctor name is required']);
        return;
    }
    
    if (empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'Department is required']);
        return;
    }
    
    if (!empty($email)) {
        $check_sql = "SELECT doctor_id FROM doctors WHERE email = ? AND doctor_id != ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("si", $email, $doctor_id);
        $check_stmt->execute();
        $check_result = $check_stmt->get_result();
        
        if ($check_result->num_rows > 0) {
            echo json_encode(['success' => false, 'message' => 'Email already exists for another doctor']);
            $check_stmt->close();
            return;
        }
        $check_stmt->close();
    }
    
    $sql = "UPDATE doctors 
            SET doctor_name = ?, email = ?, phone = ?, department_id = ?, status = ? 
            WHERE doctor_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("sssisi", $doctor_name, $email, $phone, $department_id, $status, $doctor_id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Doctor updated successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to update doctor: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function deleteDoctor($conn) {
    if (!isset($_POST['doctor_id'])) {
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    $doctor_id = intval($_POST['doctor_id']);
    
    $check_sql = "SELECT COUNT(*) as count FROM appointments WHERE doctor_id = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("i", $doctor_id);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    $row = $check_result->fetch_assoc();
    
    if ($row['count'] > 0) {
        echo json_encode(['success' => false, 'message' => 'Cannot delete doctor with existing appointments. Please set status to INACTIVE instead.']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();
    
    $sql = "DELETE FROM doctors WHERE doctor_id = ?";
    $stmt = $conn->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Prepare failed: ' . $conn->error]);
        return;
    }
    
    $stmt->bind_param("i", $doctor_id);
    
    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            echo json_encode(['success' => true, 'message' => 'Doctor deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to delete doctor: ' . $stmt->error]);
    }
    
    $stmt->close();
}

function getDepartments($conn) {
    $sql = "SELECT department_id, department_name FROM departments ORDER BY department_name ASC";
    $result = $conn->query($sql);
    
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Query failed: ' . $conn->error]);
        return;
    }
    
    $departments = [];
    while ($row = $result->fetch_assoc()) {
        $departments[] = $row;
    }
    
    echo json_encode(['success' => true, 'data' => $departments]);
}
?>

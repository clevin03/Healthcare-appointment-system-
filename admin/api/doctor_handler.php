<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

// All actions in this handler are for admins only.
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized Access']);
    exit();
}

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
    $sql = "SELECT d.*, dep.department_name, u.email
            FROM doctors d 
            LEFT JOIN departments dep ON d.department_id = dep.department_id 
            LEFT JOIN users u ON d.user_id = u.user_id
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
    $sql = "SELECT d.*, dep.department_name, u.email
            FROM doctors d 
            LEFT JOIN departments dep ON d.department_id = dep.department_id 
            LEFT JOIN users u ON d.user_id = u.user_id
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
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? null;
    $department_id = $_POST['department_id'] ?? null;
    $status = $_POST['status'] ?? 'ACTIVE';
    
    if (empty($doctor_name)) {
        echo json_encode(['success' => false, 'message' => 'Doctor name is required']);
        return;
    }

    if (empty($email)) {
        echo json_encode(['success' => false, 'message' => 'Email is required to create a doctor user account.']);
        return;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        return;
    }
    
    if (empty($department_id)) {
        echo json_encode(['success' => false, 'message' => 'Department is required']);
        return;
    }
    
    // Check if email exists in users table
    $check_sql = "SELECT user_id FROM users WHERE email = ?";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("s", $email);
    $check_stmt->execute();
    $check_result = $check_stmt->get_result();
    
    if ($check_result->num_rows > 0) {
        echo json_encode(['success' => false, 'message' => 'Email is already registered to another user.']);
        $check_stmt->close();
        return;
    }
    $check_stmt->close();

    $conn->begin_transaction();

    try {
        // Create user account for the doctor
        $temporary_password = 'doctor123'; // A default temporary password
        $password_hash = password_hash($temporary_password, PASSWORD_DEFAULT);
        $user_type = 'doctor';

        $user_sql = "INSERT INTO users (email, password, user_type, is_active) VALUES (?, ?, ?, 1)";
        $user_stmt = $conn->prepare($user_sql);
        if (!$user_stmt) {
            throw new Exception('User insert prepare failed: ' . $conn->error);
        }
        $user_stmt->bind_param("sss", $email, $password_hash, $user_type);
        
        if (!$user_stmt->execute()) {
            throw new Exception('Failed to create user account: ' . $user_stmt->error);
        }
        
        $user_id = $user_stmt->insert_id;
        $user_stmt->close();

        // Insert doctor details
        $sql = "INSERT INTO doctors (user_id, doctor_name, phone, department_id, status) 
                VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception('Prepare failed: ' . $conn->error);
        }
        
        $stmt->bind_param("issis", $user_id, $doctor_name, $phone, $department_id, $status);
        
        if ($stmt->execute()) {
            $doctor_id = $stmt->insert_id;
            $conn->commit();
            echo json_encode(['success' => true, 'message' => 'Doctor added successfully and a user account has been created.', 'doctor_id' => $doctor_id]);
        } else {
            throw new Exception('Failed to add doctor: ' . $stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
}

function editDoctor($conn) {
    $doctor_id = (int) ($_POST['doctor_id'] ?? 0);
    $doctor_name = trim($_POST['doctor_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $department_id = (int) ($_POST['department_id'] ?? 0);
    $status = $_POST['status'] ?? 'ACTIVE';

    if ($doctor_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Doctor ID is required']);
        return;
    }
    
    if (empty($doctor_name)) {
        echo json_encode(['success' => false, 'message' => 'Doctor name is required']);
        return;
    }
    
    if ($department_id <= 0) {
        echo json_encode(['success' => false, 'message' => 'Department is required']);
        return;
    }

    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email format.']);
        return;
    }

    $lookup_stmt = $conn->prepare("SELECT user_id FROM doctors WHERE doctor_id = ?");
    $lookup_stmt->bind_param("i", $doctor_id);
    $lookup_stmt->execute();
    $doctor = $lookup_stmt->get_result()->fetch_assoc();
    $lookup_stmt->close();

    if (!$doctor) {
        echo json_encode(['success' => false, 'message' => 'Doctor not found']);
        return;
    }

    $user_id = (int) $doctor['user_id'];

    if ($email !== '') {
        $check_stmt = $conn->prepare("SELECT user_id FROM users WHERE email = ? AND user_id <> ?");
        $check_stmt->bind_param("si", $email, $user_id);
        $check_stmt->execute();
        if ($check_stmt->get_result()->num_rows > 0) {
            $check_stmt->close();
            echo json_encode(['success' => false, 'message' => 'Email already exists for another user']);
            return;
        }
        $check_stmt->close();
    }

    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare(
            "UPDATE doctors
             SET doctor_name = ?, phone = ?, department_id = ?, status = ?
             WHERE doctor_id = ?"
        );
        $stmt->bind_param("ssisi", $doctor_name, $phone, $department_id, $status, $doctor_id);
        if (!$stmt->execute()) {
            throw new Exception('Failed to update doctor: ' . $stmt->error);
        }
        $stmt->close();

        if ($email !== '') {
            $user_stmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $user_stmt->bind_param("si", $email, $user_id);
            if (!$user_stmt->execute()) {
                throw new Exception('Failed to update doctor email: ' . $user_stmt->error);
            }
            $user_stmt->close();
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'Doctor updated successfully']);
    } catch (Exception $e) {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
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

<?php
header('Content-Type: application/json');
require_once '../../config/db_connection.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'getAll':
        getAllDepartments($conn);
        break;
    case 'create':
        createDepartment($conn);
        break;
    case 'update':
        updateDepartment($conn);
        break;
    case 'delete':
        deleteDepartment($conn);
        break;
    case 'toggleStatus':
        toggleDepartmentStatus($conn);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Invalid action']);
}

closeConnection($conn);

function getAllDepartments($conn) {
    try {
        $sql = "SELECT * FROM departments ORDER BY department_name ASC";
        $result = $conn->query($sql);
        
        if (!$result) {
            throw new Exception($conn->error);
        }
        
        $departments = [];
        while ($row = $result->fetch_assoc()) {
            $deptId = $row['department_id'];
            $doctorSql = "SELECT doctor_id, doctor_name, phone, status 
                         FROM doctors 
                         WHERE department_id = ? 
                         ORDER BY doctor_name ASC";
            
            $stmt = $conn->prepare($doctorSql);
            $stmt->bind_param("i", $deptId);
            $stmt->execute();
            $doctorResult = $stmt->get_result();
            
            $doctors = [];
            while ($doctor = $doctorResult->fetch_assoc()) {
                $doctors[] = $doctor;
            }
            $stmt->close();
            
            $row['doctors'] = $doctors;
            $departments[] = $row;
        }
        
        echo json_encode([
            'success' => true,
            'departments' => $departments
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error retrieving departments: ' . $e->getMessage()
        ]);
    }
}

function createDepartment($conn) {
    try {
        $departmentName = trim($_POST['department_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;
        
        if (empty($departmentName)) {
            echo json_encode([
                'success' => false,
                'message' => 'Department name is required'
            ]);
            return;
        }
        $checkSql = "SELECT department_id FROM departments WHERE department_name = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("s", $departmentName);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'A department with this name already exists'
            ]);
            $stmt->close();
            return;
        }
        $stmt->close();
        
        $sql = "INSERT INTO departments (department_name, description, is_active) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssi", $departmentName, $description, $isActive);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Department created successfully',
                'department_id' => $conn->insert_id
            ]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error creating department: ' . $e->getMessage()
        ]);
    }
}

function updateDepartment($conn) {
    try {
        $departmentId = (int)($_POST['department_id'] ?? 0);
        $departmentName = trim($_POST['department_name'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 1;

        if ($departmentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid department ID'
            ]);
            return;
        }
        
        if (empty($departmentName)) {
            echo json_encode([
                'success' => false,
                'message' => 'Department name is required'
            ]);
            return;
        }
    
        $checkSql = "SELECT department_id FROM departments WHERE department_name = ? AND department_id != ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("si", $departmentName, $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'A department with this name already exists'
            ]);
            $stmt->close();
            return;
        }
        $stmt->close();
        
        $sql = "UPDATE departments SET department_name = ?, description = ?, is_active = ? WHERE department_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $departmentName, $description, $isActive, $departmentId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Department updated successfully'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating department: ' . $e->getMessage()
        ]);
    }
}

function deleteDepartment($conn) {
    try {
        $departmentId = (int)($_POST['department_id'] ?? 0);
        
        if ($departmentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid department ID'
            ]);
            return;
        }
        
        $checkSql = "SELECT COUNT(*) as count FROM doctors WHERE department_id = ?";
        $stmt = $conn->prepare($checkSql);
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete department. There are ' . $row['count'] . ' doctor(s) assigned to this department.'
            ]);
            $stmt->close();
            return;
        }
        $stmt->close();
        $checkAppointmentsSql = "SELECT COUNT(*) as count FROM appointments WHERE department_id = ?";
        $stmt = $conn->prepare($checkAppointmentsSql);
        $stmt->bind_param("i", $departmentId);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        
        if ($row['count'] > 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Cannot delete department. There are ' . $row['count'] . ' appointment(s) associated with this department.'
            ]);
            $stmt->close();
            return;
        }
        $stmt->close();

        $sql = "DELETE FROM departments WHERE department_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $departmentId);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Department deleted successfully'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error deleting department: ' . $e->getMessage()
        ]);
    }
}

function toggleDepartmentStatus($conn) {
    try {
        $departmentId = (int)($_POST['department_id'] ?? 0);
        $isActive = isset($_POST['is_active']) ? (int)$_POST['is_active'] : 0;
        
        if ($departmentId <= 0) {
            echo json_encode([
                'success' => false,
                'message' => 'Invalid department ID'
            ]);
            return;
        }
        
        $sql = "UPDATE departments SET is_active = ? WHERE department_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $isActive, $departmentId);
        
        if ($stmt->execute()) {
            $status = $isActive ? 'activated' : 'deactivated';
            echo json_encode([
                'success' => true,
                'message' => 'Department ' . $status . ' successfully'
            ]);
        } else {
            throw new Exception($stmt->error);
        }
        
        $stmt->close();
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Error updating department status: ' . $e->getMessage()
        ]);
    }
}
?>

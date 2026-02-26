<?php
session_start();
require_once '../config/db_connection.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Department Management - eDoctor</title>
    <link rel="stylesheet" href="static/department.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <header class="page-header">
            <h1><i class="fas fa-building"></i> Department Management</h1>
            <button class="btn-add" id="addDepartmentBtn">
                <i class="fas fa-plus"></i> Add New Department
            </button>
        </header>

        <div class="content">
            <div class="departments-grid" id="departmentsGrid">
            </div>
        </div>
    </div>

    <div id="departmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Department</h2>
                <span class="close">&times;</span>
            </div>
            <form id="departmentForm">
                <input type="hidden" id="departmentId" name="department_id">
                
                <div class="form-group">
                    <label for="departmentName">Department Name <span class="required">*</span></label>
                    <input type="text" id="departmentName" name="department_name" required>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"></textarea>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" id="isActive" name="is_active" checked>
                        <span>Active Department</span>
                    </label>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>

    <div id="loadingSpinner" class="spinner-overlay" style="display: none;">
        <div class="spinner"></div>
    </div>

    <script src="static/department.js"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="static/doctor.css">
    <title>Doctor Management</title>
</head>
<body>
    <section class="doctor-section">
        <div class="stat-title">
            <i class="fa-solid fa-user-doctor"></i> Doctor Management
        </div>
        
        <div class="stat-content">
            <button class="btn-add" onclick="openAddModal()">
                <i class="fa-solid fa-plus"></i> Add New Doctor
            </button>
            
            <table class="doctor-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Department</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="doctorTableBody">
                    <tr>
                        <td colspan="7" style="text-align:center;">
                            <i class="fa-solid fa-spinner fa-spin"></i> Loading...
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <div id="doctorModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle">Add New Doctor</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="doctorForm">
                <input type="hidden" id="doctorId" name="doctor_id">
                
                <div class="form-group">
                    <label for="doctorName">Doctor Name <span class="required">*</span></label>
                    <input type="text" id="doctorName" name="doctor_name" required maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" maxlength="100">
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone</label>
                    <input type="tel" id="phone" name="phone" maxlength="20">
                </div>
                
                <div class="form-group">
                    <label for="department">Department <span class="required">*</span></label>
                    <select id="department" name="department_id" required>
                        <option value="">Select Department</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="status">Status <span class="required">*</span></label>
                    <select id="status" name="status" required>
                        <option value="ACTIVE">Active</option>
                        <option value="INACTIVE">Inactive</option>
                    </select>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Save Doctor
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="static/doctor.js"></script>
</body>
</html>
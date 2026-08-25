<?php
    session_start();

    if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
        header('Location: ../auth/login.php');
        exit();
    }

    require_once '../config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="static/patient.css">
    <title>Patient Management</title>
</head>
<body>
    <section class="patient-section">
        <div class="stat-title"><i class="fa-solid fa-users"></i> Patient Management</div>
        <div class="stat-content">
            <table class="patient-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Birth Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody id="patientTableBody">
                </tbody>
            </table>
        </div>
    </section>

    <div id="patientDetailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fa-solid fa-user-circle"></i> Patient Details</h2>
                <span class="close" onclick="closePatientModal()">&times;</span>
            </div>
            <div class="modal-body" id="patientDetailsBody">
            </div>
            <div class="modal-footer">
                <button class="btn-secondary" onclick="closePatientModal()">
                    <i class="fa-solid fa-times"></i> Close
                </button>
            </div>
        </div>
    </div>

    <div id="patientEditModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fa-solid fa-user-pen"></i> Edit Patient</h2>
                <span class="close" onclick="closePatientEditModal()">&times;</span>
            </div>
            <form id="patientEditForm">
                <input type="hidden" name="patient_id" id="editPatientId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="editFirstName">First Name</label>
                        <input type="text" name="first_name" id="editFirstName" required>
                    </div>
                    <div class="form-group">
                        <label for="editLastName">Last Name</label>
                        <input type="text" name="last_name" id="editLastName" required>
                    </div>
                </div>
                <div class="form-group">
                    <label for="editPhone">Phone</label>
                    <input type="tel" name="phone" id="editPhone">
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editGender">Gender</label>
                        <select name="gender" id="editGender">
                            <option value="">Select gender</option>
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="editDateOfBirth">Birth Date</label>
                        <input type="date" name="date_of_birth" id="editDateOfBirth">
                    </div>
                </div>
                <div class="form-group">
                    <label for="editAddress">Address</label>
                    <textarea name="address" id="editAddress" rows="3"></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closePatientEditModal()">
                        <i class="fa-solid fa-times"></i> Cancel
                    </button>
                    <button type="submit" class="btn-primary">
                        <i class="fa-solid fa-save"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="static/patient.js?v=<?php echo time(); ?>"></script>
</body>
</html>
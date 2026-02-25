<?php
    session_start();
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

    <script src="static/patient.js?v=<?php echo time(); ?>"></script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="static/appointment.css" class="stylesheet">
    <title>Appointments Management</title>
</head>
<body>
    <?php
    require_once '../config/db_connection.php';
    
    $sql = "SELECT a.appointment_id, a.appointment_number, a.appointment_date, a.appointment_time, a.status,
                   p.patient_name, p.phone,
                   d.doctor_name,
                   dept.department_name
            FROM appointments a
            LEFT JOIN patients p ON a.patient_id = p.patient_id
            LEFT JOIN doctors d ON a.doctor_id = d.doctor_id
            LEFT JOIN departments dept ON a.department_id = dept.department_id
            ORDER BY a.appointment_date DESC, a.appointment_time DESC";
    
    $result = $conn->query($sql);
    $appointments = [];
    
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $appointments[] = $row;
        }
    }
    
    $dept_sql = "SELECT department_id, department_name FROM departments ORDER BY department_name";
    $dept_result = $conn->query($dept_sql);
    $departments = [];
    if ($dept_result && $dept_result->num_rows > 0) {
        while ($row = $dept_result->fetch_assoc()) {
            $departments[] = $row;
        }
    }
    ?>
    
    <div class="container">
        <header>
            <h1><i class="fas fa-calendar-check"></i> Appointment Management</h1>
            <button class="btn-add" onclick="openAddModal()">
                <i class="fas fa-plus"></i> Add New Appointment
            </button>
        </header>

        <div class="appointments-grid" id="appointmentsGrid">
            <?php
            if (count($appointments) > 0) {
                foreach ($appointments as $appointment) {
            ?>
                <div class="appointment-card" data-id="<?php echo $appointment['appointment_id']; ?>">
                    <div class="card-header">
                        <span class="appointment-number">
                            <i class="fas fa-hashtag"></i> <?php echo htmlspecialchars($appointment['appointment_number']); ?>
                        </span>
                        <span class="status-badge status-<?php echo strtolower($appointment['status']); ?>">
                            <?php echo htmlspecialchars($appointment['status']); ?>
                        </span>
                    </div>
                    
                    <div class="card-body">
                        <div class="info-row">
                            <i class="fas fa-user-injured"></i>
                            <div class="info-content">
                                <label>Patient Name</label>
                                <span><?php echo htmlspecialchars($appointment['patient_name']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="fas fa-user-doctor"></i>
                            <div class="info-content">
                                <label>Doctor Name</label>
                                <span><?php echo htmlspecialchars($appointment['doctor_name']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="fas fa-phone"></i>
                            <div class="info-content">
                                <label>Phone Number</label>
                                <span><?php echo htmlspecialchars($appointment['phone']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="fas fa-building"></i>
                            <div class="info-content">
                                <label>Department</label>
                                <span><?php echo htmlspecialchars($appointment['department_name']); ?></span>
                            </div>
                        </div>
                        
                        <div class="info-row">
                            <i class="fas fa-calendar"></i>
                            <div class="info-content">
                                <label>Date & Time</label>
                                <span>
                                    <?php 
                                    echo date('M d, Y', strtotime($appointment['appointment_date']));
                                    echo ' at ';
                                    echo date('h:i A', strtotime($appointment['appointment_time']));
                                    ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <button class="btn-icon btn-view" onclick="viewDetails(<?php echo $appointment['appointment_id']; ?>)" title="View Details">
                            <i class="fas fa-eye"></i>
                        </button>
                        <button class="btn-icon btn-edit" onclick="editAppointment(<?php echo $appointment['appointment_id']; ?>)" title="Edit">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-icon btn-delete" onclick="deleteAppointment(<?php echo $appointment['appointment_id']; ?>)" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            <?php
                }
            } else {
            ?>
                <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 15px;">
                    <i class="fas fa-calendar-times" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                    <h3 style="color: #666;">No appointments found</h3>
                    <p style="color: #999;">Click "Add New Appointment" to create your first appointment.</p>
                </div>
            <?php
            }
            ?>
        </div>
    </div>

    <div id="appointmentModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modalTitle"><i class="fas fa-plus-circle"></i> Add New Appointment</h2>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <form id="appointmentForm" method="POST">
                <input type="hidden" id="appointmentId" name="id">
                
                <div class="form-group">
                    <label for="appointmentNumber"><i class="fas fa-hashtag"></i> Appointment Number</label>
                    <input type="text" id="appointmentNumber" name="appointment_number" required>
                </div>
                
                <div class="form-group">
                    <label for="patientName"><i class="fas fa-user-injured"></i> Patient Name</label>
                    <input type="text" id="patientName" name="patient_name" required>
                </div>
                
                <div class="form-group">
                    <label for="doctorName"><i class="fas fa-user-doctor"></i> Doctor Name</label>
                    <input type="text" id="doctorName" name="doctor_name" required>
                </div>
                
                <div class="form-group">
                    <label for="phoneNumber"><i class="fas fa-phone"></i> Phone Number</label>
                    <input type="tel" id="phoneNumber" name="phone_number" required>
                </div>
                
                <div class="form-group">
                    <label for="department"><i class="fas fa-building"></i> Department</label>
                    <select id="department" name="department" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept) { ?>
                            <option value="<?php echo htmlspecialchars($dept['department_name']); ?>"><?php echo htmlspecialchars($dept['department_name']); ?></option>
                        <?php } ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="appointmentDate"><i class="fas fa-calendar"></i> Date</label>
                        <input type="date" id="appointmentDate" name="date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="appointmentTime"><i class="fas fa-clock"></i> Time</label>
                        <input type="time" id="appointmentTime" name="time" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="status"><i class="fas fa-info-circle"></i> Status</label>
                    <select id="status" name="status" required>
                        <option value="PENDING">Pending</option>
                        <option value="CONFIRMED">Confirmed</option>
                        <option value="CANCELLED">Cancelled</option>
                    </select>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn-secondary" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-save"></i> Save Appointment
                    </button>
                </div>
            </form>
        </div>
    </div>


    <div id="detailsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-info-circle"></i> Appointment Details</h2>
                <span class="close" onclick="closeDetailsModal()">&times;</span>
            </div>
            <div class="details-content" id="detailsContent">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-primary" onclick="closeDetailsModal()">Close</button>
            </div>
        </div>
    </div>

    <div id="deleteModal" class="modal">
        <div class="modal-content modal-small">
            <div class="modal-header">
                <h2><i class="fas fa-exclamation-triangle"></i> Confirm Delete</h2>
                <span class="close" onclick="closeDeleteModal()">&times;</span>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this appointment? This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="button" class="btn-danger" onclick="confirmDelete()">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </div>
    </div>

    <script src="static/apointment.js"></script>
</body>
</html>

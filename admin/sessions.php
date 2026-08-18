<?php
session_start();
if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}
require_once '../config/db_connection.php';

?>

<doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Management - eDoctor</title>
    <link rel="stylesheet" href="static/department.css">
    <link rel="stylesheet" href="static/sessions.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php
    $sql = "SELECT s.session_id, d.doctor_name, s.session_day, s.start_time,
    s.end_time, s.max_patients FROM sessions s JOIN doctors d ON
    s.doctor_id = d.doctor_id ORDER BY s.session_id DESC";
    $result = $conn->query($sql);
    $sessions = [];
    
    if ($result && $result->num_rows > 0){
        while ($row = $result->fetch_assoc()){
            $sessions[]=$row;
        }
    }
    ?>
    <section class="session-section">
        <div class="container">
            <header class="page-header stat-title">
                <h1><i class="fas fa-clock"></i> Session Management</h1>
                <button class="btn-add" id="addSessionBtn">
                    <i class="fas fa-plus"></i> Add New Session
                </button>
            </header>

            <div class="content stat-content">
                <div class="sessions-table" id="sessionsTable">
                    <?php
                    if (count($sessions)>0){
                        ?>
                        <table class="session-table">
                                <thead>
                                    <tr>
                                        <th>Session ID</th>
                                        <th>Doctor Name</th>
                                        <th>Session Date</th>
                                        <th>Start Time</th>
                                        <th>End Time</th>
                                        <th>Max Patients</th>
                                    </tr>
                                    <tbody id="sessionTable">
                                        
                                    </tbody>
                                </thead>
                            
                            <?php
                            foreach($sessions as $session){
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($session['session_id']); ?></td>
                                <td><?php echo htmlspecialchars($session['doctor_name']); ?></td>
                                <td><?php echo htmlspecialchars($session['session_day']); ?></td>
                                <td><?php echo htmlspecialchars($session['start_time']); ?></td>
                                <td><?php echo htmlspecialchars($session['end_time']); ?></td>
                                <td><?php echo htmlspecialchars($session['max_patients']); ?></td>
                            </tr>
                            <?php
                            }
                            ?>
                        </table>  
                    <?php }else{ ?>
                        <div style="grid-column: 1/-1; text-align: center; padding: 40px; background: white; border-radius: 15px;">
                            <i class="fas fa-calendar-times" style="font-size: 48px; color: #ccc; margin-bottom: 15px;"></i>
                            <h3 style="color: #666;">No sessions found</h3>
                            <p style="color: #999;">Click "Add New Session" to create your first session.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </section>
    

        <div class="modal" id="sessionModal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2 id="modalTitle">Add New Session</h2>
                    <span class="close">&times;</span>
                </div>
                <form id="sessionForm" method="POST">
                    <input type="hidden" id="sessionId" name="session_id">
                    <input type="hidden" id="doctorId" name="doctor_id">
                    <input type="hidden" id="createdBy" name="created_by" value="<?php echo isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : ''; ?>">
                    <div class="form-group">
                        <!--<label for="doctorName">Doctor Name <span class="required">*</span></label>
                        <input type="text" id="doctorName" name="doctor_name" required>-->
                        <select id="doctorName" name="doctor_name" required>
                            <option value="">Select Doctor</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="sessionDate">Session Date <span class="required">*</span></label>
                        <input type="date" id="sessionDate" name="session_date" required>
                    </div>
                    <div class="form-group">
                        <label for="startTime">Start Time <span class="required">*</span></label>
                        <input type="time" id="startTime" name="start_time" required>
                    </div>
                    <div class="form-group">
                        <label for="endTime">End Time <span class="required">*</span></label>
                        <input type="time" id="endTime" name="end_time" required>
                    </div>
                    <div class="form-group">
                        <label for="maxPatients">Max Patients<span class="required">*</span></label>
                        <input type="number" id="maxPatients" name="maxPatients" required>
                    </div>
                    <div class="form-group status">
                        <label>Status<span class="required">*</span></label>
                        <div class="radio-group">
                            <label for="pending"><input type="radio" id="pending" name="status" value="pending"> Pending</label>
                            <label for="active"><input type="radio" id="active" name="status" value="active"> Active</label>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn-submit">Save</button>
                        <button type="button" class="btn-cancel" id="cancelBtn">Cancel</button>
                    </div>

                </form>
            </div>
        </div>

    </div>
    

    <script src="static/sessions.js"></script>
</body>
</html>
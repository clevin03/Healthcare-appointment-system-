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
                <table>
                    <thead>
                        <tr>
                            <th>Session ID</th>
                            <th>Doctor ID</th>
                            <th>Session Date</th>
                            <th>Start Time</th>
                            <th>End Time</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="sessionTableBody">
                        <!-- Session rows will be populated here via JavaScript -->

                    </tbody>
                </table>
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
                <form id="sessionForm">
                    <input type="hidden" id="sessionId" name="session_id">
                    <input type="hidden" id="doctorId" name="doctor_id">
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
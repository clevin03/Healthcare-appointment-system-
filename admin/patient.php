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

    <script src="static/patient.js"></script>
</body>
</html>
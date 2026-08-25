<?php
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Records</title>
    <link rel="stylesheet" href="static/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
</head>
<body>
    <div class="top-header" style="background-color: #007bff; color: white;">
        <div class="header-content">
            <div class="user-profile">
                <div class="user-avatar">
                    <span class="avatar-icon"><i class="fas fa-user"></i></span>
                </div>
                <div class="user-info">
                    <h3 class="user-name"><?php echo htmlspecialchars($patient_name); ?></h3>
                    <p class="user-email"><?php echo htmlspecialchars($patient_email); ?></p>
                </div>
            </div>
            <form method="post" action="../auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" class="logout-form">
                <button type="submit" class="logout-btn">Logout</button>
            </form>
        </div>
    </div>
    <div class="container">
       <div class="sidebar">
            <nav class="sidebar-menu">
                <ul>
                    <li>
                        <a href="patient_dashboard.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-home"></i></span>
                            <span class="menu-text">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="chatbot.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-robot"></i></span>
                            <span class="menu-text">Health Assistant</span>
                        </a>
                    </li>
                    <li>
                        <a href="allDoctors.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-user-md"></i></span>
                            <span class="menu-text">All Doctors</span>
                        </a>
                    </li>
                    <li>
                        <a href="sessions.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-calendar"></i></span>
                            <span class="menu-text">Scheduled Sessions</span>
                        </a>
                    </li>
                    <li>
                        <a href="my_bookings.php" class="menu-item active">
                            <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="menu-text">My Bookings</span>
                        </a>
                    </li>
                    <li>
                        <a href="myRecords.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="menu-text">My Records</span>
                        </a>
                    </li>
                    <li>
                        <a href="patientSettings.php" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-cog"></i></span>
                            <span class="menu-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div> 
    </div>
</body>
</html>
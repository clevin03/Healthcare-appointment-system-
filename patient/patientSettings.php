<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') != 'patient') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db_connection.php';

$patient_id = $_SESSION['patient_id'] ?? 0;
$user_id = $_SESSION['user_id'] ?? 0;

// Fetch current patient data
$stmt = $conn->prepare("SELECT p.first_name, p.last_name, u.email, p.phone FROM patients p JOIN users u ON p.user_id = u.user_id WHERE p.patient_id = ?");
$stmt->bind_param("i", $patient_id);
$stmt->execute();
$result = $stmt->get_result();
$patient = $result->fetch_assoc();
$stmt->close();

// Use session data as fallback
//$patient_name = $patient['patient_name'] ?? $_SESSION['user_name'] ?? 'Patient';
$first_name = $patient['first_name'] ?? '';
$last_name = $patient['last_name'] ?? '';
$patient_email = $patient['email'] ?? $_SESSION['user_email'] ?? '';
$patient_phone = $patient['phone'] ?? '';

$patient_name = trim($first_name . ' ' . $last_name);
if (empty($patient_name)) {
    $patient_name = $_SESSION['user_name'] ?? 'Patient';
}

$update_success = '';
$update_error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        $newFirstName = trim($_POST['first_name'] ?? '');
        $newLastName = trim($_POST['last_name'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        if (!empty($newFirstName) && !empty($newEmail)&& !empty($newLastName)) {
            $conn->begin_transaction();
            $profile_updated = false;

            // Update the users table (assuming it holds the primary email and name for login)
            $updateUserStmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $updateUserStmt->bind_param("si", $newEmail, $user_id);

            if ($updateUserStmt->execute()) {
                // Also update the patients table to keep it in sync
                $updatePatientStmt = $conn->prepare("UPDATE patients SET first_name = ?,last_name = ?, phone = ? WHERE patient_id = ?");
                $updatePatientStmt->bind_param("sssi", $newFirstName, $newLastName, $newPhone, $patient_id);
                if ($updatePatientStmt->execute()) {
                    $conn->commit();
                    $profile_updated = true;
                } else {
                    $conn->rollback();
                    $update_error = "Error updating patient details: " . $updatePatientStmt->error;
                }
                $updatePatientStmt->close();
            } else {
                $conn->rollback();
                $update_error = "Error updating user account: " . $updateUserStmt->error;
            }
            $updateUserStmt->close();

            if ($profile_updated) {
                $_SESSION['user_name'] = $newFirstName . ' ' . $newLastName;
                $_SESSION['patient_name'] = $newFirstName . ' ' . $newLastName;
                $_SESSION['user_email'] = $newEmail;
                $update_success = "Profile updated successfully!";
                $patient_name = $newFirstName.' '.$newLastName;
                $patient_email = $newEmail;
                $patient_phone = $newPhone;
            }
        } else {
            $update_error = "Name and Email cannot be empty.";
        }
    } elseif (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $update_error = "All password fields are required.";
        } elseif ($new_password !== $confirm_password) {
            $update_error = "New password and confirmation do not match.";
        } else {
            // Fetch user to verify current password
            // Corrected: Use $user_id for operations on the 'users' table.
            if ($user_id > 0) { // Ensure user_id is valid before proceeding
                $userStmt = $conn->prepare("SELECT password FROM users WHERE user_id = ? AND user_type = 'patient'");
                if ($userStmt) { // Check if prepare was successful
                    $userStmt->bind_param("i", $user_id); // Corrected: Bind $user_id
                    $userStmt->execute();
                    $userResult = $userStmt->get_result();
                    if ($userRow = $userResult->fetch_assoc()) {
                        if (password_verify($current_password, $userRow['password'])) {
                            $newPasswordHash = password_hash($new_password, PASSWORD_DEFAULT);
                            $passUpdateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                            if ($passUpdateStmt) { // Check if prepare was successful
                                $passUpdateStmt->bind_param("si", $newPasswordHash, $user_id); // Corrected: Bind $user_id
                                if ($passUpdateStmt->execute()) {
                                    $update_success = "Password changed successfully!";
                                } else {
                                    $update_error = "Error changing password: " . $passUpdateStmt->error;
                                }
                                $passUpdateStmt->close();
                            } else {
                                $update_error = "Failed to prepare password update statement.";
                            }
                        } else {
                            $update_error = "Incorrect current password.";
                        }
                    } else {
                        $update_error = "Could not find user account to update password.";
                    }
                } else {
                    $update_error = "Failed to prepare user verification statement.";
                }
                $userStmt->close();
            } else {
                $update_error = "Invalid user ID for password change operation.";
            }
        }
    }
}
$conn->close();
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Settings</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/patient_dashboard.css">
    <link rel="stylesheet" href="static/patientSettings.css">
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
                        <a href="#home" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-home"></i></span>
                            <span class="menu-text">Home</span>
                        </a>
                    </li>
                    <li>
                        <a href="#chatbot" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-robot"></i></span>
                            <span class="menu-text">Health Assistant</span>
                        </a>
                    </li>
                    <li>
                        <a href="#doctors" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-user-md"></i></span>
                            <span class="menu-text">All Doctors</span>
                        </a>
                    </li>
                    <li>
                        <a href="#sessions" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-calendar"></i></span>
                            <span class="menu-text">Scheduled Sessions</span>
                        </a>
                    </li>
                    <li>
                        <a href="#bookings" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="menu-text">My Bookings</span>
                        </a>
                    </li>
                    <li>
                        <a href="#records" class="menu-item">
                            <span class="menu-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="menu-text">My Records</span>
                        </a>
                    </li>
                    <li>
                        <a href="patientSettings.php" class="menu-item active">
                            <span class="menu-icon"><i class="fas fa-cog"></i></span>
                            <span class="menu-text">Settings</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </div>

        <div class="main-content">
            <h1>Settings</h1>

            <?php if (!empty($update_success)): ?>
                <div class="alert alert-success"><?php echo $update_success; ?></div>
            <?php endif; ?>
            <?php if (!empty($update_error)): ?>
                <div class="alert alert-error"><?php echo $update_error; ?></div>
            <?php endif; ?>

            <div class="settings-container">
                <div class="setting-card" id="viewProfileCard" style="cursor: pointer;">
                    <div class="icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="settings-text">
                        <h3>Profile Information</h3>
                        <p>View personal information about your account.</p>
                    </div>
                </div>
                
                <a href="">
                    <div class="setting-card">
                        <div class="icon">
                            <i class="fas fa-user-cog"></i>
                        </div>
                        <div class="settings-text">
                            <h3>Edit Profile</h3>
                            <p>Update your personal information.</p>
                        </div>
                    </div>
                </a>
                
                <a href="">
                    <div class="setting-card">
                    <div class="icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <div class="settings-text">
                        <h3>Change Password</h3>
                        <p>Update your account password.</p>
                    </div>
                </div>
                </a>
            
        </div>
    </div>
    
    <!-- Profile Information Modal -->
    <div id="profileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Profile Information</h2>
                <span class="close-btn">&times;</span>
            </div>
            <div class="modal-body">
                <div class="profile-info-item">
                    <strong>Name:</strong>
                    <p><?php echo htmlspecialchars($patient_name); ?></p>
                </div>
                <div class="profile-info-item">
                    <strong>Email:</strong>
                    <p><?php echo htmlspecialchars($patient_email); ?></p>
                </div>
                <div class="profile-info-item">
                    <strong>Phone:</strong>
                    <p><?php echo htmlspecialchars($patient_phone ?: 'Not provided'); ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit profile form Modal -->
    <div id="editProfileModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Edit Profile</h2>
                <span class="close-btn">&times;</span>
            </div>
            <div class="modal-body">
                <form method="post" action="patientSettings.php" id="editProfileForm">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label for="edit_first_name">First Name</label>
                        <input type="text" id="edit_first_name" name="first_name" value="<?php echo htmlspecialchars($first_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_last_name">Last Name</label>
                        <input type="text" id="edit_last_name" name="last_name" value="<?php echo htmlspecialchars($last_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($patient_email); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="text" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($patient_phone); ?>">
                    </div>
                    <button type="submit" class="btn-primary">Save Changes</button>
                </form>
            </div>
        </div>
    </div>

    <!-- Change Password Modal -->
    <div id="changePasswordModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Change Password</h2>
                <span class="close-btn">&times;</span>
            </div>
            <div class="modal-body">
                <form method="post" action="patientSettings.php" id="changePasswordForm">
                    <input type="hidden" name="change_password" value="1">
                    <div class="form-group">
                        <label for="current_password">Current Password</label>
                        <input type="password" id="current_password" name="current_password" required>
                    </div>
                    <div class="form-group">
                        <label for="new_password">New Password</label>
                        <input type="password" id="new_password" name="new_password" required>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirm New Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>
                    <button type="submit" class="btn-primary">Change Password</button>
                </form>
            </div>
        </div>
    </div>


    <script src="static/patient_dashboard.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Get the modal
            var modal = document.getElementById("profileModal");
            var editProfileModal = document.getElementById("editProfileModal");
            var changePasswordModal = document.getElementById("changePasswordModal");

            // Get the card that opens the modal
            var card = document.getElementById("viewProfileCard");
            var editProfileCard = document.querySelector(".settings-container a:nth-of-type(1) .setting-card");
            var changePasswordCard = document.querySelector(".settings-container a:nth-of-type(2) .setting-card");

            // Get the <span> element that closes the modal
            var span = modal.querySelector(".close-btn");
            var editProfileSpan = editProfileModal.querySelector(".close-btn");
            var changePasswordSpan = changePasswordModal.querySelector(".close-btn");

            // When the user clicks the card, open the modal 
            card.onclick = function() {
                modal.style.display = "block";
            }
            editProfileCard.onclick = function(e) {
                e.preventDefault(); // Prevent default link behavior
                editProfileModal.style.display = "block";
            }
            changePasswordCard.onclick = function(e) {
                e.preventDefault(); // Prevent default link behavior
                changePasswordModal.style.display = "block";
            }

            // When the user clicks on <span> (x), close the modal
            span.onclick = function() {
                modal.style.display = "none";
            }
            editProfileSpan.onclick = function() {
                editProfileModal.style.display = "none";
            }
            changePasswordSpan.onclick = function() {
                changePasswordModal.style.display = "none";
            }

            // When the user clicks anywhere outside of the modal, close it
            window.onclick = function(event) {
                if (event.target == modal || event.target == editProfileModal || event.target == changePasswordModal) {
                    modal.style.display = "none";
                }
            }
        });
    </script>
</body>
</html>

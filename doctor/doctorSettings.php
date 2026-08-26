<?php
session_start();

if (!isset($_SESSION['user_id']) || ($_SESSION['user_type'] ?? '') != 'doctor') {
    header("Location: ../auth/login.php");
    exit();
}

require_once '../config/db_connection.php';
$doctorId = $_SESSION['doctor_id'];
$userId = $_SESSION['user_id'];
$doctorName = $_SESSION['user_name'];
$doctorEmail = $_SESSION['doctor_email'];

$stmt = $conn->prepare("SELECT phone FROM doctors WHERE doctor_id = ?");
$stmt->bind_param("i", $doctorId);
$stmt->execute();
$result = $stmt->get_result();
$doctor = $result->fetch_assoc();
$stmt->close();

$phoneNumber = $doctor['phone'];

if($_SERVER['REQUEST_METHOD'] == 'POST'){
    if(isset($_POST['update_profile'])){
        $newName = trim($_POST['name'] ?? '');
        $newEmail = trim($_POST['email'] ?? '');
        $newPhone = trim($_POST['phone'] ?? '');

        if(!empty($newName) && !empty($newEmail)){
            $conn->begin_transaction();
            $profile_updated = false;

            $updateUserStmt = $conn->prepare("UPDATE users SET email = ? WHERE user_id = ?");
            $updateUserStmt->bind_param("si", $newEmail, $userId);

            if ($updateUserStmt->execute()) {
                // Also update the doctors table to keep it in sync
                $updateDoctorStmt = $conn->prepare("UPDATE doctors SET doctor_name = ?, phone = ? WHERE doctor_id = ?");
                $updateDoctorStmt->bind_param("sssi", $newName, $newPhone, $doctorId);
                if ($updateDoctorStmt->execute()) {
                    $conn->commit();
                    $profile_updated = true;
                } else {
                    $conn->rollback();
                    $update_error = "Error updating patient details: " . $updateDoctorStmt->error;
                }
                $updateDoctorStmt->close();
            } else {
                $conn->rollback();
                $update_error = "Error updating user account: " . $updateUserStmt->error;
            }
            $updateUserStmt->close();

            if ($profile_updated) {
                $_SESSION['doctor_name'] = $newName;
                $_SESSION['user_email'] = $newEmail;
                $update_success = "Profile updated successfully!";
                $doctorName = $newName;
                $doctorEmail = $newEmail;
                $phoneNumber = $newPhone;
            }
        }else{
            $update_error = "Name and Email cannot be empty.";
        }
    }elseif (isset($_POST['change_password'])) {
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
            if ($userId > 0) { // Ensure user_id is valid before proceeding
                $userStmt = $conn->prepare("SELECT password FROM users WHERE user_id = ? AND user_type = 'doctor'");
                if ($userStmt) { // Check if prepare was successful
                    $userStmt->bind_param("i", $userId); // Corrected: Bind $user_id
                    $userStmt->execute();
                    $userResult = $userStmt->get_result();
                    if ($userRow = $userResult->fetch_assoc()) {
                        if (password_verify($current_password, $userRow['password'])) {
                            $newPasswordHash = password_hash($new_password, PASSWORD_DEFAULT);
                            $passUpdateStmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                            if ($passUpdateStmt) { // Check if prepare was successful
                                $passUpdateStmt->bind_param("si", $newPasswordHash, $userId); // Corrected: Bind $user_id
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

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/main.css">
    <link rel="stylesheet" href="static/doctorSettings.css">
    <title>Settings</title>
</head>
<body>
    <div class="header">
        <div class="header-content">
            <div class="logo"><i class="fa-solid fa-user-doctor"></i> Doctor's Portal</div>
            <div class="user-info">
                <span>Welcome, <?php echo htmlspecialchars($doctorName); ?></span>
                <form method="post" action="../auth/logout.php" onsubmit="return confirm('Are you sure you want to logout?');" style="display:inline; margin:0;">
                    <button type="submit">Logout</button>
                </form>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="sidebar">
            <ul>
                <li><a href="doctor_dashboard.php" ><i class="fa-solid fa-chart-column"></i> Dashboard</a></li>
                <li><a href="#"><i class="fa-solid fa-calendar"></i> My se</a></li>
                <li><a href="patient_search.php"><i class="fa-solid fa-users"></i> Find Patient</a></li>
                <li><a href="doctorSettingd.php" class="active"><i class="fa-solid fa-gear"></i> Settings</a></li>
            </ul>
        </div>
        <div class="main-content">
            <h1 class="page-title">Settings</h1>

            <div class="settings-container">
                <div class="setting-card" id="viewProfileCard" style="cursor: pointer">
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
                    <p><?php echo htmlspecialchars($doctorName); ?></p>
                </div>
                <div class="profile-info-item">
                    <strong>Email:</strong>
                    <p><?php echo htmlspecialchars($doctorEmail); ?></p>
                </div>
                <div class="profile-info-item">
                    <strong>Phone:</strong>
                    <p><?php echo htmlspecialchars($phoneNumber ?: 'Not provided'); ?></p>
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
                <form method="post" action="doctorSettings.php" id="editProfileForm">
                    <input type="hidden" name="update_profile" value="1">
                    <div class="form-group">
                        <label for="edit_first_name">Name</label>
                        <input type="text" id="edit_first_name" name="name" value="<?php echo htmlspecialchars($doctorName); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_email">Email</label>
                        <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($doctorEmail); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="edit_phone">Phone</label>
                        <input type="text" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($phoneNumber); ?>">
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
                <form method="post" action="doctorSettings.php" id="changePasswordForm">
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
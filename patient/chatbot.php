<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../login.php");
    exit();
}

$patient_name = isset($_SESSION['patient_name']) ? $_SESSION['patient_name'] : 'Patient';
$patient_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/chatbot.css">
</head>
<body>
    <div class="chatbot-container">
        <div class="chatbot-header">
            <div class="header-content">
                <div class="header-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="header-info">
                    <h1>Healthcare Assistant</h1>
                    <p class="status-indicator">
                        <span class="status-dot"></span>
                        Online - Ready to help
                    </p>
                </div>
            </div>
            <a href="../patient/patient_dashboard.php" class="back-btn" title="Back to Dashboard">
                <i class="fas fa-arrow-left"></i>
            </a>
        </div>

        <div class="chatbot-messages" id="chatMessages">
            <div class="message bot-message welcome-message">
                <div class="message-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <p>Hello <?php echo htmlspecialchars($patient_name); ?>! 👋 Welcome to our Healthcare Assistant.</p>
                    <p>I'm here to help you:</p>
                    <div class="quick-suggestions">
                        <div class="suggestion-item" onclick="sendMessage('Find a doctor')">
                            <i class="fas fa-user-md"></i>
                            <span>Find a Doctor</span>
                        </div>
                        <div class="suggestion-item" onclick="sendMessage('Book an appointment')">
                            <i class="fas fa-calendar"></i>
                            <span>Book Appointment</span>
                        </div>
                        <div class="suggestion-item" onclick="sendMessage('Health information')">
                            <i class="fas fa-info-circle"></i>
                            <span>Health Info</span>
                        </div>
                        <div class="suggestion-item" onclick="sendMessage('View my appointments')">
                            <i class="fas fa-list"></i>
                            <span>My Appointments</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="chatbot-input-area">
            <form id="chatForm" onsubmit="handleFormSubmit(event)">
                <div class="input-wrapper">
                    <input 
                        type="text" 
                        id="userInput" 
                        class="chat-input" 
                        placeholder="Type your message or ask about doctors, appointments..."
                        autocomplete="off"
                    />
                    <button type="submit" class="send-btn" title="Send message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>
            </form>
            <div class="input-hints">
                <small>Try: "Find a cardiologist", "Book an appointment", "Show my appointments"</small>
            </div>
        </div>
    </div>

    <script>
        const patientId = <?php echo $patient_id; ?>;
        const patientName = "<?php echo htmlspecialchars($patient_name); ?>";
    </script>
    <script src="static/chatbot.js"></script>
</body>
</html>
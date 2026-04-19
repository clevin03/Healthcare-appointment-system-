<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'patient') {
    header("Location: ../login.php");
    exit();
}

$patient_name = isset($_SESSION['patient_name']) ? $_SESSION['patient_name'] : 'Patient';
$patient_id = $_SESSION['user_id'];
$chatbot_css_ver = @filemtime(__DIR__ . '/static/chatbot.css') ?: time();
$chatbot_js_ver = @filemtime(__DIR__ . '/static/chatbot.js') ?: time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Healthcare Assistant</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/chatbot.css?v=<?php echo (int)$chatbot_css_ver; ?>">
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
                        <div class="suggestion-item" onclick="openBookingPage()">
                            <i class="fas fa-calendar-check"></i>
                            <span>Book Appointment</span>
                        </div>
                        <div class="suggestion-item mental-health-item" onclick="sendMessage('I need mental health support')">
                            <i class="fas fa-heart"></i>
                            <span>Mental Health</span>
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
                <small>Try: "Book an appointment", "Find a cardiologist", "Show my appointments"</small>
            </div>
        </div>
    </div>

    <div id="crisisModal" class="crisis-modal hidden" hidden>
        <div class="crisis-modal-content">
            <div class="crisis-header">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>We Care About Your Safety</h2>
            </div>
            <div class="crisis-body">
                <p>If you are experiencing a mental health crisis, please take these steps immediately:</p>
                <div class="crisis-actions">
                    <div class="crisis-action">
                        <strong>Emergency Services:</strong>
                        <p>Call 911 (USA) or your local emergency number</p>
                    </div>
                    <div class="crisis-action">
                        <strong>Crisis Hotline:</strong>
                        <p>Call or text 988 (Suicide & Crisis Lifeline, USA)</p>
                    </div>
                    <div class="crisis-action">
                        <strong>Text Support:</strong>
                        <p>Text HOME to 741741 (Crisis Text Line)</p>
                    </div>
                    <div class="crisis-action">
                        <strong>Trusted Person:</strong>
                        <p>Reach out to a family member, friend, or counselor now</p>
                    </div>
                </div>
            </div>
            <button class="crisis-close-btn" onclick="closeCrisisModal()">I'm Safe, Close This</button>
        </div>
    </div>

    <div id="memoryConsentModal" class="consent-modal hidden" hidden>
        <div class="consent-modal-content">
            <h3>Save Mental Health Progress?</h3>
            <p>We can save your conversation patterns and mood trends to help provide better support over time.</p>
            <div class="consent-options">
                <button class="consent-yes" onclick="setMemoryConsent(true)">Yes, Help Me Track Progress</button>
                <button class="consent-no" onclick="setMemoryConsent(false)">Not Now</button>
            </div>
        </div>
    </div>

    <script>
        const patientId = <?php echo json_encode((int)$patient_id); ?>;
        const patientName = <?php echo json_encode((string)$patient_name); ?>;
    </script>
    <script src="static/chatbot.js?v=<?php echo (int)$chatbot_js_ver; ?>"></script>
</body>
</html>
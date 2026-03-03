<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
    <link rel="stylesheet" href="static/message.css">
    <title>Send Email</title>
</head>
<body>
    <section class="message-section">
        <div class="stat-title">
            <i class="fa-solid fa-envelope"></i> Send Email
        </div>
        
        <div class="stat-content">
            <form class="email-form" action="api/message_handler.php" method="post">
                <div class="form-group">
                    <label for="email">
                        <i class="fa-solid fa-at"></i> Recipient Email
                    </label>
                    <input type="email" id="email" name="email" placeholder="Enter recipient's email address" required>
                </div>

                <div class="form-group">
                    <label for="subject">
                        <i class="fa-solid fa-heading"></i> Subject
                    </label>
                    <input type="text" id="subject" name="subject" placeholder="Enter email subject" required>
                </div>

                <div class="form-group">
                    <label for="message">
                        <i class="fa-solid fa-message"></i> Message
                    </label>
                    <textarea id="message" name="message" placeholder="Type your message here..." required></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn-cancel" onclick="window.history.back()">
                        <i class="fa-solid fa-times"></i> Cancel
                    </button>
                    <button type="submit" name="send" value="1" class="btn-submit">
                        <i class="fa-solid fa-paper-plane"></i> Send Email
                    </button>
                </div>
            </form>
        </div>
    </section>

    <script src="static/message.js"></script>
</body>
</html>
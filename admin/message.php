<?php
session_start();
require_once '../config/db_connection.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/7.0.1/css/all.min.css"/>
    <link rel="stylesheet" href="static/message.css">
    
</head>
<body>
        <div class="main-content">
            <div class="header">
                <h1><i class="fas fa-comments"></i> Messages</h1>
            </div>

            <div class="content-body">
                <div class="tabs">
                    <button class="tab-btn active" data-tab="compose">
                        <i class="fas fa-pen"></i> Compose Message
                    </button>
                    <button class="tab-btn" data-tab="inbox">
                        <i class="fas fa-inbox"></i> Inbox
                    </button>
                    <button class="tab-btn" data-tab="sent">
                        <i class="fas fa-paper-plane"></i> Sent Messages
                    </button>
                    <button class="tab-btn" data-tab="templates">
                        <i class="fas fa-file-alt"></i> Templates
                    </button>
                </div>
                <div class="tab-content active" id="compose-tab">
                    <div class="card">
                        <h2>Compose New Message</h2>
                        <form id="messageForm" class="message-form">
                            
                            <div class="form-group">
                                <label>Send To:</label>
                                <div class="radio-group">
                                    <label class="radio-label">
                                        <input type="radio" name="send_to" value="patient" checked>
                                        <span>Patient</span>
                                    </label>
                                    <label class="radio-label">
                                        <input type="radio" name="send_to" value="doctor">
                                        <span>Doctor</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="recipient">Select Recipient *</label>
                                <select id="recipient" name="recipient" required>
                                    <option value="">Choose a recipient...</option>
                                </select>
                                <small id="recipientPhone"></small>
                            </div>

                            <div class="form-group">
                                <label for="messageType">Message Type *</label>
                                <select id="messageType" name="message_type" required>
                                    <option value="">Select message type...</option>
                                    <option value="appointment_reminder">Appointment Reminder</option>
                                    <option value="appointment_confirmation">Appointment Confirmation</option>
                                    <option value="appointment_cancelled">Appointment Cancelled</option>
                                    <option value="appointment_rescheduled">Appointment Rescheduled</option>
                                    <option value="custom">Custom Message</option>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="subject">Subject (Optional)</label>
                                <input type="text" id="subject" name="subject" placeholder="Message subject">
                            </div>

                            <div class="form-group">
                                <label for="message">Message *</label>
                                <textarea id="message" name="message" rows="6" placeholder="Type your message here..." required></textarea>
                                <div class="char-count">
                                    <span id="charCount">0</span> / 1000 characters
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="appointment_id">Related Appointment (Optional)</label>
                                <select id="appointment_id" name="appointment_id">
                                    <option value="">No appointment</option>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Send Message
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="fas fa-redo"></i> Clear
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="tab-content" id="inbox-tab">
                    <div class="card">
                        <h2>Inbox</h2>
                        <div class="message-list">
                            <div class="message-item">
                                <div class="message-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <h4>John Doe (Patient)</h4>
                                        <span class="message-time">2 hours ago</span>
                                    </div>
                                    <p>Can I reschedule my appointment to tomorrow?</p>
                                    <span class="message-status unread">Unread</span>
                                </div>
                            </div>

                            <div class="message-item">
                                <div class="message-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <h4>Dr. Sarah Smith</h4>
                                        <span class="message-time">5 hours ago</span>
                                    </div>
                                    <p>I'll be running 15 minutes late for appointments today.</p>
                                    <span class="message-status read">Read</span>
                                </div>
                            </div>

                            <div class="message-item">
                                <div class="message-avatar">
                                    <i class="fas fa-user-circle"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <h4>Jane Wilson (Patient)</h4>
                                        <span class="message-time">1 day ago</span>
                                    </div>
                                    <p>Thank you for the appointment confirmation!</p>
                                    <span class="message-status read">Read</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tab-content" id="sent-tab">
                    <div class="card">
                        <h2>Sent Messages</h2>
                        <div class="filter-bar">
                            <input type="text" id="searchSent" placeholder="Search sent messages...">
                            <select id="filterDate">
                                <option value="">All dates</option>
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                            </select>
                        </div>
                        <div class="message-list">
                            <div class="message-item sent">
                                <div class="message-avatar">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <h4>To: John Doe (Patient) - +91-9876543210</h4>
                                        <span class="message-time">Today, 10:30 AM</span>
                                    </div>
                                    <p>Hi John, This is a reminder of your appointment with Dr. Sarah on 2026-02-25 at 3:00 PM.</p>
                                    <span class="message-status delivered">Delivered</span>
                                </div>
                            </div>

                            <div class="message-item sent">
                                <div class="message-avatar">
                                    <i class="fas fa-paper-plane"></i>
                                </div>
                                <div class="message-content">
                                    <div class="message-header">
                                        <h4>To: Mike Brown (Patient) - +91-9876543211</h4>
                                        <span class="message-time">Yesterday, 02:15 PM</span>
                                    </div>
                                    <p>Your appointment #APT001 is confirmed for 2026-02-24 at 2:00 PM</p>
                                    <span class="message-status delivered">Delivered</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="tab-content" id="templates-tab">
                    <div class="card">
                        <h2>Message Templates</h2>
                        <button class="btn btn-primary" id="addTemplateBtn">
                            <i class="fas fa-plus"></i> Add New Template
                        </button>
                        
                        <div class="templates-grid">
                            <div class="template-card">
                                <h4>Appointment Reminder</h4>
                                <p>Hi {patient_name}, This is a reminder of your appointment with Dr. {doctor_name} on {date} at {time}. Please arrive 10 minutes early.</p>
                                <div class="template-actions">
                                    <button class="btn-sm btn-use"><i class="fas fa-copy"></i> Use</button>
                                    <button class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>

                            <div class="template-card">
                                <h4>Appointment Confirmation</h4>
                                <p>Your appointment #{appointment_number} is confirmed for {date} at {time}. Reply CANCEL to cancel or RESCHEDULE to request a new time.</p>
                                <div class="template-actions">
                                    <button class="btn-sm btn-use"><i class="fas fa-copy"></i> Use</button>
                                    <button class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>

                            <div class="template-card">
                                <h4>Appointment Cancelled</h4>
                                <p>Your appointment on {date} has been cancelled. If you wish to reschedule, please contact us at {phone_number}. We regret any inconvenience.</p>
                                <div class="template-actions">
                                    <button class="btn-sm btn-use"><i class="fas fa-copy"></i> Use</button>
                                    <button class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>

                            <div class="template-card">
                                <h4>Appointment Rescheduled</h4>
                                <p>Your appointment has been rescheduled to {new_date} at {new_time}. Your previous appointment on {old_date} has been cancelled.</p>
                                <div class="template-actions">
                                    <button class="btn-sm btn-use"><i class="fas fa-copy"></i> Use</button>
                                    <button class="btn-sm btn-edit"><i class="fas fa-edit"></i> Edit</button>
                                    <button class="btn-sm btn-delete"><i class="fas fa-trash"></i> Delete</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal" id="messageModal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <h2 id="modalTitle"></h2>
            <p id="modalMessage"></p>
            <button class="btn btn-primary" onclick="closeModal()">OK</button>
        </div>
    </div>

    <script src="static/message.js"></script>
</body>
</html>
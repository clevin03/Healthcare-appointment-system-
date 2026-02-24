document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const tabName = this.getAttribute('data-tab');
            tabBtns.forEach(b => b.classList.remove('active'));
            tabContents.forEach(content => content.classList.remove('active'));
            
            this.classList.add('active');
            document.getElementById(tabName + '-tab').classList.add('active');
            
            if (tabName === 'inbox') {
                loadInboxMessages();
            } else if (tabName === 'sent') {
                loadSentMessages();
            }
        });
    });

    const sendToRadios = document.querySelectorAll('input[name="send_to"]');
    sendToRadios.forEach(radio => {
        radio.addEventListener('change', loadRecipients);
    });

    loadRecipients();

    const messageField = document.getElementById('message');
    messageField.addEventListener('input', function() {
        const charCount = this.value.length;
        document.getElementById('charCount').textContent = charCount;
        
        if (charCount > 1000) {
            this.value = this.value.substring(0, 1000);
            document.getElementById('charCount').textContent = '1000';
        }
    });

    const messageType = document.getElementById('messageType');
    messageType.addEventListener('change', function() {
        const templates = {
            'appointment_reminder': 'Hi {patient_name}, This is a reminder of your appointment with Dr. {doctor_name} on {date} at {time}. Please arrive 10 minutes early.',
            'appointment_confirmation': 'Your appointment #{appointment_number} is confirmed for {date} at {time}. Reply CANCEL to cancel or RESCHEDULE to request a new time.',
            'appointment_cancelled': 'Your appointment on {date} has been cancelled. If you wish to reschedule, please contact us. We regret any inconvenience.',
            'appointment_rescheduled': 'Your appointment has been rescheduled to {new_date} at {new_time}. Your previous appointment on {old_date} has been cancelled.',
            'custom': ''
        };

        if (templates[this.value]) {
            document.getElementById('message').value = templates[this.value];
            updateCharCount();
        }
    });

    const messageForm = document.getElementById('messageForm');
    messageForm.addEventListener('submit', function(e) {
        e.preventDefault();
        sendMessage();
    });

    const modal = document.getElementById('messageModal');
    const closeBtn = document.querySelector('.close');
    closeBtn.addEventListener('click', closeModal);
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeModal();
        }
    });

    const addTemplateBtn = document.getElementById('addTemplateBtn');
    if (addTemplateBtn) {
        addTemplateBtn.addEventListener('click', addNewTemplate);
    }

    const useButtons = document.querySelectorAll('.btn-use');
    const editButtons = document.querySelectorAll('.btn-edit');
    const deleteButtons = document.querySelectorAll('.btn-delete');

    useButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const templateText = this.closest('.template-card').querySelector('p').textContent;
            document.getElementById('message').value = templateText;
            updateCharCount();
            document.querySelector('.tab-btn[data-tab="compose"]').click();
        });
    });

    editButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            showMessage('Edit Template', 'Template editing functionality coming soon.');
        });
    });

    deleteButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            if (confirm('Are you sure you want to delete this template?')) {
                this.closest('.template-card').remove();
                showMessage('Success', 'Template has been deleted.');
            }
        });
    });
});

function parseJsonResponse(response) {
    return response.text().then(text => {
        if (!text) {
            throw new Error('Empty response from server');
        }

        try {
            return JSON.parse(text);
        } catch (err) {
            throw new Error('Invalid JSON response: ' + text);
        }
    });
}

function loadRecipients() {
    const sendTo = document.querySelector('input[name="send_to"]:checked').value;
    const recipientSelect = document.getElementById('recipient');
    
    recipientSelect.innerHTML = '<option value="">Loading...</option>';

    fetch(`api/message_handler.php?action=get_recipients&type=${sendTo}`)
        .then(parseJsonResponse)
        .then(data => {
            recipientSelect.innerHTML = '<option value="">Choose a recipient...</option>';
            
            if (data.success && data.recipients) {
                data.recipients.forEach(item => {
                    const option = document.createElement('option');
                    option.value = item.id;
                    option.textContent = item.name;
                    option.dataset.phone = item.phone || '';
                    recipientSelect.appendChild(option);
                });
            } else {
                recipientSelect.innerHTML = '<option value="">No recipients available</option>';
            }
        })
        .catch(error => {
            console.error('Error loading recipients:', error);
            recipientSelect.innerHTML = '<option value="">Error loading recipients</option>';
        });

    recipientSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const phone = selectedOption.dataset.phone || '';
        document.getElementById('recipientPhone').textContent = phone ? `Phone: ${phone}` : '';
    });
}

function sendMessage() {
    const sendTo = document.querySelector('input[name="send_to"]:checked').value;
    const recipient = document.getElementById('recipient').value;
    const message = document.getElementById('message').value;
    const messageType = document.getElementById('messageType').value;
    const subject = document.getElementById('subject').value;
    const appointmentId = document.getElementById('appointment_id').value;

    if (!recipient || !message || !messageType) {
        showMessage('Validation Error', 'Please fill in all required fields.');
        return;
    }

    const submitBtn = document.querySelector('.btn-primary');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending...';

    const formData = new FormData();
    formData.append('action', 'send');
    formData.append('recipient_id', recipient);
    formData.append('recipient_type', sendTo);
    formData.append('message', message);
    formData.append('message_type', messageType);
    formData.append('subject', subject);
    if (appointmentId) {
        formData.append('appointment_id', appointmentId);
    }

    fetch('api/message_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(parseJsonResponse)
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;

        if (data.success) {
            document.getElementById('messageForm').reset();
            document.getElementById('charCount').textContent = '0';
            document.getElementById('recipientPhone').textContent = '';

            showMessage('Success', '✓ Message sent successfully!');

            setTimeout(() => {
                document.querySelector('.tab-btn[data-tab="sent"]').click();
                loadSentMessages();
            }, 1500);
        } else {
            showMessage('Error', data.message || 'Failed to send message');
        }
    })
    .catch(error => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
        console.error('Error:', error);
        showMessage('Error', 'Network error: ' + error.message);
    });
}

function updateCharCount() {
    const charCount = document.getElementById('message').value.length;
    document.getElementById('charCount').textContent = charCount;
}

function showMessage(title, message) {
    const modal = document.getElementById('messageModal');
    document.getElementById('modalTitle').textContent = title;
    document.getElementById('modalMessage').textContent = message;
    modal.style.display = 'block';
}

function closeModal() {
    const modal = document.getElementById('messageModal');
    modal.style.display = 'none';
}

function addNewTemplate() {
    const templateName = prompt('Enter template name:');
    if (templateName) {
        const templateText = prompt('Enter template text:');
        if (templateText) {
            showMessage('Success', 'Template added successfully!');
        }
    }
}

function loadAppointments() {
    const appointmentSelect = document.getElementById('appointment_id');
    
    fetch('api/message_handler.php?action=get_appointments')
        .then(parseJsonResponse)
        .then(data => {
            if (data.success && data.appointments) {
                data.appointments.forEach(apt => {
                    const option = document.createElement('option');
                    option.value = apt.appointment_id;
                    option.textContent = `${apt.appointment_number} - ${apt.patient_name} (${apt.appointment_date} ${apt.appointment_time})`;
                    appointmentSelect.appendChild(option);
                });
            }
        })
        .catch(error => console.error('Error loading appointments:', error));
}

function loadInboxMessages() {
    fetch('api/message_handler.php?action=get_inbox')
        .then(parseJsonResponse)
        .then(data => {
            const messageList = document.querySelector('#inbox-tab .message-list');
            messageList.innerHTML = '';

            if (data.success && data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const messageItem = document.createElement('div');
                    messageItem.className = 'message-item' + (msg.is_read ? '' : ' unread');
                    messageItem.innerHTML = `
                        <div class="message-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <h4>${msg.sender_name}</h4>
                                <span class="message-time">${formatDate(msg.created_at)}</span>
                            </div>
                            <p>${escapeHtml(msg.message)}</p>
                            <span class="message-status ${msg.is_read ? 'read' : 'unread'}">${msg.is_read ? 'Read' : 'Unread'}</span>
                        </div>
                    `;
                    
                    messageItem.addEventListener('click', () => {
                        if (!msg.is_read) {
                            markMessageAsRead(msg.message_id);
                            messageItem.classList.remove('unread');
                        }
                    });
                    
                    messageList.appendChild(messageItem);
                });
            } else {
                messageList.innerHTML = '<div class="message-item"><p>No messages in inbox</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading inbox:', error);
            document.querySelector('#inbox-tab .message-list').innerHTML = '<div class="message-item"><p>Error loading messages</p></div>';
        });
}

function loadSentMessages() {
    fetch('api/message_handler.php?action=get_sent')
        .then(parseJsonResponse)
        .then(data => {
            const messageList = document.querySelector('#sent-tab .message-list');
            messageList.innerHTML = '';

            if (data.success && data.messages && data.messages.length > 0) {
                data.messages.forEach(msg => {
                    const messageItem = document.createElement('div');
                    messageItem.className = 'message-item sent';
                    messageItem.innerHTML = `
                        <div class="message-avatar">
                            <i class="fas fa-paper-plane"></i>
                        </div>
                        <div class="message-content">
                            <div class="message-header">
                                <h4>To: ${msg.recipient_name} (${msg.phone})</h4>
                                <span class="message-time">${formatDate(msg.created_at)}</span>
                            </div>
                            <p>${escapeHtml(msg.message)}</p>
                            <span class="message-status delivered">${msg.status.charAt(0).toUpperCase() + msg.status.slice(1)}</span>
                        </div>
                    `;
                    messageList.appendChild(messageItem);
                });
            } else {
                messageList.innerHTML = '<div class="message-item"><p>No sent messages</p></div>';
            }
        })
        .catch(error => {
            console.error('Error loading sent messages:', error);
            document.querySelector('#sent-tab .message-list').innerHTML = '<div class="message-item"><p>Error loading messages</p></div>';
        });
}

function markMessageAsRead(messageId) {
    const formData = new FormData();
    formData.append('action', 'mark_as_read');
    formData.append('message_id', messageId);

    fetch('api/message_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(parseJsonResponse)
    .catch(error => console.error('Error marking as read:', error));
}


function formatDate(dateString) {
    const date = new Date(dateString);
    const today = new Date();
    const yesterday = new Date(today);
    yesterday.setDate(yesterday.getDate() - 1);

    if (date.toDateString() === today.toDateString()) {
        return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else if (date.toDateString() === yesterday.toDateString()) {
        return 'Yesterday, ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    } else {
        return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

window.addEventListener('load', loadAppointments);

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchSent');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const messageItems = document.querySelectorAll('#sent-tab .message-item');
            
            messageItems.forEach(item => {
                const text = item.textContent.toLowerCase();
                item.style.display = text.includes(searchTerm) ? 'flex' : 'none';
            });
        });
    }

    const filterDate = document.getElementById('filterDate');
    if (filterDate) {
        filterDate.addEventListener('change', function() {
            console.log('Filter by:', this.value);
        });
    }
});

document.addEventListener('keydown', function(event) {
    if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
        const messageForm = document.getElementById('messageForm');
        if (messageForm && document.querySelector('.tab-content.active') === document.getElementById('compose-tab')) {
            event.preventDefault();
            sendMessage();
        }
    }

    if ((event.ctrlKey || event.metaKey) && event.key === '1') {
        event.preventDefault();
        document.querySelector('.tab-btn[data-tab="compose"]').click();
    }

    if ((event.ctrlKey || event.metaKey) && event.key === '2') {
        event.preventDefault();
        document.querySelector('.tab-btn[data-tab="inbox"]').click();
    }

    if ((event.ctrlKey || event.metaKey) && event.key === '3') {
        event.preventDefault();
        document.querySelector('.tab-btn[data-tab="sent"]').click();
    }

    if ((event.ctrlKey || event.metaKey) && event.key === '4') {
        event.preventDefault();
        document.querySelector('.tab-btn[data-tab="templates"]').click();
    }
});

document.addEventListener('DOMContentLoaded', function() {
    initializeMenuListeners();
    initializeSearchForm();
});

function initializeMenuListeners() {
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            menuItems.forEach(m => m.classList.remove('active'));
            
            this.classList.add('active');
            
            const href = this.getAttribute('href');
            handleNavigation(href);
        });
    });
}

function handleNavigation(page) {
    console.log('Navigating to:', page);
    
    switch(page) {
        case '#home':
            window.location.href = 'patient_dashboard.php';
            break;
        case '#chatbot':
            window.location.href = 'chatbot.php';
            break;
        case '#doctors':
            window.location.href = 'allDoctors.php';
            break;
        case '#sessions':
            window.location.href = 'scheduled_sessions.php';
            break;
        case '#bookings':
            window.location.href = 'my_bookings.php';
            break;
        case '#settings':
            window.location.href = 'patientSettings.php';
            break;
        default:
            console.log('Unknown page');
    }
}

function openChatbotModal() {
    const chatbotModal = document.getElementById('chatbotModal');
    const chatbotInput = document.getElementById('chatbotInput');

    if (chatbotModal) {
        chatbotModal.classList.add('active');

        if (chatbotInput) {
            chatbotInput.focus();
        }
    }
}

function initializeSearchForm() {
    const searchForm = document.querySelector('.search-form');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const searchInput = document.getElementById('doctor-search');
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm) {
                window.location.href = 'allDoctors.php?search=' + encodeURIComponent(searchTerm);
            } else {
                alert('Please enter a doctor name');
            }
        });
    }
}

function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

function formatTime(dateString) {
    const options = { hour: '2-digit', minute: '2-digit', hour12: true };
    return new Date(dateString).toLocaleTimeString('en-US', options);
}

function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

async function apiCall(endpoint, options = {}) {
    try {
        const response = await fetch(endpoint, {
            headers: {
                'Content-Type': 'application/json',
                ...options.headers
            },
            ...options
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    } catch (error) {
        console.error('API Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
        throw error;
    }
}

function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

function validatePhone(phone) {
    const phoneRegex = /^[0-9]{10,}$/;
    return phoneRegex.test(phone.replace(/\D/g, ''));
}

function formatPhoneNumber(phone) {
    const cleaned = phone.replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    
    return phone;
}

const LocalStorage = {
    set: function(key, value) {
        try {
            localStorage.setItem(key, JSON.stringify(value));
        } catch (error) {
            console.error('LocalStorage set error:', error);
        }
    },
    get: function(key) {
        try {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        } catch (error) {
            console.error('LocalStorage get error:', error);
            return null;
        }
    },
    remove: function(key) {
        try {
            localStorage.removeItem(key);
        } catch (error) {
            console.error('LocalStorage remove error:', error);
        }
    },
    clear: function() {
        try {
            localStorage.clear();
        } catch (error) {
            console.error('LocalStorage clear error:', error);
        }
    }
};

window.DashboardUtils = {
    formatDate,
    formatTime,
    showNotification,
    apiCall,
    validateEmail,
    validatePhone,
    formatPhoneNumber,
    LocalStorage
};

// Chatbot
document.addEventListener('DOMContentLoaded', function() {
    initializeChatbot();
});

function initializeChatbot() {
    const chatbotIcon = document.getElementById('chatbotIcon');
    const chatbotModal = document.getElementById('chatbotModal');
    const closeChatbot = document.getElementById('closeChatbot');
    const sendMessageBtn = document.getElementById('sendMessage');
    const chatbotInput = document.getElementById('chatbotInput');
    const chatbotMessages = document.getElementById('chatbotMessages');

    if (chatbotIcon) {
        chatbotIcon.addEventListener('click', function() {
            if (chatbotModal.classList.contains('active')) {
                chatbotModal.classList.remove('active');
            } else {
                openChatbotModal();
            }
        });
    }

    if (closeChatbot) {
        closeChatbot.addEventListener('click', function() {
            chatbotModal.classList.remove('active');
        });
    }

    if (sendMessageBtn) {
        sendMessageBtn.addEventListener('click', function() {
            sendChatMessage();
        });
    }

    if (chatbotInput) {
        chatbotInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                sendChatMessage();
            }
        });
    }

    function sendChatMessage() {
        const message = chatbotInput.value.trim();
        
        if (message === '') {
            return;
        }

        addMessage(message, 'user');
        chatbotInput.value = '';

        setTimeout(() => {
            const botResponse = getBotResponse(message);
            addMessage(botResponse, 'bot');
        }, 1000);
    }

    function addMessage(text, sender) {
        const messageDiv = document.createElement('div');
        messageDiv.className = sender === 'user' ? 'user-message' : 'bot-message';

        const avatar = document.createElement('div');
        avatar.className = 'message-avatar';
        avatar.innerHTML = sender === 'user' ? '<i class="fas fa-user"></i>' : '<i class="fas fa-robot"></i>';

        const content = document.createElement('div');
        content.className = 'message-content';
        
        const messagePara = document.createElement('p');
        messagePara.textContent = text;
        
        const timeSpan = document.createElement('span');
        timeSpan.className = 'message-time';
        const now = new Date();
        timeSpan.textContent = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
        
        content.appendChild(messagePara);
        content.appendChild(timeSpan);

        messageDiv.appendChild(avatar);
        messageDiv.appendChild(content);

        chatbotMessages.appendChild(messageDiv);
        chatbotMessages.scrollTop = chatbotMessages.scrollHeight;
    }

    function getBotResponse(message) {
        const lowerMessage = message.toLowerCase();

        if (lowerMessage.includes('hello') || lowerMessage.includes('hi') || lowerMessage.includes('hey')) {
            return 'Hello! How can I assist you with your healthcare needs today?';
        } else if (lowerMessage.includes('appointment') || lowerMessage.includes('book')) {
            return 'To book an appointment, please navigate to the "Scheduled Sessions" section from the menu. You can select a doctor and available time slot.';
        } else if (lowerMessage.includes('doctor')) {
            return 'You can view all available doctors by clicking on "All Doctors" in the menu. There you can see their specialties and availability.';
        } else if (lowerMessage.includes('cancel')) {
            return 'To cancel an appointment, go to "My Bookings" section where you can manage all your scheduled appointments.';
        } else if (lowerMessage.includes('time') || lowerMessage.includes('schedule')) {
            return 'You can view your upcoming appointments and session times in the "My Bookings" section or from the homepage dashboard.';
        } else if (lowerMessage.includes('help')) {
            return 'I can help you with:\n- Booking appointments\n- Finding doctors\n- Viewing your bookings\n- Checking session schedules\n\nWhat would you like to do?';
        } else if (lowerMessage.includes('thank')) {
            return 'You\'re welcome! Is there anything else I can help you with?';
        } else if (lowerMessage.includes('bye') || lowerMessage.includes('goodbye')) {
            return 'Goodbye! Take care and stay healthy!';
        } else {
            return 'I understand you\'re asking about "' + message + '". For specific inquiries, please contact our support team or navigate through the menu options. How else can I assist you?';
        }
    }
}

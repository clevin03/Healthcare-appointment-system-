// Patient Dashboard JavaScript

document.addEventListener('DOMContentLoaded', function() {
    initializeMenuListeners();
    initializeLogoutButton();
    initializeSearchForm();
});

// Initialize menu navigation
function initializeMenuListeners() {
    const menuItems = document.querySelectorAll('.menu-item');
    
    menuItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all items
            menuItems.forEach(m => m.classList.remove('active'));
            
            // Add active class to clicked item
            this.classList.add('active');
            
            // Handle navigation
            const href = this.getAttribute('href');
            handleNavigation(href);
        });
    });
}

// Handle navigation based on menu selection
function handleNavigation(page) {
    console.log('Navigating to:', page);
    
    switch(page) {
        case '#home':
            console.log('Home page');
            break;
        case '#doctors':
            window.location.href = '../doctor/all_doctors.php';
            break;
        case '#sessions':
            window.location.href = 'scheduled_sessions.php';
            break;
        case '#bookings':
            window.location.href = 'my_bookings.php';
            break;
        case '#settings':
            window.location.href = 'settings.php';
            break;
        default:
            console.log('Unknown page');
    }
}

// Initialize logout button
function initializeLogoutButton() {
    const logoutBtn = document.querySelector('.logout-btn');
    
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (confirm('Are you sure you want to log out?')) {
                fetch('../logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    }
                })
                .then(response => {
                    window.location.href = '../login.php';
                })
                .catch(error => {
                    console.error('Logout error:', error);
                    window.location.href = '../login.php';
                });
            }
        });
    }
}

// Initialize search form
function initializeSearchForm() {
    const searchForm = document.querySelector('.search-form');
    
    if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const searchInput = document.getElementById('doctor-search');
            const searchTerm = searchInput.value.trim();
            
            if (searchTerm) {
                // Redirect to doctors page with search query
                window.location.href = 'all_doctors.php?search=' + encodeURIComponent(searchTerm);
            } else {
                alert('Please enter a doctor name');
            }
        });
    }
}

// Format date to readable format
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(dateString).toLocaleDateString('en-US', options);
}

// Format time to readable format
function formatTime(dateString) {
    const options = { hour: '2-digit', minute: '2-digit', hour12: true };
    return new Date(dateString).toLocaleTimeString('en-US', options);
}

// Show notification
function showNotification(message, type = 'info') {
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

// API call helper
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

// Validate email format
function validateEmail(email) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return emailRegex.test(email);
}

// Validate phone format
function validatePhone(phone) {
    const phoneRegex = /^[0-9]{10,}$/;
    return phoneRegex.test(phone.replace(/\D/g, ''));
}

// Format phone number
function formatPhoneNumber(phone) {
    const cleaned = phone.replace(/\D/g, '');
    const match = cleaned.match(/^(\d{3})(\d{3})(\d{4})$/);
    
    if (match) {
        return `(${match[1]}) ${match[2]}-${match[3]}`;
    }
    
    return phone;
}

// Local storage management
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

// Export for use in other scripts
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

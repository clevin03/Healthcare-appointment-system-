document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('registerForm');
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    const email = document.getElementById('email');
    const phone = document.getElementById('phone');


    password.addEventListener('input', validatePassword);
    confirmPassword.addEventListener('input', validateConfirmPassword);
    email.addEventListener('blur', validateEmail);
    phone.addEventListener('input', formatPhone);

    form.addEventListener('submit', handleSubmit);
    
    const dobInput = document.getElementById('date_of_birth');
    const today = new Date();
    const maxDate = new Date(today.getFullYear() - 1, today.getMonth(), today.getDate());
    dobInput.max = maxDate.toISOString().split('T')[0];
    
    const minDate = new Date(today.getFullYear() - 120, today.getMonth(), today.getDate());
    dobInput.min = minDate.toISOString().split('T')[0];
});

function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = input.parentElement.querySelector('.toggle-password');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function validatePassword() {
    const password = document.getElementById('password');
    const value = password.value;
    const hint = password.parentElement.nextElementSibling;

    const minLength = value.length >= 8;
    const hasLetters = /[a-zA-Z]/.test(value);
    const hasNumbers = /\d/.test(value);
    
    if (value.length === 0) {
        password.classList.remove('valid', 'invalid');
        hint.style.color = 'var(--text-light)';
        hint.textContent = 'At least 8 characters with letters and numbers';
        return false;
    }
    
    if (minLength && hasLetters && hasNumbers) {
        password.classList.remove('invalid');
        password.classList.add('valid');
        hint.style.color = 'var(--success-text)';
        hint.textContent = '✓ Strong password';
        return true;
    } else {
        password.classList.remove('valid');
        password.classList.add('invalid');
        hint.style.color = 'var(--error-text)';
        
        if (!minLength) {
            hint.textContent = '✗ Password must be at least 8 characters';
        } else if (!hasLetters) {
            hint.textContent = '✗ Password must contain letters';
        } else if (!hasNumbers) {
            hint.textContent = '✗ Password must contain numbers';
        }
        return false;
    }
}

function validateConfirmPassword() {
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');
    
    if (confirmPassword.value.length === 0) {
        confirmPassword.classList.remove('valid', 'invalid');
        return false;
    }
    
    if (password.value === confirmPassword.value) {
        confirmPassword.classList.remove('invalid');
        confirmPassword.classList.add('valid');
        return true;
    } else {
        confirmPassword.classList.remove('valid');
        confirmPassword.classList.add('invalid');
        return false;
    }
}

function validateEmail() {
    const email = document.getElementById('email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    
    if (email.value.length === 0) {
        email.classList.remove('valid', 'invalid');
        return false;
    }
    
    if (emailRegex.test(email.value)) {
        email.classList.remove('invalid');
        email.classList.add('valid');
        return true;
    } else {
        email.classList.remove('valid');
        email.classList.add('invalid');
        return false;
    }
}

function formatPhone() {
    const phone = document.getElementById('phone');
    let value = phone.value.replace(/\D/g, '');

    if (value.length > 11) {
        value = value.slice(0, 11);
    }
    
    phone.value = value;
    
    if (value.length >= 10) {
        phone.classList.remove('invalid');
        phone.classList.add('valid');
    } else if (value.length > 0) {
        phone.classList.remove('valid');
        phone.classList.add('invalid');
    } else {
        phone.classList.remove('valid', 'invalid');
    }
}

function showAlert(message, type) {
    const alert = document.getElementById('alert-message');
    alert.textContent = message;
    alert.className = `alert ${type}`;
    alert.style.display = 'block';
    window.scrollTo({ top: 0, behavior: 'smooth' });

    setTimeout(() => {
        alert.style.display = 'none';
    }, 5000);
}

function handleSubmit(e) {
    e.preventDefault();
    
    document.getElementById('alert-message').style.display = 'none';
    
    const form = e.target;
    const formData = new FormData(form);
    let isValid = true;
    let errorMessage = '';

    const requiredFields = [
        { name: 'first_name', label: 'First Name' },
        { name: 'last_name', label: 'Last Name' },
        { name: 'email', label: 'Email' },
        { name: 'phone', label: 'Phone' },
        { name: 'date_of_birth', label: 'Date of Birth' },
        { name: 'gender', label: 'Gender' },
        { name: 'address', label: 'Address' },
        { name: 'password', label: 'Password' },
        { name: 'confirm_password', label: 'Confirm Password' }
    ];
    
    for (let field of requiredFields) {
        if (!formData.get(field.name) || formData.get(field.name).trim() === '') {
            isValid = false;
            errorMessage = `Please fill in the ${field.label} field.`;
            break;
        }
    }
    
    if (!isValid) {
        showAlert(errorMessage, 'error');
        return;
    }
    
    if (!validateEmail()) {
        showAlert('Please enter a valid email address.', 'error');
        return;
    }
    
    const phone = document.getElementById('phone').value;
    if (phone.length < 10) {
        showAlert('Phone number must be at least 10 digits.', 'error');
        return;
    }
    
    if (!validatePassword()) {
        showAlert('Password must be at least 8 characters and contain letters and numbers.', 'error');
        return;
    }
    
    if (!validateConfirmPassword()) {
        showAlert('Passwords do not match.', 'error');
        return;
    }
    
    const terms = document.getElementById('terms');
    if (!terms.checked) {
        showAlert('Please agree to the Terms and Conditions.', 'error');
        return;
    }

    const submitBtn = form.querySelector('.btn-submit');
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;

    fetch('register_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        
        if (data.success) {
            showAlert(data.message || 'Registration successful! Redirecting...', 'success');
            setTimeout(() => {
                window.location.href = data.redirect || '/auth/login.php';
            }, 2000);
        } else {
            showAlert(data.message || 'Registration failed.', 'error');
        }
    })
    .catch(error => {
        submitBtn.classList.remove('loading');
        submitBtn.disabled = false;
        showAlert('An error occurred during registration.', 'error');
        console.error('Error:', error);
    });
}

document.querySelectorAll('.form-group input, .form-group select, .form-group textarea').forEach(element => {
    element.addEventListener('focus', function() {
        this.parentElement.style.transform = 'translateY(-2px)';
        this.parentElement.style.transition = 'transform 0.2s ease';
    });
    
    element.addEventListener('blur', function() {
        this.parentElement.style.transform = 'translateY(0)';
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.email-form');
    const submitBtn = document.querySelector('.btn-submit');
    
    if (form) {
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const email = document.getElementById('email').value.trim();
            const subject = document.getElementById('subject').value.trim();
            const message = document.getElementById('message').value.trim();

            if (!email) {
                alert('Please enter an email address');
                document.getElementById('email').focus();
                return false;
            }

            if (!isValidEmail(email)) {
                alert('Please enter a valid email address');
                document.getElementById('email').focus();
                return false;
            }

            if (!subject) {
                alert('Please enter a subject');
                document.getElementById('subject').focus();
                return false;
            }

            if (subject.length < 3) {
                alert('Subject must be at least 3 characters long');
                document.getElementById('subject').focus();
                return false;
            }

            if (!message) {
                alert('Please enter a message');
                document.getElementById('message').focus();
                return false;
            }

            if (message.length < 10) {
                alert('Message must be at least 10 characters long');
                document.getElementById('message').focus();
                return false;
            }

            const originalHTML = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Sending...';

            const formData = new FormData(form);
            
            fetch(form.action, {
                method: 'POST',
                body: formData
            })
            .then(async (response) => {
                const raw = await response.text();
                let data;

                try {
                    data = JSON.parse(raw);
                } catch (parseError) {
                    throw new Error(raw || 'Invalid server response');
                }

                if (!response.ok) {
                    throw new Error(data.message || 'Request failed');
                }

                return data;
            })
            .then(data => {
                if (data.success) {
                    submitBtn.innerHTML = '<i class="fa-solid fa-check-circle"></i> Sent Successfully!';
                    submitBtn.style.background = 'linear-gradient(135deg, #51cf66 0%, #37b679 100%)';
                    alert(data.message);
                    form.reset();
                    
                    setTimeout(() => {
                        submitBtn.innerHTML = originalHTML;
                        submitBtn.style.background = 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)';
                        submitBtn.disabled = false;
                    }, 2000);
                } else {
                    alert(data.message);
                    submitBtn.innerHTML = originalHTML;
                    submitBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert(error.message || 'An error occurred. Please try again.');
                submitBtn.innerHTML = originalHTML;
                submitBtn.disabled = false;
            });
        });

        const inputs = form.querySelectorAll('input, textarea');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentElement.querySelector('label').style.color = '#667eea';
            });
            
            input.addEventListener('blur', function() {
                this.parentElement.querySelector('label').style.color = '#333';
            });
        });
    }
});

const textarea = document.getElementById('message');
if (textarea) {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = (this.scrollHeight) + 'px';
    });
}

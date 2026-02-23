function logout() {
    if (confirm('Are you sure you want to logout?')) {
        window.location.href = '../login.php';
    }
}

document.querySelectorAll('.sidebar ul li a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.sidebar ul li a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});

function openAddModal() {
    const modal = document.getElementById('appointmentModal');
    if (modal) {
        modal.style.display = 'block';
        document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Add New Appointment';
        document.getElementById('appointmentForm').reset();
        document.getElementById('appointmentId').value = '';
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('appointmentDate').value = today;
    }
}

function closeModal() {
    const modal = document.getElementById('appointmentModal');
    if (modal) {
        modal.style.display = 'none';
        document.getElementById('appointmentForm').reset();
    }
}

window.onclick = function(event) {
    const modal = document.getElementById('appointmentModal');
    if (modal && event.target == modal) {
        modal.style.display = 'none';
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const appointmentForm = document.getElementById('appointmentForm');
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const appointmentId = document.getElementById('appointmentId').value;
            
            formData.append('action', appointmentId ? 'edit' : 'add');
            
            try {
                const response = await fetch('api/appointment_handler.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.success) {
                    alert(appointmentId ? 'Appointment updated successfully!' : 'Appointment created successfully!');
                    closeModal();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            } catch (error) {
                alert('Error: ' + error.message);
            }
        });
    }
});
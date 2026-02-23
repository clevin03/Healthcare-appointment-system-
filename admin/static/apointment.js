
let currentAppointmentId = null;

function openAddModal() {
    document.getElementById('appointmentModal').style.display = 'block';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus-circle"></i> Add New Appointment';
    document.getElementById('appointmentForm').reset();
    document.getElementById('appointmentId').value = '';
}

function editAppointment(id) {
    currentAppointmentId = id;
    document.getElementById('appointmentModal').style.display = 'block';
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Appointment';
    fetch('api/appointment_handler.php?action=get&id=' + id)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                document.getElementById('appointmentId').value = data.appointment_id;
                document.getElementById('appointmentNumber').value = data.appointment_number;
                document.getElementById('patientName').value = data.patient_name;
                document.getElementById('doctorName').value = data.doctor_name;
                document.getElementById('phoneNumber').value = data.phone;
                document.getElementById('appointmentDate').value = data.appointment_date;
                document.getElementById('appointmentTime').value = data.appointment_time;
                document.getElementById('status').value = data.status;
            } else {
                alert('Error loading appointment data: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load appointment data');
        });
}

function viewDetails(id) {
    document.getElementById('detailsModal').style.display = 'block';
    
    fetch('api/appointment_handler.php?action=get&id=' + id)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const data = result.data;
                const detailsHTML = `
                    <div class="detail-item">
                        <strong><i class="fas fa-hashtag"></i> Appointment Number:</strong>
                        <span>${data.appointment_number}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-user-injured"></i> Patient Name:</strong>
                        <span>${data.patient_name}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-user-doctor"></i> Doctor Name:</strong>
                        <span>${data.doctor_name}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-phone"></i> Phone Number:</strong>
                        <span>${data.phone}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-calendar"></i> Date:</strong>
                        <span>${formatDate(data.appointment_date)}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-clock"></i> Time:</strong>
                        <span>${formatTime(data.appointment_time)}</span>
                    </div>
                    <div class="detail-item">
                        <strong><i class="fas fa-info-circle"></i> Status:</strong>
                        <span class="status-badge status-${data.status.toLowerCase()}">${data.status}</span>
                    </div>
                `;
                document.getElementById('detailsContent').innerHTML = detailsHTML;
            } else {
                alert('Error loading appointment details: ' + result.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Failed to load appointment details');
        });
}

function deleteAppointment(id) {
    currentAppointmentId = id;
    document.getElementById('deleteModal').style.display = 'block';
}

function confirmDelete() {
    if (!currentAppointmentId) return;
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('id', currentAppointmentId);
    
    fetch('api/appointment_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(result => {
        if (result.success) {
            alert(result.message);
            closeDeleteModal();
            const card = document.querySelector(`.appointment-card[data-id="${currentAppointmentId}"]`);
            if (card) {
                card.remove();
            }
            setTimeout(() => location.reload(), 500);
        } else {
            alert('Error: ' + result.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Failed to delete appointment');
    });
}

function closeModal() {
    document.getElementById('appointmentModal').style.display = 'none';
}

function closeDetailsModal() {
    document.getElementById('detailsModal').style.display = 'none';
}

function closeDeleteModal() {
    document.getElementById('deleteModal').style.display = 'none';
}

window.onclick = function(event) {
    const appointmentModal = document.getElementById('appointmentModal');
    const detailsModal = document.getElementById('detailsModal');
    const deleteModal = document.getElementById('deleteModal');
    
    if (event.target == appointmentModal) {
        closeModal();
    } else if (event.target == detailsModal) {
        closeDetailsModal();
    } else if (event.target == deleteModal) {
        closeDeleteModal();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const appointmentForm = document.getElementById('appointmentForm');
    
    if (appointmentForm) {
        appointmentForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            const appointmentId = document.getElementById('appointmentId').value;

            if (appointmentId) {
                formData.append('action', 'edit');
            } else {
                formData.append('action', 'add');
            }

            fetch('api/appointment_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    closeModal();
                    location.reload();
                } else {
                    alert('Error: ' + result.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Failed to save appointment');
            });
        });
    }
});

function formatDate(dateString) {
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatTime(timeString) {
    const time = new Date('1970-01-01T' + timeString);
    return time.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

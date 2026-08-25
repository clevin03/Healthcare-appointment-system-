function loadPatients() {
    fetch('api/patient_handler.php?action=get_all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populatePatientTable(data.data);
            } else {
                console.error('Error loading patients:', data.message);
                document.getElementById('patientTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center;">Error loading patients</td></tr>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('patientTableBody').innerHTML = '<tr><td colspan="7" style="text-align:center;">Failed to load data</td></tr>';
        });
}

function populatePatientTable(patients) {
    const tbody = document.getElementById('patientTableBody');
    tbody.innerHTML = '';

    if (patients.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No patients found</td></tr>';
        return;
    }

    patients.forEach(patient => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${patient.patient_id}</td>
            <td>${patient.patient_name}</td>
            <td>${patient.phone}</td>
            <td>${patient.email}</td>
            <td>${patient.date_of_birth}</td>
            <td>
                <button class="btn-icon btn-view" onclick="viewPatient(${patient.patient_id})">
                    <i class="fa-solid fa-eye"></i>
                </button>
                <button class="btn-icon btn-edit" onclick="editPatient(${patient.patient_id})" title="Edit patient">
                    <i class="fa-solid fa-pen"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function viewPatient(patientId) {
    fetch(`api/patient_handler.php?action=get&id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                displayPatientDetails(data.data);
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error fetching patient details:', error);
            alert('Failed to load patient details');
        });
}

function displayPatientDetails(patient) {
    const modal = document.getElementById('patientDetailsModal');
    const detailsBody = document.getElementById('patientDetailsBody');
    
    detailsBody.innerHTML = `
        <div class="detail-item">
            <strong><i class="fa-solid fa-id-card"></i> Patient ID</strong>
            <span>${patient.patient_id || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <strong><i class="fa-solid fa-user"></i> Full Name</strong>
            <span>${patient.patient_name || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <strong><i class="fa-solid fa-phone"></i> Phone Number</strong>
            <span>${patient.phone || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <strong><i class="fa-solid fa-envelope"></i> Email Address</strong>
            <span>${patient.email || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <strong><i class="fa-solid fa-calendar"></i> Date of Birth</strong>
            <span>${patient.date_of_birth || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <strong><i class="fa-solid fa-venus-mars"></i> Gender</strong>
            <span>${patient.gender || 'N/A'}</span>
        </div>
        <div class="detail-item">
            <strong><i class="fa-solid fa-location-dot"></i> Address</strong>
            <span>${patient.address || 'N/A'}</span>
        </div>
        <!--<div class="detail-item">
            <strong><i class="fa-solid fa-notes-medical"></i> Medical History</strong>
            <span>${patient.medical_history || 'No medical history recorded'}</span>
        </div>-->
        <div class="detail-item">
            <strong><i class="fa-solid fa-clock"></i> Registration Date</strong>
            <span>${patient.created_at || 'N/A'}</span>
        </div>
    `;
    
    modal.style.display = 'block';
}

function closePatientModal() {
    const modal = document.getElementById('patientDetailsModal');
    modal.style.display = 'none';
}

function editPatient(patientId) {
    fetch(`api/patient_handler.php?action=get&id=${patientId}`)
        .then(response => response.json())
        .then(data => {
            if (!data.success) {
                throw new Error(data.message);
            }

            const patient = data.data;
            document.getElementById('editPatientId').value = patient.patient_id;
            document.getElementById('editFirstName').value = patient.first_name || '';
            document.getElementById('editLastName').value = patient.last_name || '';
            document.getElementById('editPhone').value = patient.phone || '';
            document.getElementById('editGender').value = patient.gender || '';
            document.getElementById('editDateOfBirth').value = patient.date_of_birth || '';
            document.getElementById('editAddress').value = patient.address || '';
            document.getElementById('patientEditModal').style.display = 'block';
        })
        .catch(error => {
            console.error('Error fetching patient for editing:', error);
            alert(error.message || 'Failed to load patient data');
        });
}

function closePatientEditModal() {
    document.getElementById('patientEditModal').style.display = 'none';
}

window.onclick = function(event) {
    const modal = document.getElementById('patientDetailsModal');
    if (event.target === modal) {
        closePatientModal();
    }

    const editModal = document.getElementById('patientEditModal');
    if (event.target === editModal) {
        closePatientEditModal();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    loadPatients();

    document.getElementById('patientEditForm').addEventListener('submit', function(event) {
        event.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'edit');

        fetch('api/patient_handler.php', {
            method: 'POST',
            body: formData
        })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message);
                }

                closePatientEditModal();
                loadPatients();
                alert(data.message);
            })
            .catch(error => {
                console.error('Error updating patient:', error);
                alert(error.message || 'Failed to update patient');
            });
    });
});

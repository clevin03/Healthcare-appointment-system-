let departments = [];

document.addEventListener('DOMContentLoaded', function() {
    loadDepartments();
    loadDoctors();
});

function loadDepartments() {
    fetch('api/doctor_handler.php?action=get_departments')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                departments = data.data;
                populateDepartmentDropdown();
            } else {
                console.error('Error loading departments:', data.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
        });
}

function populateDepartmentDropdown() {
    const select = document.getElementById('department');
    select.innerHTML = '<option value="">Select Department</option>';
    
    departments.forEach(dept => {
        const option = document.createElement('option');
        option.value = dept.department_id;
        option.textContent = dept.department_name;
        select.appendChild(option);
    });
}

function loadDoctors() {
    fetch('api/doctor_handler.php?action=get_all')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                populateDoctorTable(data.data);
            } else {
                console.error('Error loading doctors:', data.message);
                document.getElementById('doctorTableBody').innerHTML = 
                    '<tr><td colspan="7" style="text-align:center;">Error loading doctors</td></tr>';
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            document.getElementById('doctorTableBody').innerHTML = 
                '<tr><td colspan="7" style="text-align:center;">Failed to load data</td></tr>';
        });
}

function populateDoctorTable(doctors) {
    const tbody = document.getElementById('doctorTableBody');
    tbody.innerHTML = '';

    if (doctors.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" style="text-align:center;">No doctors found</td></tr>';
        return;
    }

    doctors.forEach(doctor => {
        const row = document.createElement('tr');
        const statusClass = doctor.status === 'ACTIVE' ? 'status-active' : 'status-inactive';
        
        row.innerHTML = `
            <td>${doctor.doctor_id}</td>
            <td>${doctor.doctor_name}</td>
            <td>${doctor.email || '-'}</td>
            <td>${doctor.phone || '-'}</td>
            <td>${doctor.department_name || '-'}</td>
            <td><span class="status-badge ${statusClass}">${doctor.status}</span></td>
            <td>
                <button class="btn-icon btn-edit" onclick="editDoctor(${doctor.doctor_id})" title="Edit">
                    <i class="fa-solid fa-pen-to-square"></i>
                </button>
                <button class="btn-icon btn-delete" onclick="deleteDoctor(${doctor.doctor_id})" title="Delete">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });
}

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add New Doctor';
    document.getElementById('doctorForm').reset();
    document.getElementById('doctorId').value = '';
    document.getElementById('doctorModal').style.display = 'block';
}

function editDoctor(doctorId) {
    fetch(`api/doctor_handler.php?action=get&id=${doctorId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const doctor = data.data;
                document.getElementById('modalTitle').textContent = 'Edit Doctor';
                document.getElementById('doctorId').value = doctor.doctor_id;
                document.getElementById('doctorName').value = doctor.doctor_name;
                document.getElementById('email').value = doctor.email || '';
                document.getElementById('phone').value = doctor.phone || '';
                document.getElementById('department').value = doctor.department_id || '';
                document.getElementById('status').value = doctor.status;
                document.getElementById('doctorModal').style.display = 'block';
            } else {
                alert('Error loading doctor: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Fetch error:', error);
            alert('Failed to load doctor data');
        });
}

function deleteDoctor(doctorId) {
    if (!confirm('Are you sure you want to delete this doctor?')) {
        return;
    }
    
    const formData = new FormData();
    formData.append('action', 'delete');
    formData.append('doctor_id', doctorId);
    
    fetch('api/doctor_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            loadDoctors();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Failed to delete doctor');
    });
}

function closeModal() {
    document.getElementById('doctorModal').style.display = 'none';
    document.getElementById('doctorForm').reset();
}

window.onclick = function(event) {
    const modal = document.getElementById('doctorModal');
    if (event.target === modal) {
        closeModal();
    }
}

document.getElementById('doctorForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const doctorId = document.getElementById('doctorId').value;
    
    if (doctorId) {
        formData.append('action', 'edit');
    } else {
        formData.append('action', 'add');
    }
    
    fetch('api/doctor_handler.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            closeModal();
            loadDoctors();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Fetch error:', error);
        alert('Failed to save doctor');
    });
});

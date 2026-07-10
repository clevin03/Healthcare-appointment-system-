document.addEventListener('DOMContentLoaded', function() {
    const sessionTableBody = document.getElementById('sessionTableBody');
    const addSessionButton = document.getElementById('addSessionBtn');
    const sessionModal = document.getElementById('sessionModal');
    const closeBtn = document.querySelector('.close'); // Assumes a generic close button
    const cancelBtn = document.getElementById('cancelBtn'); // Assumes a cancel button in the modal
    let doctors = []; // To store the list of doctors
    const doctorSelect = document.getElementById('doctorName');
    const hiddenDoctorIdInput = document.getElementById('doctorId');

    if (addSessionButton) {
        addSessionButton.addEventListener('click', openAddModal);
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', closeModal);
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    window.addEventListener('click', function(event) {
        if (event.target == sessionModal) {
            closeModal();
        }
    });

    // When a doctor is selected from the dropdown, update the hidden doctorId input field.
    if (doctorSelect) {
        doctorSelect.addEventListener('change', function() {
            if (hiddenDoctorIdInput) {
                hiddenDoctorIdInput.value = this.value;
            }
        });
    }

    function openAddModal() {
        loadDoctors();
        if (sessionModal) {
            sessionModal.style.display = 'block';
        }
    }

    function closeModal() {
        sessionModal.style.display = 'none';
    }

   async function loadDoctors() {
    try{
        const response = await fetch('api/sessions_handler.php?action=getDoctors');
        const data = await response.json();
        if (data.success && Array.isArray(data.data)) {
            doctors = data.data; // Store doctors for later use
            populateDoctorDropdown(doctors);
        } else {
            console.error('Error loading doctors:', data.message || 'Invalid data format');
        }
    } catch (error) {
        console.error('Fetch error for doctors:', error);
    }
    }

    function populateDoctorDropdown(doctorList) {
        const select = document.getElementById('doctorName');
        if (!select) {
            console.warn('Doctor dropdown with id="doctorName" not found in the modal.');
            return;
        }

        select.innerHTML = '<option value="">Select a Doctor</option>';

        if (!Array.isArray(doctorList) || doctorList.length === 0) {
            const option = document.createElement('option');
            option.value = '';
            option.textContent = 'No doctors available';
            option.disabled = true;
            select.appendChild(option);
            return;
        }

        doctorList.forEach(doctor => {
            const option = document.createElement('option');
            option.value = doctor.doctor_id;
            option.textContent = doctor.doctor_name;
            select.appendChild(option);
        });
    }

    function createSessionTableRow(session) {
        const row = document.createElement('tr');
        const doctor = doctors.find(d => d.doctor_id == session.doctor_id);
        const doctorName = doctor ? doctor.doctor_name : `ID: ${session.doctor_id}`;

        row.innerHTML = `
            <td>${session.session_id}</td>
            <td>${doctorName}</td>
            <td>${session.session_date}</td>
            <td>${session.start_time}</td>
            <td>${session.end_time}</td>
            <td>
                <button class="btn-edit" data-session-id="${session.session_id}">
                    <i class="fas fa-edit"></i> Edit
                </button>
                <button class="btn-delete" data-session-id="${session.session_id}">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </td>
        `;
        return row;
    }

    // Initial fetch of data when the page loads
    loadDoctors();
    // Wire form submission to the API
    const sessionForm = document.getElementById('sessionForm');
    if (sessionForm) {
        sessionForm.addEventListener('submit', handleFormSubmit);
    }

});

    async function handleFormSubmit(e) {
        e.preventDefault();

        const sessionId = document.getElementById('sessionId') ? document.getElementById('sessionId').value : '';
        const doctorId = document.getElementById('doctorId') ? document.getElementById('doctorId').value : document.getElementById('doctorName').value;
        const sessionDate = document.getElementById('sessionDate') ? document.getElementById('sessionDate').value : '';
        const startTime = document.getElementById('startTime') ? document.getElementById('startTime').value : '';
        const endTime = document.getElementById('endTime') ? document.getElementById('endTime').value : '';
        const maxPatients = document.getElementById('maxPatients') ? document.getElementById('maxPatients').value : '';
        const createdBy = document.getElementById('createdBy') ? document.getElementById('createdBy').value : '';
        const statusEl = document.querySelector('input[name="status"]:checked');
        const status = statusEl ? statusEl.value : '';

        const formData = new FormData();
        // determine action: create vs edit
        if (sessionId && sessionId.trim() !== '') {
            formData.append('action', 'editSession');
            formData.append('session_id', sessionId);
        } else {
            formData.append('action', 'createSession');
        }

        formData.append('doctor_id', doctorId);
        formData.append('session_date', sessionDate);
        formData.append('start_time', startTime);
        formData.append('end_time', endTime);
        formData.append('max_patients', maxPatients);
        formData.append('created_by', createdBy);
        formData.append('status', status);

        try {
            const resp = await fetch('api/sessions_handler.php', {
                method: 'POST',
                body: formData
            });

            const text = await resp.text();
            let data = {};
            try {
                data = text ? JSON.parse(text) : {};
            } catch (parseError) {
                console.error('Invalid JSON response:', text);
                alert('Server returned an invalid response. Please check the PHP handler.');
                return;
            }

            if (data.success) {
                // close modal and reload to show updated sessions
                const sessionModal = document.getElementById('sessionModal');
                if (sessionModal) sessionModal.style.display = 'none';
                // simple approach: reload page to refresh session list
                location.reload();
            } else {
                alert('Error: ' + (data.message || 'Unknown error'));
            }
        } catch (err) {
            console.error('Submission error', err);
            alert('Network error while submitting the form');
        }
    }
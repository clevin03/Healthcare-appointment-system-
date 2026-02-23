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
            </td>
        `;
        tbody.appendChild(row);
    });
}

function viewPatient(patientId) {
    alert('View patient ' + patientId);
}

document.addEventListener('DOMContentLoaded', function() {
    loadPatients();
});

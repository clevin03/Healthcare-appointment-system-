document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('medicalReportModal');

    // Handle closing the modal using event delegation
    modal.addEventListener('click', function(event) {
        // Close if the background overlay or an element with the .close-btn class is clicked
        if (event.target === modal || event.target.closest('.close-btn')) {
            modal.style.display = 'none';
        }
    });

    const medicalReportForms = document.querySelectorAll('.medical-report-form');

    medicalReportForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const appointmentId = this.querySelector('input[name="appointment_id"]').value;
            const patientId = this.querySelector('input[name="patient_id"]').value;
            openMedicalReportModal(appointmentId, patientId, false);
        });
    });

    const completionForms = document.querySelectorAll('.completion-form');

    completionForms.forEach(form => {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const appointmentId = this.querySelector('input[name="appointment_id"]').value;
            const row = this.closest('tr');
            const patientId = row.querySelector('input[name="patient_id"]').value;

            if (row.dataset.medicalReportSaved !== '1') {
                openMedicalReportModal(appointmentId, patientId, true);
                return;
            }

            const formData = new FormData(this);
            formData.append('action', 'completeAppointment');

            try {
                const response = await fetch('api/medical_record_handler.php', {
                    method: 'POST',
                    body: formData
                });
                const result = await response.json();

                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Could not complete the appointment.');
                }

                row.remove();
            } catch (error) {
                console.error('There was a problem completing the appointment:', error);
                alert(error.message);
            }
        });
    });
});

function openMedicalReportModal(appointmentId, patientId, completeAfterSave) {
    const doctorId = document.getElementById('sessionDoctorId').value;
    const medicalReportModal = document.getElementById('medicalReportModal');
    const medicalReportForm = document.getElementById('medicalReportForm');
    const urlParams = new URLSearchParams(window.location.search);
    const sessionId = urlParams.get('session_id');

    medicalReportForm.innerHTML = `
        <div class="modal-body">
            <input type="hidden" name="appointment_id" id="modalAppointmentId" value="${appointmentId}">
            <input type="hidden" name="patient_id" id="patient_id" value="${patientId}">
            <input type="hidden" name="doctor_id" id="doctor_id" value="${doctorId}">
            <input type="hidden" name="session_id" value="${sessionId || ''}">
            <input type="hidden" name="complete_after_save" id="completeAfterSave" value="${completeAfterSave ? '1' : '0'}">
            <div class="form-group">
                <label for="diagnosis">Diagnosis</label>
                <textarea id="diagnosis" name="diagnosis" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="prescription">Prescription</label>
                <textarea id="prescription" name="prescription" class="form-control" rows="4" required></textarea>
            </div>
            <div class="form-group">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
            </div>
        </div>
        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" onclick="submitReport(event)">Save Record</button>
        </div>
    `;

    medicalReportModal.style.display = 'block';
}

async function submitReport(event){
    if (event) {
        event.preventDefault();
    }

    const appointmentIdInput = document.getElementById('modalAppointmentId');
    const patientIdInput = document.getElementById('patient_id');
    const doctorIdInput = document.getElementById('doctor_id');
    const diagnosisInput = document.getElementById('diagnosis');
    const prescriptionInput = document.getElementById('prescription');
    const notesInput = document.getElementById('notes');
    const completeAfterSaveInput = document.getElementById('completeAfterSave');

    const appointmentId = appointmentIdInput.value;
    const patientId = patientIdInput.value;
    const doctorId = doctorIdInput.value;
    const diagnosis = diagnosisInput.value;
    const prescription = prescriptionInput.value;
    const notes = notesInput.value;
    const completeAfterSave = completeAfterSaveInput.value;

    if (!diagnosis.trim() || !prescription.trim()) {
        alert('Please fill in the diagnosis and prescription fields before saving.');
        return;
    }

    try{
        const formData = new FormData();
        formData.append('action', 'saveRecord');
        formData.append('patient_id', patientId);
        formData.append('patient_id', patientId);
        formData.append('appointment_id', appointmentId);
        formData.append('doctor_id', doctorId);
        formData.append('diagnosis', diagnosis);
        formData.append('prescription', prescription);
        formData.append('notes', notes);
        formData.append('complete_after_save', completeAfterSave);

        const response = await fetch('api/medical_record_handler.php', {
            method: 'POST',
            body: formData
        });
        
        if(!response.ok){
            throw new Error(`Network response was not ok, status: ${response.status}`);
        }
        const result = await response.json();
        if(result.success){
            alert('Record added successfully!');
            document.getElementById('medicalReportModal').style.display = 'none';
            const row = document.querySelector(`tr[data-appointment-id="${appointmentId}"]`);
            if (completeAfterSave === '1') {
                row.remove();
            } else if (row) {
                row.dataset.medicalReportSaved = '1';
            }
        } else {
            alert('Failed to add record.');
        } 
    
    } catch(error) {
        console.error('There was a problem adding records' ,error);
        alert('Failed to added records');
    }
}
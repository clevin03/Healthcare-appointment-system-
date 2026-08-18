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
            const doctorId = document.getElementById('sessionDoctorId').value;

            const medicalReportModal = document.getElementById('medicalReportModal');
            const medicalReportForm = document.getElementById('medicalReportForm');

            // Get session_id from the URL to pass to the save_medical_report.php script
            const urlParams = new URLSearchParams(window.location.search);
            const sessionId = urlParams.get('session_id');

            // Clear and rebuild the modal form content
            medicalReportForm.innerHTML = `
                <div class="modal-body">
                    <input type="hidden" name="appointment_id" id="modalAppointmentId" value="${appointmentId}">
                    <input type="hidden" name="patient_id" value="${patientId}">
                    <input type="hidden" name="doctor_id" value="${doctorId}">
                    <input type="hidden" name="session_id" value="${sessionId || ''}">
                    <div class="form-group">
                        <label for="diagnosis">Diagnosis</label>
                        <textarea id="diagnosis" name="diagnosis" class="form-control" rows="4" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="prescription">Prescription</label>
                        <textarea id="prescription" name="prescription" class="form-control" rows="4"></textarea>
                    </div>
                    <div class="form-group">
                        <label for="notes">Notes</label>
                        <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <!--<button type="button" class="btn btn-secondary ">Cancel</button>-->
                    <button type="submit" class="btn btn-primary">Save Record</button>
                </div>
            `;

            medicalReportModal.style.display = 'block';
        });
    });
});
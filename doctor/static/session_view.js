document.addEventListener('DOMContentLoaded', function() {
        const modal = document.getElementById('medicalReportModal');
        const openModalButtons = document.querySelectorAll('.open-report-modal');
        const closeModalButtons = document.querySelectorAll('.close-btn');
        const modalAppointmentIdInput = document.getElementById('modalAppointmentId');

        openModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                const appointmentId = this.getAttribute('data-appointment-id');
                if (modalAppointmentIdInput) {
                    modalAppointmentIdInput.value = appointmentId;
                }
                modal.style.display = 'block';
            });
        });

        closeModalButtons.forEach(button => {
            button.addEventListener('click', function() {
                modal.style.display = 'none';
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        });
    });
document.addEventListener('DOMContentLoaded', function() {
    const sessionForms = document.querySelectorAll('.session-book-form');

    sessionForms.forEach(form => {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const sessionIdInput = event.target.querySelector('input[name="session_id"]');
            const doctorIdInput = event.target.querySelector('input[name="doctor_id"]');
            if (!sessionIdInput) {
                console.error('Session ID input not found in the form.');
                alert('An error occurred. Could not find session information.');
                return;
            }
            const sessionId = sessionIdInput.value;
            const doctorId = doctorIdInput.value;

            if (!sessionId) {
                alert('Please provide a session ID.');
                return;
            }
            try {
                const formData = new FormData();
                formData.append('action', 'sessionDetails');
                formData.append('session_id', sessionId);
                formData.append('doctor_id', doctorId);
                const response = await fetch('api/session_handler.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`Network response was not ok, status: ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    console.log('Session Details:', result.data); //for debugging
                    console.log('Doctor ID:', doctorId); //for debugging
                    displaySessionDetails(result.data);
                } else {
                    alert(`Error: ${result.message}`);
                }
            } catch (error) {
                console.error('There was a problem with the fetch operation:', error);
                alert('Failed to fetch session details. Please check the console for more information.');
            }
        });
    });
});
/**
 * Displays the fetched session details on the page.
 * This function assumes you have a modal with the ID 'sessionModal' and a body with ID 'modalBody' in your HTML.
 * @param {object} details The session details object from the API.
 */
function displaySessionDetails(details) {
    const displayContainer = document.getElementById('modalBody');
    const sessionModal = document.getElementById('sessionModal');
    if (sessionModal) {
        sessionModal.style.display = 'block'; // Show the modal
    }
    if (!displayContainer) {
        console.warn("Element with id 'modalBody' not found. Displaying details in an alert.");
        alert(`Session Details:\n\nDoctor: ${details.doctor_name}\nDate: ${details.session_day}\nTime: ${details.start_time} - ${details.end_time}\nPatients: ${details.current_count} / ${details.max_patients}`);
        return;
    }
    
    const currentCount = Number(details.current_count) || 0;
    const nextAppointmentNumber = currentCount + 1;

    displayContainer.innerHTML = `
        <h2>Session Details</h2>
        <p><strong>Doctor:</strong> ${details.doctor_name}</p>
        <p><strong>Date:</strong> ${details.session_day}</p>
        <p><strong>Time:</strong> ${details.start_time} - ${details.end_time}</p>
        <p><strong>Current Bookings:</strong> ${currentCount} / ${details.max_patients}</p>
        <p><strong>Your Appointment Number:</strong> ${nextAppointmentNumber}</p>
        <p><strong>Doctor ID:</strong> ${details.doctor_id}</p>
        <input type="hidden" id="sessionId" value="${details.session_id}">
        <input type="hidden" id="appointmentNumber" value="${nextAppointmentNumber}">
        <input type="hidden" id="doctorId" value="${details.doctor_id}">
        <button onclick="closeModal()">Close</button>
        <button onclick="bookAppointment(event)">Book Appointment</button>
    `;
}

function closeModal() {
    document.getElementById('sessionModal').style.display = 'none';
}

async function bookAppointment(event) {
    if (event && typeof event.preventDefault === 'function') {
        event.preventDefault();
    }
    const sessionIdInput = document.getElementById('sessionId');
    const sessionId = sessionIdInput ? sessionIdInput.value : '';
    const appointmentNumberInput = document.getElementById('appointmentNumber');
    const appointmentNumber = appointmentNumberInput ? appointmentNumberInput.value : '';
    const doctorIdInput = document.getElementById('doctorId');
    const doctorId = doctorIdInput ? doctorIdInput.value : '';

    if (!sessionId) {
        alert('Unable to book appointment: session ID is missing.');
        return;
    }

    try{
        const formData = new FormData();
        formData.append('action', 'bookAppointment');
        formData.append('session_id', sessionId);
        formData.append('appointment_number', appointmentNumber);
        formData.append('doctor_id', doctorId);

        const response = await fetch('api/booking_handler.php', {
            method: 'POST',
            body: formData
        });

        if(!response.ok){
            throw new Error(`Network response was not ok, status: ${response.status}`);
        }

        const result = await response.json();
        if(result.success){
            alert('Appointment booked successfully!');
            closeModal();
        } else {
            alert('Failed to book appointment.');
        }
    }catch(error){
        console.log(doctorId);
        console.error('There was a problem with the fetch operation:', error);
        alert('Failed to book appointment.');
    }
}

//Shoul add a function to update current appointment count after booking an appointment, so that the user can see the updated count without refreshing the page.


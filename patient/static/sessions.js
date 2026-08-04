document.addEventListener('DOMContentLoaded', function() {
    const sessionForms = document.querySelectorAll('.session-book-form');

    sessionForms.forEach(form => {
        form.addEventListener('submit', async function(event) {
            event.preventDefault();

            const sessionIdInput = event.target.querySelector('input[name="session_id"]');
            if (!sessionIdInput) {
                console.error('Session ID input not found in the form.');
                alert('An error occurred. Could not find session information.');
                return;
            }
            const sessionId = sessionIdInput.value;

            if (!sessionId) {
                alert('Please provide a session ID.');
                return;
            }
            try {
                const formData = new FormData();
                formData.append('action', 'sessionDetails');
                formData.append('session_id', sessionId);

                const response = await fetch('api/session_handler.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`Network response was not ok, status: ${response.status}`);
                }

                const result = await response.json();
                if (result.success) {
                    console.log('Session Details:', result.data);
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
    displayContainer.innerHTML = `
        <h2>Session Details</h2>
        <p><strong>Doctor:</strong> ${details.doctor_name}</p>
        <p><strong>Date:</strong> ${details.session_day}</p>
        <p><strong>Time:</strong> ${details.start_time} - ${details.end_time}</p>
        <p><strong>Current Bookings:</strong> ${details.current_count} / ${details.max_patients}</p>
        <button onclick="closeModal()">Close</button>
        <button onclick="bookAppointment(${details.session_id})">Book Appointment</button>
    `;
}

function closeModal() {
    document.getElementById('sessionModal').style.display = 'none';
}

async function bookSession(sessionId) {
    
}


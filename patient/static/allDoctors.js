document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('doctor-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterDoctors);
        // Initial filter call in case of back-navigation with filled form
        filterDoctors();
    }

    document.querySelectorAll('.book-appointment-btn').forEach(button => {
        button.addEventListener('click', () => loadDoctorSessions(button.dataset.doctorId, button.dataset.doctorName));
    });

    const sessionModal = document.getElementById('sessionModal');
    if (sessionModal) {
        window.addEventListener('click', event => {
            if (event.target === sessionModal) closeModal();
        });
    }
});

function filterDoctors() {
    const searchTerm = document.getElementById('doctor-search').value.toLowerCase();
    const doctorCards = document.querySelectorAll('.doctor-card');
    const noResultsDiv = document.getElementById('client-no-results');
    let anyVisible = false;

    doctorCards.forEach(card => {
        const doctorName = card.querySelector('h3').textContent.toLowerCase();
        const isVisible = doctorName.includes(searchTerm);
        card.style.display = isVisible ? '' : 'none';
        if (isVisible) anyVisible = true;
    });

    if (noResultsDiv) {
        noResultsDiv.style.display = anyVisible ? 'none' : 'block';
    }
}

async function loadDoctorSessions(doctorId, doctorName) {
    const modal = document.getElementById('sessionModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalBody = document.getElementById('modalBody');
    if (!modal || !modalBody) return;

    modalTitle.textContent = `Available sessions - ${doctorName}`;
    modalBody.innerHTML = '<p class="sessions-message">Loading available sessions...</p>';
    modal.style.display = 'block';

    try {
        const response = await fetch(`api/session_handler.php?action=doctorSessions&doctor_id=${encodeURIComponent(doctorId)}`);
        const result = await response.json();
        if (!response.ok || !result.success) throw new Error(result.message || 'Unable to load sessions');

        if (!Array.isArray(result.data) || result.data.length === 0) {
            modalBody.innerHTML = '<p class="sessions-message">No available sessions.</p>';
            return;
        }

        modalBody.innerHTML = result.data.map(session => {
    console.log('Session times:', session.start_time, session.end_time);

    return `
        <div class="available-session">
            <div>
                <strong>${escapeHtml(formatDate(session.session_day))}</strong>
                <span>
                    ${escapeHtml(session.start_time)} -
                    ${escapeHtml(session.end_time)}
                </span>
                <small>
                    ${Number(session.max_patients) - Number(session.current_count)}
                    places remaining
                </small>
            </div>
            <button
                type="button"
                class="modal-book-btn"
                data-session-id="${Number(session.session_id)}"
                data-doctor-id="${Number(session.doctor_id)}">
                Book
            </button>
        </div>
    `;
}).join('');
        
        modalBody.querySelectorAll('.modal-book-btn').forEach(button => {
            button.addEventListener('click', () => bookSession(button.dataset.sessionId, button.dataset.doctorId));
        });
    } catch (error) {
        console.error('Unable to load doctor sessions:', error);
        modalBody.innerHTML = '<p class="sessions-message">Unable to load available sessions. Please try again.</p>';
    }
}

function formatDate(date) {
    return new Date(`${date}T00:00:00`).toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
}

function formatTime(time) {
    return new Date(`1970-01-01T${time}`).toLocaleTimeString([], { hour: 'numeric', minute: '2-digit' });
}

function escapeHtml(value) {
    return String(value).replace(/[&<>'"]/g, character => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' }[character]));
}

async function bookSession(sessionId, doctorId) {
    const formData = new FormData();
    formData.append('action', 'bookAppointment');
    formData.append('session_id', sessionId);
    formData.append('doctor_id', doctorId);
    formData.append('appointment_number', `APT-${Date.now()}-${Math.random().toString(36).slice(2, 6).toUpperCase()}`);

    try {
        const response = await fetch('api/booking_handler.php', { method: 'POST', body: formData });
        const result = await response.json();
        alert(result.message || (result.success ? 'Appointment booked successfully.' : 'Failed to book appointment.'));
        if (result.success) closeModal();
    } catch (error) {
        console.error('Unable to book appointment:', error);
        alert('Failed to book appointment. Please try again.');
    }
}

function closeModal() {
    const modal = document.getElementById('sessionModal');
    if (modal) modal.style.display = 'none';
}
document.addEventListener('DOMContentLoaded', function() {
    //const bookingSection = document.querySelector('.booking-section');
    fetchBookings();
});

async function fetchBookings() {
    try{
        const response = await fetch('api/booking_handler.php?action=get_bookings');
        if(!response.ok){
            throw new Error(`Response status: ${response.status}`);
        }
        const data = await response.json();
        if(data.success){
            renderBookings(data.data);
        }
    }catch(error){
        console.error('Error fetching bookings:', error);
    }
}

function renderBookings(bookings) {
    const grid = document.getElementById('bookingGrid');
    grid.innerHTML = '';

    if (bookings.length === 0) {
        grid.innerHTML = '<p style="text-align:center;">No bookings found</p>';
        return;
    }

    bookings.forEach(booking =>{
        const item = document.createElement('div');
        item.className = 'booking-card';
        item.innerHTML = `
            <h3>Doctor: ${booking.doctor_name}</h3>
            <p>Appointment id/: ${booking.appointment_id}</p>
            <p>Appointment number: ${booking.appointment_number}</p>
            <p>Date: ${booking.session_day}</p>
            <p>Start Time: ${booking.start_time}</p>
            <p>End Time: ${booking.end_time}</p>
            <p>Status: ${booking.status}</p>
        `;
        grid.appendChild(item);
    })
}
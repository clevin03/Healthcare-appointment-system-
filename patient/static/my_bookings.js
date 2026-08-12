document.addEventListener('DOMContentLoaded', function() {
    //const bookingSection = document.querySelector('.booking-section');
    fetchBookings();
});

async function fetchBookings() {
    try{
        const response = await fetch('api/booking_handler.php?action=getBookings');
        if(!response.ok){
            throw new Error(`Response status: ${response.status}`);
        }
        const data = await response.json();
        if(data.success){
            renderBookings(data.data);
            console.log('Bookings fetched successfully:', data.data); //To be removed
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
        console.log('No bookings found'); //To be removed
        return;
    }

    bookings.forEach(booking =>{
        const item = document.createElement('div');
        item.className = 'booking-card';
        item.innerHTML = `
            <h3>Doctor: ${booking.doctor_name}</h3>
            
            <p><strong>Appointment Number:</strong> ${booking.appointment_number}</p>
            <p><strong>Date:</strong> ${booking.session_day}</p>
            <p><strong>Start Time:</strong> ${booking.start_time}</p>
            <p><strong>End Time:</strong> ${booking.end_time}</p>
            <p><strong>Status:</strong> ${booking.status}</p>
        `;
        grid.appendChild(item);
    })
}
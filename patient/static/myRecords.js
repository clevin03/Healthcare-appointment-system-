document.addEventListener('DOMContentLoaded', function() {
    //const bookingSection = document.querySelector('.booking-section');
    fetchBookings();
});

async function fetchBookings() {
    try{
        const response = await fetch('api/record_handler.php?action=getRecords');
        if(!response.ok){
            throw new Error(`Response status: ${response.status}`);
        }
        const data = await response.json();
        if(data.success){
            renderRecords(data.data);
            console.log('Records fetched successfully:', data.data); //To be removed
        }
    }catch(error){
        console.error('Error fetching bookings:', error);
    }
}

function renderRecords(records) {
    const grid = document.getElementById('recordGrid');
    grid.innerHTML = '';

    if (records.length === 0) {
        grid.innerHTML = '<p style="text-align:center;">No Records found</p>';
        console.log('No records found'); //To be removed
        return;
    }

    records.forEach(record =>{
        const item = document.createElement('div');
        item.className = 'booking-card';
        item.innerHTML = `
            <h3>Date: ${record.date}</h3>
            
            <p><strong>Doctor Name:</strong> ${record.doctor_name}</p>
            <p><strong>Diagnosis:</strong> ${record.diagnosis}</p>
            <p><strong>Prescription:</strong> ${record.prescription}</p>
            <p><strong>Aditional Notes</strong> ${record.notes}</p>
        `;
        grid.appendChild(item);
    })
}
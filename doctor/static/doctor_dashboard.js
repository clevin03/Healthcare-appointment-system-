document.addEventListener('DOMContentLoaded', function() {
    //const bookingSection = document.querySelector('.booking-section');
    fetchSessions();
});


async function fetchSessions(){
    try{
        const response = await fetch('api/doctor_sessions.php?action=getAll');
        if(!response.ok){
            throw new Error(`Response status: ${response.status}`);
        }
        const data = await response.json();
        console.log('Fetched doctor sessions response:', data); //For testing
        if(data.success){
            renderSessions(data.sessions);
        } else {
            console.error('Doctor sessions API error:', data.message || 'Unknown error'); //For Testing
            renderSessions([]);
        }
    } catch (error) {
        console.error('Error fetching sessions:', error);
    }
}

function renderSessions(sessions) {
    const tableBody = document.getElementById('upcomingSessionsTableBody');
    tableBody.innerHTML = '';
    const today = new Date();
    const todayString = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;

    if(!sessions || sessions.length === 0){
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 7;
        cell.textContent = 'No sessions found';
        cell.style.textAlign = 'center';
        row.appendChild(cell);
        tableBody.appendChild(row);
        console.log('No sessions found');
        return;
    }

    sessions.forEach(session=>{
        const row = document.createElement('tr');
        const isToday = session.session_day === todayString;
        row.innerHTML = `
            <td>${session.session_day}</td>
            <td>${session.start_time}</td>
            <td>${session.end_time}</td>
            <td>${session.max_patients}</td>
            <td>${session.current_count}</td>
            <td>${session.status}</td>
            <td>
            <form action="session_view.php" method="GET">
                <input type="hidden" name="session_id" value="${session.session_id}">
                <button type="submit" class="btn btn-primary"${isToday ? '' : ' disabled aria-disabled="true"'}>Start</button>
            </form>
            </td>
        `;
        console.log(session.session_id);
        tableBody.appendChild(row);
    })
}
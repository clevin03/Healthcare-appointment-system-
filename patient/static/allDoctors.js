document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('doctor-search');
    if (searchInput) {
        searchInput.addEventListener('input', filterDoctors);
        // Initial filter call in case of back-navigation with filled form
        filterDoctors();
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
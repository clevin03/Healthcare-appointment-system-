document.querySelectorAll('.sidebar ul li a').forEach(link => {
    link.addEventListener('click', function() {
        document.querySelectorAll('.sidebar ul li a').forEach(a => a.classList.remove('active'));
        this.classList.add('active');
    });
});

document.addEventListener('DOMContentLoaded', function() {
    const mainContent = document.querySelector('.main-content');
    if (mainContent) {
        mainContent.scrollTop = 0;
    }
});

(function () {
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    if (!finePointer) {
        return;
    }

    const dot = document.querySelector('.cursor-dot');
    const ring = document.querySelector('.cursor-ring');

    if (!dot || !ring) {
        return;
    }

    let mouseX = window.innerWidth / 2;
    let mouseY = window.innerHeight / 2;
    let ringX = mouseX;
    let ringY = mouseY;

    const showCursor = () => {
        dot.classList.add('cursor-visible');
        ring.classList.add('cursor-visible');
    };

    const hideCursor = () => {
        dot.classList.remove('cursor-visible');
        ring.classList.remove('cursor-visible');
    };

    document.addEventListener('mousemove', (event) => {
        mouseX = event.clientX;
        mouseY = event.clientY;
        showCursor();
        dot.style.transform = `translate3d(${mouseX - 4}px, ${mouseY - 4}px, 0)`;
    });

    document.addEventListener('mouseleave', hideCursor);
    document.addEventListener('mouseenter', showCursor);

    document.addEventListener('mouseover', (event) => {
        if (event.target.closest('a, button, input, select, textarea, label, .submit-btn, .btn-submit, .toggle-password')) {
            ring.classList.add('cursor-hover');
        }
    });

    document.addEventListener('mouseout', (event) => {
        if (event.target.closest('a, button, input, select, textarea, label, .submit-btn, .btn-submit, .toggle-password')) {
            ring.classList.remove('cursor-hover');
        }
    });

    const animateRing = () => {
        ringX += (mouseX - ringX) * 0.16;
        ringY += (mouseY - ringY) * 0.16;
        ring.style.transform = `translate3d(${ringX - 15}px, ${ringY - 15}px, 0)`;
        requestAnimationFrame(animateRing);
    };

    animateRing();
})();

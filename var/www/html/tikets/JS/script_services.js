// Service cards interaction
document.querySelectorAll('.service-card').forEach(card => {
    card.addEventListener('click', function(e) {
        // Solo redirigir si el click no fue en un enlace interno
        if (!e.target.closest('a') && !e.target.closest('button')) {
            const link = this.querySelector('.service-link');
            if (link) {
                window.location.href = link.href;
            }
        }
    });
});

// Smooth scroll for hero CTA
document.querySelector('.hero-cta').addEventListener('click', function(e) {
    if (this.getAttribute('href').startsWith('#')) {
        e.preventDefault();
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            targetElement.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    }
});

// Current year for footer
document.getElementById('current-year').textContent = new Date().getFullYear();
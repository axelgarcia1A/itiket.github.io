// Demo buttons functionality
document.querySelectorAll('.demo-button').forEach(button => {
    button.addEventListener('click', function() {
        const featureTitle = this.closest('.feature-card').querySelector('h3').textContent;
        alert(`Iniciando demostración de: ${featureTitle}`);
        // Aquí iría la lógica para mostrar la demostración real
    });
});

// Smooth scroll for CTA buttons
document.querySelectorAll('.cta-button').forEach(button => {
    button.addEventListener('click', function(e) {
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
});
// script_about.js
// Animación de contadores
function animateCounters() {
    const counters = document.querySelectorAll('.stat-number');
    const speed = 200;
    
    counters.forEach(counter => {
        const target = +counter.getAttribute('data-count');
        const count = +counter.innerText;
        const increment = target / speed;
        
        if (count < target) {
            counter.innerText = Math.ceil(count + increment);
            setTimeout(animateCounters, 1);
        } else {
            counter.innerText = target;
        }
    });
}

// Iniciar animación cuando la sección es visible
const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            animateCounters();
            observer.unobserve(entry.target);
        }
    });
}, { threshold: 0.5 });

const heroSection = document.querySelector('.about-hero');
if (heroSection) {
    observer.observe(heroSection);
}

// Efecto hover para tarjetas
document.querySelectorAll('.mission-card, .team-member, .timeline-content').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = '';
        this.style.boxShadow = '';
    });
});

// Slider de testimonios simple
let currentTestimonial = 0;
const testimonials = document.querySelectorAll('.testimonial-card');
const testimonialInterval = 5000; // 5 segundos

function showNextTestimonial() {
    testimonials.forEach(testimonial => {
        testimonial.style.display = 'none';
    });
    
    currentTestimonial = (currentTestimonial + 1) % testimonials.length;
    testimonials[currentTestimonial].style.display = 'block';
    
    setTimeout(showNextTestimonial, testimonialInterval);
}

// Iniciar slider si hay múltiples testimonios
if (testimonials.length > 1) {
    testimonials.forEach((testimonial, index) => {
        if (index !== 0) {
            testimonial.style.display = 'none';
        }
    });
    
    setTimeout(showNextTestimonial, testimonialInterval);
}
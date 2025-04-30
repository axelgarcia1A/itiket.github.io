// script_careers.js
// Acordeón de posiciones
document.querySelectorAll('.accordion-header').forEach(header => {
    header.addEventListener('click', function() {
        const item = this.parentElement;
        const isActive = item.classList.contains('active');
        
        // Cerrar todos los acordeones primero
        document.querySelectorAll('.accordion-item').forEach(accordion => {
            accordion.classList.remove('active');
        });
        
        // Abrir el actual si no estaba activo
        if (!isActive) {
            item.classList.add('active');
        }
    });
});

// Manejo del formulario
const applicationForm = document.querySelector('.application-form');
if (applicationForm) {
    applicationForm.addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validación básica
        const requiredFields = this.querySelectorAll('[required]');
        let isValid = true;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.style.borderColor = 'red';
                isValid = false;
            } else {
                field.style.borderColor = '';
            }
        });
        
        if (!isValid) {
            alert('Por favor completa todos los campos requeridos');
            return;
        }
        
        // Simular envío
        console.log('Formulario enviado:', {
            nombre: this.querySelector('#full-name').value,
            email: this.querySelector('#email').value,
            posición: this.querySelector('#position').value
        });
        
        // Mostrar confirmación
        alert('¡Gracias por tu aplicación! Hemos recibido tu información y nos pondremos en contacto contigo pronto.');
        this.reset();
        
        // Scroll al inicio del formulario
        this.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
}

// Resaltar posición seleccionada en el formulario
const positionSelect = document.getElementById('position');
if (positionSelect) {
    positionSelect.addEventListener('change', function() {
        if (this.value) {
            // Scroll a la posición seleccionada
            const positionElement = document.querySelector(`.accordion-header .position-title:contains("${this.options[this.selectedIndex].text}")`);
            if (positionElement) {
                positionElement.closest('.accordion-item').scrollIntoView({ behavior: 'smooth' });
            }
        }
    });
}

// Efecto hover para tarjetas
document.querySelectorAll('.culture-card, .benefit-item').forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-5px)';
        this.style.boxShadow = '0 10px 20px rgba(0,0,0,0.1)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = '';
        this.style.boxShadow = '';
    });
});
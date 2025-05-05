document.addEventListener('DOMContentLoaded', function() {
    // 1. Configurar fecha actual
    const currentDateElement = document.getElementById('currentDate');
    const currentDate = new Date();
    const options = { year: 'numeric', month: 'long', day: 'numeric' };
    currentDateElement.textContent = currentDate.toLocaleDateString('es-ES', options);
    
    // 2. Selección de elementos
    const acceptButton = document.getElementById('acceptButton');
    const declineButton = document.getElementById('declineButton');
    const termsContainer = document.querySelector('.terms-container');
    
    // 3. Efecto de scroll suave para secciones
    document.querySelectorAll('.terms-section h2').forEach(heading => {
        heading.style.cursor = 'pointer';
        heading.addEventListener('click', function() {
            this.parentElement.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });
    
    // 4. Animación al cargar
    setTimeout(() => {
        termsContainer.style.transform = 'translateY(0)';
        termsContainer.style.opacity = '1';
    }, 100);
    
    // 5. Manejo de aceptación de términos
    acceptButton.addEventListener('click', function() {
        // Guardar en localStorage y sessionStorage
        localStorage.setItem('termsAccepted', 'true');
        sessionStorage.setItem('termsAccepted', 'true');
        
        // Animación de confirmación
        this.innerHTML = '<span class="spinner"></span> Redirigiendo...';
        this.classList.add('processing');
        
        // Redirigir con parámetro y hash
        setTimeout(() => {
            window.location.href = './signup.html?terms=accepted#registered';
        }, 1500);
    });
    
    // 6. Manejo de rechazo
    declineButton.addEventListener('click', function() {
        // Animación de rechazo
        this.innerHTML = '✓ Rechazado';
        this.style.backgroundColor = '#ff6b6b';
        this.style.color = 'white';
        
        // Mostrar mensaje y redirigir
        setTimeout(() => {
            alert('Para utilizar iTiket, debes aceptar nuestros Términos y Condiciones.');
            window.location.href = './index.html';
        }, 1000);
    });
    
    // 7. Verificar si ya venía de intentar registrarse
    if (document.referrer.includes('signup')) {
        const warning = document.createElement('div');
        warning.className = 'api-error';
        warning.innerHTML = `
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            Debes aceptar los Términos y Condiciones para registrarte
        `;
        document.querySelector('.terms-header').after(warning);
    }
    
    // 8. Configuración inicial de animación
    termsContainer.style.transform = 'translateY(20px)';
    termsContainer.style.opacity = '0';
    termsContainer.style.transition = 'all 0.4s ease-out';
});
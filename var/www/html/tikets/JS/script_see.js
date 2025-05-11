// Funciones específicas para ver_tiket.php
function setupTicketDetailPage() {
    // Auto-expand textarea al escribir
    const textarea = document.querySelector('.response-form textarea');
    if (textarea) {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
        
        // Contador de caracteres
        const charCounter = document.createElement('div');
        charCounter.className = 'char-counter';
        charCounter.innerHTML = '<span id="response-char-count">0</span>/2000 caracteres';
        textarea.parentNode.insertBefore(charCounter, textarea.nextSibling);
        
        textarea.addEventListener('input', function() {
            const count = this.value.length;
            document.getElementById('response-char-count').textContent = count;
            
            if (count > 1800) {
                charCounter.style.color = '#e74c3c';
            } else if (count > 1500) {
                charCounter.style.color = '#f39c12';
            } else {
                charCounter.style.color = '#7f8c8d';
            }
        });
    }
    
    // Confirmar antes de cerrar con cambios no guardados
    const responseForm = document.querySelector('.response-form');
    if (responseForm) {
        let formChanged = false;
        
        responseForm.addEventListener('change', () => formChanged = true);
        responseForm.addEventListener('input', () => formChanged = true);
        
        window.addEventListener('beforeunload', (e) => {
            if (formChanged) {
                e.preventDefault();
                return e.returnValue = 'Tienes una respuesta sin enviar. ¿Seguro que quieres salir?';
            }
        });
        
        responseForm.addEventListener('submit', () => {
            formChanged = false;
            const submitBtn = responseForm.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                submitBtn.disabled = true;
            }
        });
    }
}

// Inicializar cuando el DOM esté listo
if (document.querySelector('.ticket-detail-container')) {
    setupTicketDetailPage();
}
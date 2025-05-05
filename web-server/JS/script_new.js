document.addEventListener('DOMContentLoaded', function() {
    // Contador de caracteres para la descripción
    const descripcion = document.getElementById('descripcion');
    const charCount = document.getElementById('char-count');
    
    if (descripcion && charCount) {
        descripcion.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCount.textContent = currentLength;
            
            // Cambiar color si se acerca al límite
            if (currentLength > 900) {
                charCount.style.color = '#e74c3c';
            } else if (currentLength > 700) {
                charCount.style.color = '#f39c12';
            } else {
                charCount.style.color = '#7f8c8d';
            }
        });
    }
    
    // Validación de formulario antes de enviar
    const form = document.querySelector('.ticket-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            const titulo = this.elements['titulo'].value.trim();
            const descripcion = this.elements['descripcion'].value.trim();
            
            if (titulo.length < 5) {
                alert('El título debe tener al menos 5 caracteres');
                e.preventDefault();
                return;
            }
            
            if (descripcion.length < 20) {
                alert('La descripción debe tener al menos 20 caracteres');
                e.preventDefault();
                return;
            }
            
            // Mostrar spinner de carga
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';
                submitBtn.disabled = true;
            }
        });
    }
    
    // Prevenir envío múltiple
    let formSubmitted = false;
    window.addEventListener('beforeunload', function(e) {
        if (formSubmitted) {
            return;
        }
        
        const form = document.querySelector('.ticket-form');
        if (form && form.checkValidity()) {
            e.preventDefault();
            return e.returnValue = '¿Estás seguro de querer salir? Los cambios no se guardarán.';
        }
    });
    
    document.querySelectorAll('.ticket-form').forEach(form => {
        form.addEventListener('submit', function() {
            formSubmitted = true;
        });
    });
});
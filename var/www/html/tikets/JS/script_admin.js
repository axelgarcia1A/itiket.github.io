document.addEventListener('DOMContentLoaded', function() {
    // Mostrar notificación si hay parámetros en la URL
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        showNotification('Operación realizada con éxito', 'success');
    } else if (urlParams.has('error')) {
        showNotification('Error al realizar la operación', 'error');
    }
});

// Función para mostrar/ocultar detalles del tiket
function toggleDetails(ticketId) {
    const detailsElement = document.getElementById(`details-${ticketId}`);
    if (detailsElement.style.display === 'block') {
        detailsElement.style.display = 'none';
    } else {
        // Cerrar otros detalles abiertos
        document.querySelectorAll('.ticket-details').forEach(details => {
            details.style.display = 'none';
        });
        detailsElement.style.display = 'block';
    }
}

// Confirmar eliminación
function confirmDelete() {
    return confirm('¿Estás seguro de que deseas eliminar este tiket? Esta acción no se puede deshacer.');
}

// Mostrar notificación
function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    
    document.body.appendChild(notification);
    
    // Mostrar notificación
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateY(0)';
    }, 100);
    
    // Ocultar después de 3 segundos
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Manejar envío de formularios con feedback visual
document.querySelectorAll('form').forEach(form => {
    form.addEventListener('submit', function(e) {
        const submitBtn = this.querySelector('button[type="submit"]');
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
        }
    });
});
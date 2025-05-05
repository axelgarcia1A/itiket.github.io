document.addEventListener('DOMContentLoaded', function() {
    // Confirmación antes de acciones importantes
    const deleteLinks = document.querySelectorAll('.delete-tiket');
    deleteLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de querer eliminar este tiket?')) {
                e.preventDefault();
            }
        });
    });

    // Filtrado de tikets por estado
    const filterSelect = document.getElementById('filter-status');
    if (filterSelect) {
        filterSelect.addEventListener('change', function() {
            const status = this.value;
            const rows = document.querySelectorAll('.ticket-row');
            
            rows.forEach(row => {
                if (status === 'all' || row.classList.contains(status)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Animación al pasar el ratón sobre filas
    const ticketRows = document.querySelectorAll('.ticket-row');
    ticketRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
});
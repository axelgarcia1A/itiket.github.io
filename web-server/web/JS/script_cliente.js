        // Confirmación para cerrar sesión
        document.querySelector('a[href="logout.php"]')?.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                e.preventDefault();
            }
        });
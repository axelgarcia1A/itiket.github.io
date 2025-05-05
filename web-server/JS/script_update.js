    // =============================================
    // Footer Functionality
    // =============================================
    // Actualizar año del copyright
    const yearElement = document.getElementById('current-year');
    if (yearElement) {
        yearElement.textContent = new Date().getFullYear();
    }
    
    // Smooth scrolling para enlaces del footer
    document.querySelectorAll('.footer-links a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Update URL without reloading
                history.pushState(null, null, targetId);
            }
        });
    });
    
    // Tracking para enlaces sociales
    document.querySelectorAll('.social-link').forEach(link => {
        link.addEventListener('click', function(e) {
            const platform = this.getAttribute('aria-label');
            console.log(`Social link clicked: ${platform}`);
            // Aquí podrías añadir tracking analytics
        });
    });
    
    // =============================================
    // Newsletter Form
    // =============================================
    const newsletterForm = document.querySelector('.newsletter-form');
    if (newsletterForm) {
        newsletterForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const email = this.querySelector('input').value;
            alert(`Gracias por suscribirte con ${email}. Te mantendremos informado.`);
            this.reset();
        });
    }
    
    // =============================================
    // Update Cards Functionality
    // =============================================
    // Sistema de acordeón para las tarjetas de actualización
    document.querySelectorAll('.update-card').forEach(card => {
        const header = card.querySelector('.update-header');
        const content = card.querySelector('.update-content');
        
        if (header && content) {
            header.addEventListener('click', () => {
                card.classList.toggle('expanded');
            });
            
            // En móviles, mostrar todo siempre
            if (window.innerWidth < 768) {
                card.classList.add('expanded');
            }
        }
    });
    
    // Manejar cambios de tamaño de pantalla
    window.addEventListener('resize', () => {
        const isMobile = window.innerWidth < 768;
        document.querySelectorAll('.update-card').forEach(card => {
            if (isMobile) {
                card.classList.add('expanded');
            } else {
                card.classList.remove('expanded');
            }
        });
    });
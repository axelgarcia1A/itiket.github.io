document.addEventListener('DOMContentLoaded', function() {
    // Sistema de dark/light mode
    const themeToggle = document.getElementById('theme-toggle');
    const htmlElement = document.documentElement;
    const torch = document.querySelector('.torch');
    
    // Función para aplicar el tema
    function applyTheme(theme) {
        htmlElement.setAttribute('data-theme', theme);
        localStorage.setItem('theme', theme);
        
        // Actualizar el estado del toggle
        themeToggle.checked = theme === 'light';
        
        // Actualizar el estado visual de la antorcha
        updateTorchState(theme);
    }
    
    // Función para actualizar el estado visual de la antorcha
    function updateTorchState(theme) {
        if (theme === 'dark') {
            torch.style.filter = 'drop-shadow(0px 0px 2px rgb(255, 255, 255)) drop-shadow(0px 0px 10px rgba(255, 237, 156, 0.7)) drop-shadow(0px 0px 25px rgba(255, 227, 101, 0.4))';
        } else {
            torch.style.filter = 'none';
        }
    }
    
    // Verificar preferencia guardada o del sistema
    const savedTheme = localStorage.getItem('theme');
    const systemPrefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme) {
        applyTheme(savedTheme);
    } else if (systemPrefersDark) {
        applyTheme('dark');
        
    } else {
        applyTheme('light');
    }
    
    // Cambiar tema cuando se interactúa con el toggle
    themeToggle.addEventListener('change', function() {
        const newTheme = this.checked ? 'light' : 'dark';
        applyTheme(newTheme);
    });
    
    // Escuchar cambios en la preferencia del sistema
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
        if (!localStorage.getItem('theme')) {
            applyTheme(e.matches ? 'dark' : 'light');
        }
    });
    
    // Carrusel de testimonios
    const testimonials = [
        {
            name: "Carlos Martínez",
            company: "TechSolutions",
            text: "iTiket ha revolucionado nuestra gestión de tickets. Ahora resolvemos problemas un 40% más rápido.",
            avatar: "./web/image/avatar1.jpg"
        },
        {
            name: "Ana López",
            company: "ServiDigital",
            text: "La interfaz es intuitiva y las métricas nos ayudan a mejorar nuestro servicio constantemente.",
            avatar: "./web/image/avatar2.jpg"
        },
        {
            name: "Javier Ruiz",
            company: "InnovaSoft",
            text: "Excelente herramienta para equipos de soporte. La colaboración entre departamentos es ahora muy sencilla.",
            avatar: "./web/image/avatar3.jpg"
        }
    ];

    const testimonialSlider = document.querySelector('.testimonial-slider');
    let currentTestimonial = 0;
    
    function loadImage(url) {
        return new Promise((resolve, reject) => {
            const img = new Image();
            img.src = url;
            img.onload = () => resolve(url);
            img.onerror = () => {
                // Si falla la carga, usar un avatar por defecto
                resolve('./web/image/default-avatar.jpg');
            };
        });
    }

    async function showTestimonial(index) {
        const testimonial = testimonials[index];
        
        // Cargar imagen con manejo de errores
        const avatarUrl = await loadImage(testimonial.avatar);
        
        testimonialSlider.innerHTML = `
            <div class="testimonial active">
                <img src="${avatarUrl}" alt="${testimonial.name}" class="testimonial-avatar">
                <p class="testimonial-text">"${testimonial.text}"</p>
                <div class="testimonial-author">
                    <strong>${testimonial.name}</strong>
                    <span>${testimonial.company}</span>
                </div>
            </div>
        `;
    }
    
    // Mostrar primer testimonio
    showTestimonial(currentTestimonial);
    
    Promise.all(testimonials.map(t => loadImage(t.avatar)))
        .then(() => showTestimonial(currentTestimonial))
        .catch(() => showTestimonial(currentTestimonial));

    // Rotar testimonios cada 5 segundos
    let testimonialInterval = setInterval(() => {
        currentTestimonial = (currentTestimonial + 1) % testimonials.length;
        showTestimonial(currentTestimonial);
    }, 5000);
    
    // Pausar carrusel al interactuar
    testimonialSlider.addEventListener('mouseenter', () => {
        clearInterval(testimonialInterval);
    });
    
    testimonialSlider.addEventListener('mouseleave', () => {
        testimonialInterval = setInterval(() => {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            showTestimonial(currentTestimonial);
        }, 5000);
    });
    
    // Control del carrusel de clientes
    const carousel = document.querySelector('.carousel');
    let isPaused = false;
    
    carousel.addEventListener('mouseenter', () => {
        isPaused = true;
        document.querySelectorAll('.group').forEach(group => {
            group.style.animationPlayState = 'paused';
        });
    });
    
    carousel.addEventListener('mouseleave', () => {
        isPaused = false;
        document.querySelectorAll('.group').forEach(group => {
            group.style.animationPlayState = 'running';
        });
    });
    
    // Smooth scroll para enlaces internos
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);
            
            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
                
                // Actualizar URL sin recargar
                if (history.pushState) {
                    history.pushState(null, null, targetId);
                } else {
                    location.hash = targetId;
                }
            }
        });
    });
    
    // Efectos hover para botones CTA
    const ctaButtons = document.querySelectorAll('.cta-button');
    ctaButtons.forEach(button => {
        button.addEventListener('mouseenter', () => {
            button.style.transform = 'translateY(-3px)';
            button.style.boxShadow = '0 10px 20px rgba(0, 0, 0, 0.1)';
        });
        
        button.addEventListener('mouseleave', () => {
            button.style.transform = 'translateY(0)';
            button.style.boxShadow = 'none';
        });
    });
    
    // Optimización de imágenes - Lazy loading
    const images = document.querySelectorAll('img[data-src]');
    
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.src = img.dataset.src;
                    img.removeAttribute('data-src');
                    observer.unobserve(img);
                }
            });
        }, {
            rootMargin: '200px 0px'
        });
        
        images.forEach(img => {
            imageObserver.observe(img);
        });
    } else {
        // Fallback para navegadores que no soportan IntersectionObserver
        images.forEach(img => {
            img.src = img.dataset.src;
        });
    }
    
    // Manejo de formularios
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validación básica
            const inputs = this.querySelectorAll('input[required], textarea[required]');
            let isValid = true;
            
            inputs.forEach(input => {
                if (!input.value.trim()) {
                    input.style.borderColor = 'red';
                    isValid = false;
                } else {
                    input.style.borderColor = '';
                }
            });
            
            if (isValid) {
                // Aquí iría el código para enviar el formulario
                console.log('Formulario válido, enviando...');
                // this.submit();
                
                // Mensaje de éxito temporal
                const submitButton = this.querySelector('button[type="submit"]');
                const originalText = submitButton.textContent;
                
                submitButton.textContent = 'Enviado!';
                submitButton.disabled = true;
                
                setTimeout(() => {
                    submitButton.textContent = originalText;
                    submitButton.disabled = false;
                }, 2000);
            }
        });
    });
    
    // Mejorar accesibilidad del menú
    const menuItems = document.querySelectorAll('nav ul li a');
    menuItems.forEach((item, index) => {
        item.setAttribute('tabindex', '0');
        
        item.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                item.click();
            }
            
            // Navegación con teclado
            if (e.key === 'ArrowRight') {
                e.preventDefault();
                const nextItem = menuItems[(index + 1) % menuItems.length];
                nextItem.focus();
            }
            
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                const prevItem = menuItems[(index - 1 + menuItems.length) % menuItems.length];
                prevItem.focus();
            }
        });
    });
});
document.addEventListener('DOMContentLoaded', function() {
    // Elementos del DOM
    const forgotForm = document.getElementById('forgotForm');
    const emailInput = document.getElementById('email');
    const emailError = document.getElementById('emailError');
    const emailGroup = document.getElementById('emailGroup');
    const submitButton = document.getElementById('submitButton');
    const loginLink = document.getElementById('loginLink');
    const signupLink = document.getElementById('signupLink');
    const container = document.getElementById('forgotContainer');
    const modal = document.getElementById('confirmationModal');
    const closeModal = document.getElementById('closeModal');
    const modalButton = document.getElementById('modalButton');
    const userEmailSpan = document.getElementById('userEmail');

    // Mostrar animación al cargar
    setTimeout(() => {
        container.classList.add('show');
    }, 100);

    // Validación de email en tiempo real
    emailInput.addEventListener('input', validateEmail);

    function validateEmail() {
        const value = emailInput.value.trim();
        const isValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
        
        if (!isValid && value !== '') {
            showError(emailGroup, emailError, 'Por favor ingresa un email válido');
            return false;
        }
        
        hideError(emailGroup, emailError);
        return isValid;
    }

    function showError(group, errorElement, message) {
        group.classList.add('error');
        errorElement.textContent = message;
    }

    function hideError(group, errorElement) {
        group.classList.remove('error');
        errorElement.textContent = '';
    }

    // Envío del formulario
    forgotForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const isEmailValid = validateEmail();
        
        if (isEmailValid) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Enviando...';
            
            // Simular envío al servidor
            try {
                const response = await mockSendResetEmail(emailInput.value.trim());
                
                if (response.success) {
                    // Mostrar modal de confirmación
                    userEmailSpan.textContent = emailInput.value.trim();
                    modal.style.display = 'block';
                    
                    // Resetear formulario
                    forgotForm.reset();
                } else {
                    throw new Error(response.message);
                }
            } catch (error) {
                showError(emailGroup, emailError, error.message);
            } finally {
                submitButton.disabled = false;
                submitButton.innerHTML = 'Enviar Instrucciones';
            }
        }
    });

    // Simulación de API
    function mockSendResetEmail(email) {
        return new Promise((resolve, reject) => {
            setTimeout(() => {
                // Simular que algunos emails no están registrados
                if (email.includes('noexiste@')) {
                    reject(new Error('Este email no está registrado'));
                } else if (email.includes('error@')) {
                    reject(new Error('Error al enviar el correo. Intenta nuevamente.'));
                } else {
                    resolve({
                        success: true,
                        message: 'Correo enviado exitosamente'
                    });
                }
            }, 1500);
        });
    }

    // Cerrar modal
    closeModal.addEventListener('click', function() {
        modal.style.display = 'none';
    });

    modalButton.addEventListener('click', function() {
        modal.style.display = 'none';
        window.location.href = './login.html';
    });

    // Cerrar modal al hacer clic fuera
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });

    // Manejo de enlaces
    loginLink.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = './login.html';
    });

    signupLink.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = './signup.html';
    });
});
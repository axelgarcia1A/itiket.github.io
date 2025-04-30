document.addEventListener('DOMContentLoaded', function() {
    // 1. Selección de elementos del DOM
    const loginForm = document.getElementById('loginForm');
    const usernameInput = document.getElementById('username');
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const usernameError = document.getElementById('usernameError');
    const passwordError = document.getElementById('passwordError');
    const usernameGroup = document.getElementById('usernameGroup');
    const passwordGroup = document.getElementById('passwordGroup');
    const forgotPasswordLink = document.getElementById('forgotPassword');
    const signupLink = document.getElementById('signupLink');
    const loginContainer = document.getElementById('loginContainer');
    const loginButton = document.getElementById('loginButton');
    const rememberCheckbox = document.getElementById('remember');

    // Estado del formulario
    let isSubmitting = false;

    // 2. Toggle para mostrar/ocultar contraseña
    togglePassword.addEventListener('click', function() {
        const isPasswordVisible = passwordInput.type === 'text';
        passwordInput.type = isPasswordVisible ? 'password' : 'text';
        togglePassword.textContent = isPasswordVisible ? 'Mostrar' : 'Ocultar';
        
        // Enfocar el campo de contraseña después del toggle
        passwordInput.focus();
    });

    // 3. Validación en tiempo real
    usernameInput.addEventListener('input', validateUsername);
    passwordInput.addEventListener('input', validatePassword);

    function validateUsername() {
        const value = usernameInput.value.trim();
        
        if (value === '') {
            showError(usernameGroup, usernameError, 'Por favor ingresa tu usuario o email');
            return false;
        } 
        
        if (!isValidEmail(value) && value.length < 4) {
            showError(usernameGroup, usernameError, 'Debe ser un email válido o usuario de al menos 4 caracteres');
            return false;
        }
        
        hideError(usernameGroup, usernameError);
        return true;
    }

    function validatePassword() {
        const value = passwordInput.value;
        
        if (value.length < 6) {
            showError(passwordGroup, passwordError, 'La contraseña debe tener al menos 6 caracteres');
            return false;
        }
        
        hideError(passwordGroup, passwordError);
        return true;
    }

    // 4. Funciones auxiliares de validación
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function showError(groupElement, errorElement, message) {
        groupElement.classList.add('error');
        errorElement.textContent = message;
    }

    function hideError(groupElement, errorElement) {
        groupElement.classList.remove('error');
        errorElement.textContent = '';
    }

    // 5. Manejo del envío del formulario
    loginForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (isSubmitting) return;
        
        const isUsernameValid = validateUsername();
        const isPasswordValid = validatePassword();
        
        if (isUsernameValid && isPasswordValid) {
            isSubmitting = true;
            updateButtonState(true);
            
            // Simulación de envío a servidor (reemplazar con fetch/AJAX real)
            try {
                const response = await mockLoginRequest();
                
                if (response.success) {
                    handleSuccess();
                } else {
                    handleApiError(response.message);
                }
            } catch (error) {
                handleApiError('Error de conexión. Intenta nuevamente.');
            } finally {
                isSubmitting = false;
                updateButtonState(false);
            }
        }
    });

    // 6. Funciones para manejo de estado
    function updateButtonState(isLoading) {
        if (isLoading) {
            loginButton.disabled = true;
            loginButton.innerHTML = `
                <span class="spinner"></span> Procesando...
            `;
        } else {
            loginButton.disabled = false;
            loginButton.textContent = 'Iniciar Sesión';
        }
    }

    function handleSuccess() {
        // Animación de éxito
        loginContainer.classList.add('success-animation');
        setTimeout(() => {
            alert('¡Inicio de sesión exitoso! Redirigiendo...');
            // Aquí iría la redirección real
            // window.location.href = '/dashboard';
        }, 1000);
    }

    function handleApiError(message) {
        // Mostrar error genérico arriba del formulario
        const errorElement = document.createElement('div');
        errorElement.className = 'api-error';
        errorElement.textContent = message;
        
        const header = document.querySelector('.login-header');
        if (!document.querySelector('.api-error')) {
            header.insertAdjacentElement('afterend', errorElement);
            
            // Auto-eliminar después de 5 segundos
            setTimeout(() => {
                errorElement.remove();
            }, 5000);
        }
    }

    // 7. Simulación de API (mock)
    function mockLoginRequest() {
        return new Promise((resolve) => {
            setTimeout(() => {
                // Simulación: credenciales válidas
                if (usernameInput.value.includes('@example.com') && passwordInput.value.length >= 6) {
                    resolve({
                        success: true,
                        token: 'mock-token-123456',
                        user: {
                            id: 1,
                            name: usernameInput.value.split('@')[0]
                        }
                    });
                } else {
                    resolve({
                        success: false,
                        message: 'Credenciales incorrectas. Verifica tus datos.'
                    });
                }
            }, 1500);
        });
    }

    // 9. Recordar usuario (usando localStorage)
    if (localStorage.getItem('rememberedUsername')) {
        usernameInput.value = localStorage.getItem('rememberedUsername');
        rememberCheckbox.checked = true;
    }

    rememberCheckbox.addEventListener('change', function() {
        if (this.checked && usernameInput.value.trim()) {
            localStorage.setItem('rememberedUsername', usernameInput.value.trim());
        } else {
            localStorage.removeItem('rememberedUsername');
        }
    });
});
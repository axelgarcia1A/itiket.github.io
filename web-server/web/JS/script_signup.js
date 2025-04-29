document.addEventListener('DOMContentLoaded', function() {
    // 1. Selección de elementos del DOM
    const signupForm = document.getElementById('signupForm');
    const usernameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('confirmPassword');
    const togglePassword = document.getElementById('togglePassword');
    const toggleConfirmPassword = document.getElementById('toggleConfirmPassword');
    const termsCheckbox = document.getElementById('terms');
    const termsLink = document.getElementById('termsLink');
    const loginLink = document.getElementById('loginLink');
    const signupContainer = document.getElementById('signupContainer');
    const signupButton = document.getElementById('signupButton');

    // Elementos de error
    const usernameError = document.getElementById('usernameError');
    const emailError = document.getElementById('emailError');
    const passwordError = document.getElementById('passwordError');
    const confirmPasswordError = document.getElementById('confirmPasswordError');
    const termsError = document.getElementById('termsError');
    
    // Grupos de formulario
    const usernameGroup = document.getElementById('usernameGroup');
    const emailGroup = document.getElementById('emailGroup');
    const passwordGroup = document.getElementById('passwordGroup');
    const confirmPasswordGroup = document.getElementById('confirmPasswordGroup');

    // Estado del formulario
    let isSubmitting = false;

    // 2. Toggles para mostrar/ocultar contraseña
    togglePassword.addEventListener('click', function() {
        const isPasswordVisible = passwordInput.type === 'text';
        passwordInput.type = isPasswordVisible ? 'password' : 'text';
        togglePassword.textContent = isPasswordVisible ? 'Mostrar' : 'Ocultar';
        passwordInput.focus();
    });

    toggleConfirmPassword.addEventListener('click', function() {
        const isPasswordVisible = confirmPasswordInput.type === 'text';
        confirmPasswordInput.type = isPasswordVisible ? 'password' : 'text';
        toggleConfirmPassword.textContent = isPasswordVisible ? 'Mostrar' : 'Ocultar';
        confirmPasswordInput.focus();
    });

    // 3. Validación en tiempo real
    usernameInput.addEventListener('input', validateUsername);
    emailInput.addEventListener('input', validateEmail);
    passwordInput.addEventListener('input', validatePassword);
    confirmPasswordInput.addEventListener('input', validateConfirmPassword);

    function validateUsername() {
        const value = usernameInput.value.trim();
        
        if (value === '') {
            showError(usernameGroup, usernameError, 'Por favor ingresa un nombre de usuario');
            return false;
        }
        
        if (value.length < 4) {
            showError(usernameGroup, usernameError, 'El usuario debe tener al menos 4 caracteres');
            return false;
        }
        
        if (!/^[a-zA-Z0-9_]+$/.test(value)) {
            showError(usernameGroup, usernameError, 'Solo se permiten letras, números y guiones bajos');
            return false;
        }
        
        hideError(usernameGroup, usernameError);
        return true;
    }

    function validateEmail() {
        const value = emailInput.value.trim();
        
        if (value === '') {
            showError(emailGroup, emailError, 'Por favor ingresa tu email');
            return false;
        }
        
        if (!isValidEmail(value)) {
            showError(emailGroup, emailError, 'Por favor ingresa un email válido');
            return false;
        }
        
        hideError(emailGroup, emailError);
        return true;
    }

    function validatePassword() {
        const value = passwordInput.value;
        
        if (value.length < 8) {
            showError(passwordGroup, passwordError, 'La contraseña debe tener al menos 8 caracteres');
            return false;
        }
        
        // Verificar fortaleza de contraseña
        const strength = checkPasswordStrength(value);
        updatePasswordStrengthIndicator(strength);
        
        hideError(passwordGroup, passwordError);
        return true;
    }

    function validateConfirmPassword() {
        const password = passwordInput.value;
        const confirmPassword = confirmPasswordInput.value;
        
        if (confirmPassword === '') {
            showError(confirmPasswordGroup, confirmPasswordError, 'Por favor confirma tu contraseña');
            return false;
        }
        
        if (password !== confirmPassword) {
            showError(confirmPasswordGroup, confirmPasswordError, 'Las contraseñas no coinciden');
            return false;
        }
        
        hideError(confirmPasswordGroup, confirmPasswordError);
        return true;
    }

    function validateTerms() {
        if (!termsCheckbox.checked) {
            termsError.textContent = 'Debes aceptar los términos y condiciones';
            return false;
        }
        
        termsError.textContent = '';
        return true;
    }

    // 4. Funciones auxiliares de validación
    function isValidEmail(email) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
    }

    function checkPasswordStrength(password) {
        // Implementación básica de verificación de fortaleza
        const hasLetters = /[a-zA-Z]/.test(password);
        const hasNumbers = /[0-9]/.test(password);
        const hasSpecial = /[^a-zA-Z0-9]/.test(password);
        
        let strength = 0;
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (hasLetters && hasNumbers) strength++;
        if (hasSpecial) strength++;
        
        return strength;
    }

    function updatePasswordStrengthIndicator(strength) {
        // Puedes implementar un indicador visual de fortaleza de contraseña aquí
        // Por ejemplo, cambiar el color del borde o mostrar un mensaje
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
    signupForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        if (isSubmitting) return;
        
        const isUsernameValid = validateUsername();
        const isEmailValid = validateEmail();
        const isPasswordValid = validatePassword();
        const isConfirmPasswordValid = validateConfirmPassword();
        const isTermsValid = validateTerms();
        
        if (isUsernameValid && isEmailValid && isPasswordValid && isConfirmPasswordValid && isTermsValid) {
            isSubmitting = true;
            updateButtonState(true);
            
            // Simulación de envío a servidor (reemplazar con fetch/AJAX real)
            try {
                const response = await mockSignupRequest();
                
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
            signupButton.disabled = true;
            signupButton.innerHTML = `
                <span class="spinner"></span> Creando cuenta...
            `;
        } else {
            signupButton.disabled = false;
            signupButton.textContent = 'Registrarse';
        }
    }

    function handleSuccess() {
        // Animación de éxito
        signupContainer.classList.add('success-animation');
        setTimeout(() => {
            alert('¡Registro exitoso! Redirigiendo al login...');
            // Redirigir al login después de registro exitoso
            window.location.href = './login.html';
        }, 1000);
    }

    function handleApiError(message) {
        // Mostrar error genérico arriba del formulario
        const errorElement = document.createElement('div');
        errorElement.className = 'api-error';
        errorElement.innerHTML = `
            <svg viewBox="0 0 24 24" fill="currentColor">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
            </svg>
            ${message}
        `;
        
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
    function mockSignupRequest() {
        return new Promise((resolve) => {
            setTimeout(() => {
                // Simulación: éxito si el email no es test@example.com
                if (!emailInput.value.includes('test@example.com')) {
                    resolve({
                        success: true,
                        message: 'Registro completado con éxito'
                    });
                } else {
                    resolve({
                        success: false,
                        message: 'Este email ya está registrado'
                    });
                }
            }, 1500);
        });
    }



    // 9. Enlace de volver al login
    loginLink.addEventListener('click', function(e) {
        e.preventDefault();
        window.location.href = './login.html';
    });
});
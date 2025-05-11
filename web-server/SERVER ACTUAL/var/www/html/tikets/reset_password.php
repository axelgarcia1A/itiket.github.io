<?php
session_start();
require 'db_config.php'; // Archivo de configuración de la base de datos

// Inicializar variables
$error = '';
$success = '';
$validToken = false;
$email = '';
$password = '';
$confirmPassword = '';
$passwordError = '';
$confirmPasswordError = '';

// Verificar token
if (isset($_GET['token'])) {
    $token = $_GET['token'];
    
    $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires > NOW()");
    $stmt->execute([$token]);
    $resetRequest = $stmt->fetch();
    
    if ($resetRequest) {
        $validToken = true;
        $email = $resetRequest['email'];
    } else {
        $error = "El enlace de restablecimiento no es válido o ha expirado";
    }
}

// Procesar formulario de nueva contraseña
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['password'])) {
    $token = $_POST['token'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    
    // Validar contraseña
    if (strlen($password) < 8) {
        $passwordError = "La contraseña debe tener al menos 8 caracteres";
        $error = "Por favor corrige los errores en el formulario";
    } elseif ($password !== $confirmPassword) {
        $confirmPasswordError = "Las contraseñas no coinciden";
        $error = "Por favor corrige los errores en el formulario";
    } else {
        // Verificar token nuevamente por seguridad
        $stmt = $pdo->prepare("SELECT email FROM password_resets WHERE token = ? AND expires > NOW()");
        $stmt->execute([$token]);
        $resetRequest = $stmt->fetch();
        
        if ($resetRequest) {
            $email = $resetRequest['email'];
            
            // Actualizar contraseña en la base de datos
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET password = ? WHERE email = ?");
            $stmt->execute([$hashedPassword, $email]);
            
            // Eliminar el token usado
            $stmt = $pdo->prepare("DELETE FROM password_resets WHERE token = ?");
            $stmt->execute([$token]);
            
            $success = "¡Contraseña actualizada correctamente! Ahora puedes iniciar sesión.";
            $validToken = false; // Para ocultar el formulario después del éxito
        } else {
            $error = "El enlace de restablecimiento no es válido o ha expirado";
        }
    }
    
    // Si hay errores, mantener el token válido para mostrar el formulario
    if ($error && !$success) {
        $validToken = true;
    }
}

// Función para marcar campos como erróneos
function isInvalid($error) {
    return !empty($error) ? 'error' : '';
}
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>iTiket - Nueva Contraseña</title>
    <link rel="stylesheet" type="text/css" href="./../style/style_reset.css"/>
    <link rel="icon" type="image/png" href="./../image/itiket_logo.png">

<style>
    /* Estilos CSS */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: 'Arial', sans-serif;
    }

    body {
        background-image: url("./../image/fondo.png");
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        justify-content: center;
        align-items: center;
        height: 100vh;
        color: #0079d8;
    }

    .login-container {
        background-color: rgba(0, 0, 0, 0.85);
        border-radius: 10px;
        box-shadow: 0 0 20px rgba(0, 121, 216, 0.7);
        padding: 30px;
        width: 100%;
        max-width: 400px;
        backdrop-filter: blur(5px);
        border: 1px solid #0079d8;
        animation: glow 2s infinite alternate;
    }

    @keyframes glow {
        from { box-shadow: 0 0 10px rgba(0, 121, 216, 0.7); }
        to { box-shadow: 0 0 20px rgba(0, 121, 216, 1); }
    }

    .login-header {
        text-align: center;
        margin-bottom: 30px;
    }

    .login-header h1 {
        color: #0079d8;
        font-size: 28px;
        margin-bottom: 10px;
        text-shadow: 0 0 10px rgba(0, 121, 216, 0.5);
    }

    .login-header p {
        color: #a4ceee;
        font-size: 14px;
    }

    .form-group {
        margin-bottom: 25px;
        position: relative;
    }

    .form-group label {
        display: block;
        margin-bottom: 8px;
        color: #0079d8;
        font-weight: bold;
        font-size: 14px;
    }

    .form-group input {
        width: 100%;
        padding: 12px 15px;
        background-color: rgba(255, 255, 255, 0.1);
        border: 1px solid #0079d8;
        border-radius: 5px;
        font-size: 16px;
        transition: all 0.3s;
        color: white;
    }

    .form-group input:focus {
        border-color: #4a90e2;
        outline: none;
        box-shadow: 0 0 10px rgba(74, 144, 226, 0.5);
        background-color: rgba(255, 255, 255, 0.15);
    }

    .password-input-container {
        position: relative;
    }

    .login-button {
        width: 100%;
        padding: 14px;
        background-color: #0079d8;
        color: black;
        border: none;
        border-radius: 5px;
        font-size: 16px;
        font-weight: bold;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 10px;
    }

    .login-button:hover {
        background-color: #3a7bc8;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 121, 216, 0.4);
    }

    .login-button:active {
        transform: translateY(0);
    }

    .signup-link {
        text-align: center;
        margin-top: 25px;
        font-size: 14px;
    }

    .signup-link p {
        color: #a4ceee;
    }

    .signup-link a {
        color: #0079d8;
        text-decoration: none;
        font-weight: bold;
        transition: color 0.3s;
    }

    .signup-link a:hover {
        color: #4a90e2;
        text-decoration: underline;
    }

    .error-message {
        color: #ff6b6b;
        font-size: 13px;
        margin-top: 5px;
    }

    .form-group.error input {
        border-color: #ff6b6b;
        background-color: rgba(255, 107, 107, 0.1);
    }

    /* Mensajes de API */
    .api-error, .api-success {
        padding: 12px;
        border-radius: 5px;
        margin-bottom: 20px;
        text-align: center;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        animation: fadeIn 0.3s ease-out;
    }

    .api-error {
        background-color: rgba(255, 107, 107, 0.2);
        color: #ff6b6b;
        border: 1px solid #ff6b6b;
    }

    .api-success {
        background-color: rgba(0, 216, 121, 0.2);
        color: #00d879;
        border: 1px solid #00d879;
    }

    .api-error svg, .api-success svg {
        width: 18px;
        height: 18px;
    }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(-10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    /* Estado de carga */
    .loading .login-button {
        position: relative;
        color: transparent;
    }

    .loading .login-button::after {
        content: "";
        position: absolute;
        width: 18px;
        height: 18px;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        border: 3px solid rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        border-top-color: white;
        animation: spin 1s ease-in-out infinite;
    }

    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    .success-animation {
        animation: pulseSuccess 1.5s infinite;
    }

    @keyframes pulseSuccess {
        0% { box-shadow: 0 0 0 0 rgba(0, 216, 121, 0.4); }
        70% { box-shadow: 0 0 0 15px rgba(0, 216, 121, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 216, 121, 0); }
    }
</style>

  </head>
  <body>
    <div class="login-container <?php echo $success ? 'success-animation' : ''; ?>">
      <div class="login-header">
          <h1><?php echo $validToken ? 'Establecer Nueva Contraseña' : 'Restablecer Contraseña'; ?></h1>
          <p><?php echo $validToken ? 'Crea una nueva contraseña para tu cuenta' : ''; ?></p>
      </div>
      
      <?php if ($error): ?>
      <div class="api-error">
          <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
          </svg>
          <?php echo $error; ?>
      </div>
      <?php endif; ?>
      
      <?php if ($success): ?>
      <div class="api-success">
          <svg viewBox="0 0 24 24" fill="currentColor">
              <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
          </svg>
          <?php echo $success; ?>
      </div>
      <?php endif; ?>
      
      <?php if ($validToken): ?>
      <form method="POST" id="resetPasswordForm" <?php echo !empty($error) ? 'class="loading"' : ''; ?>>
          <input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token']); ?>">
          
          <div class="form-group <?php echo isInvalid($passwordError); ?>">
              <label for="password">Nueva Contraseña</label>
              <div class="password-input-container">
                  <input type="password" id="password" name="password" placeholder="Ingresa tu nueva contraseña" required
                         value="<?php echo htmlspecialchars($password); ?>">
              </div>
              <?php if ($passwordError): ?>
              <div class="error-message"><?php echo $passwordError; ?></div>
              <?php endif; ?>
          </div>
          
          <div class="form-group <?php echo isInvalid($confirmPasswordError); ?>">
              <label for="confirmPassword">Confirmar Contraseña</label>
              <div class="password-input-container">
                  <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Repite tu contraseña" required
                         value="<?php echo htmlspecialchars($confirmPassword); ?>">
              </div>
              <?php if ($confirmPasswordError): ?>
              <div class="error-message"><?php echo $confirmPasswordError; ?></div>
              <?php endif; ?>
          </div>
          
          <button type="submit" class="login-button">Actualizar Contraseña</button>
      </form>
      <?php endif; ?>
      
      <div class="signup-link">
          <p>¿Recordaste tu contraseña? <a href="./login.html">Inicia Sesión</a></p>
      </div>
    </div>
  </body>
</html>
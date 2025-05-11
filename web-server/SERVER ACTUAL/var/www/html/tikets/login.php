<?php
session_start();
include 'includes/db.php';

$errorMessage = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usernameOrEmail = trim($_POST['username']);
    $password = $_POST['password'];

    // Verificar si hay caracteres con tilde o especiales
    if (preg_match('/[áéíóúÁÉÍÓÚñÑüÜ]/', $usernameOrEmail)) {
        $errorMessage = "El nombre de usuario o correo electrónico no puede contener tildes o caracteres especiales.";
    } else {
        // Consultar por nombre de usuario o email
        $query = "SELECT * FROM usuarios WHERE username = $1 OR email = $1";
        $result = pg_prepare($conn, "login_usuario", $query);
        $result = pg_execute($conn, "login_usuario", [$usernameOrEmail]);

        if (pg_num_rows($result) === 1) {
            $row = pg_fetch_assoc($result);

            // Verificar contraseña
            if (password_verify($password, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['username'] = $row['username'];
                $_SESSION['rol'] = $row['rol'];

                if ($row['rol'] === 'admin') {
                    header("Location: menu.php");
                } else {
                    header("Location: clientmenu.php");
                }
                exit;
            }
        }

        $errorMessage = "Nombre de usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
    <title>iTiket</title>
    <link rel="stylesheet" type="text/css" href="./../style/style_login.css"/>
    <link rel="icon" type="image/png" href="./../image/itiket_logo.png">
  </head>
  <body>
    <div class="login-container" id="loginContainer">
      <div class="login-header">
          <h1>Iniciar Sesión</h1>
      </div>
      
      <?php if (!empty($errorMessage)): ?>
        <div class="error-message" style="text-align:center;color:red;margin-bottom:10px;">
          <?php echo htmlspecialchars($errorMessage); ?>
        </div>
      <?php endif; ?>

      <form id="loginForm" method="POST" action="login.php">
          <div class="form-group" id="usernameGroup">
              <label for="username">Usuario o Email</label>
              <input type="text" id="username" name="username" placeholder="Ingresa tu usuario o email" required>
              <div class="error-message" id="usernameError"></div>
          </div>
          
          <div class="form-group" id="passwordGroup">
              <label for="password">Contraseña</label>
              <div class="password-input-container">
                  <input type="password" id="password" name="password" placeholder="Ingresa tu contraseña" required>
                  <span class="password-toggle" id="togglePassword">Mostrar</span>
              </div>
              <div class="error-message" id="passwordError"></div>
          </div>
          
          <div class="remember-forgot">
              <div class="remember-me">
                  <input type="checkbox" id="remember" name="remember">
                  <label for="remember">Recordarme</label>
              </div>
              <div class="forgot-password">
                  <a href="./procesar_reset.php" id="forgotPassword">¿Olvidaste tu contraseña?</a>
              </div>
          </div>
          
          <button type="submit" class="login-button" id="loginButton">Iniciar Sesión</button>
      </form>
      
      <div class="signup-link">
          <p>¿No tienes una cuenta? <a href="registro.php" id="signupLink">Regístrate aquí</a></p>
      </div>
    </div>

    <script>
      document.getElementById('loginForm').addEventListener('submit', function(event) {
        var username = document.getElementById('username').value;

        // Expresión regular para verificar caracteres no permitidos (tildes y caracteres especiales)
        var regex = /[áéíóúÁÉÍÓÚñÑüÜ]/;

        if (regex.test(username)) {
          alert('El nombre de usuario o correo electrónico no puede contener tildes o caracteres especiales.');
          event.preventDefault(); // Prevenir el envío del formulario
          return false;
        }
        return true;
      });
    </script>
  </body>
</html>

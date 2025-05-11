<?php
include 'includes/db.php';

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];
    $termsAccepted = isset($_POST['terms']);

    // Verificar si hay caracteres con tilde o especiales
    if (preg_match('/[áéíóúÁÉÍÓÚñÑüÜ]/', $username)) {
        $mensaje = "El nombre de usuario no puede contener tildes o caracteres especiales.";
    } elseif (!$termsAccepted) {
        $mensaje = "Debes aceptar los términos y condiciones.";
    } elseif ($password !== $confirmPassword) {
        $mensaje = "Las contraseñas no coinciden.";
    } else {
        $password = password_hash($password, PASSWORD_BCRYPT);
        $rol = 'usuario'; // Rol por defecto

        $sql = "INSERT INTO usuarios (username, email, password, rol) VALUES ($1, $2, $3, $4)";
        $prep = pg_prepare($conn, "registro_usuario", $sql);
        $result = pg_execute($conn, "registro_usuario", [$username, $email, $password, $rol]);

        if ($result) {
            $mensaje = "✅ Usuario registrado correctamente.";
        } else {
            $mensaje = "❌ Error al registrar el usuario.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>iTiket - Crear Cuenta</title>
    <link rel="stylesheet" type="text/css" href="./../style/style_signup.css">
    <link rel="icon" type="image/png" href="./../image/itiket_logo.png">
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>Crear Cuenta</h1>
            <p>Únete a nuestra plataforma</p>
        </div>

        <?php if (!empty($mensaje)): ?>
            <div style="text-align:center; color: <?= str_starts_with($mensaje, '✅') ? 'green' : 'red' ?>;">
                <?= htmlspecialchars($mensaje) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Nombre de Usuario</label>
                <input type="text" id="username" name="username" placeholder="Crea tu nombre de usuario" required>
            </div>

            <div class="form-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" placeholder="Ingresa tu email" required>
            </div>

            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="Crea tu contraseña" required>
            </div>

            <div class="form-group">
                <label for="confirmPassword">Confirmar Contraseña</label>
                <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Repite tu contraseña" required>
            </div>

            <div class="terms-conditions">
                <input type="checkbox" id="terms" name="terms" required>
                <label for="terms">Acepto los <a href="./terms.html" target="_blank">Términos y Condiciones</a></label>
            </div>

            <button type="submit" class="login-button">Registrarse</button>
        </form>

        <div class="signup-link">
            <p>¿Ya tienes una cuenta? <a href="./login.php">Inicia Sesión</a></p>
        </div>
    </div>

    <script>
      document.querySelector('form').addEventListener('submit', function(event) {
        var username = document.getElementById('username').value;

        // Expresión regular para verificar caracteres no permitidos (tildes y caracteres especiales)
        var regex = /[áéíóúÁÉÍÓÚñÑüÜ]/;

        if (regex.test(username)) {
          alert('El nombre de usuario no puede contener tildes o caracteres especiales.');
          event.preventDefault(); // Prevenir el envío del formulario
          return false;
        }
        return true;
      });
    </script>
</body>
</html>

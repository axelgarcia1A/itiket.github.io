<?php
// Conexión a la BBDD (PostgreSQL: tiket_db)
$dsn = "pgsql:host=localhost;dbname=tiket_db";
$usuario = "tiket_user";
$contrasena = "2";

try {
    $pdo = new PDO($dsn, $usuario, $contrasena);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Procesamiento del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "Correo electrónico inválido.";
    } else {
        // Verificar si el correo existe en la base de datos
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        $usuario = $stmt->fetch();

        if (!$usuario) {
            $mensaje = "El correo no está registrado.";
        } else {
            // Generar token y caducidad
            $token = bin2hex(random_bytes(16));
            $expira = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Guardar token en BBDD
            $stmt = $pdo->prepare("UPDATE usuarios SET token = :token, token_expiracion = :expira WHERE email = :email");
            $stmt->execute([
                'token' => $token,
                'expira' => $expira,
                'email' => $email
            ]);

            // Enviar correo
            $enlace = "https://172.22.9.229/nuevo_password.php?token=$token";
            $asunto = "Recuperar contraseña - iTiket";
            $mensajeCorreo = "Hola,\n\nHaz clic en el siguiente enlace para restablecer tu contraseña:\n$enlace\n\nEste enlace caduca en 1 hora.";

            // Enviar correo (usando mail())
            if (mail($email, $asunto, $mensajeCorreo)) {
                $mensaje = "Correo de recuperación enviado a $email.";
            } else {
                $mensaje = "Error al enviar el correo. Inténtalo más tarde.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <style>
        body{
            background-color: #555555;
        }
    </style>
</head>
<body>
    <h2>Recuperar Contraseña</h2>
    <?php if (isset($mensaje)) echo "<p><strong>$mensaje</strong></p>"; ?>
    <form method="POST" action="">
        <label for="email">Correo electrónico:</label>
        <input type="email" name="email" id="email" required>
        <button type="submit">Enviar instrucciones</button>
    </form>
</body>
</html>

<?php
require 'includes/db.php';
require 'vendor/autoload.php'; // PHPMailer
use PHPMailer\PHPMailer\PHPMailer;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = strtolower(trim($_POST['email']));

    $res = pg_query_params($conn, "SELECT id FROM usuarios WHERE email = $1", [$email]);
    if (pg_num_rows($res) === 0) {
        die("Correo no registrado.");
    }

    $user = pg_fetch_assoc($res);
    $token = bin2hex(random_bytes(32));
    $expires = date("Y-m-d H:i:s", strtotime("+1 hour"));

    pg_query_params($conn, "INSERT INTO password_resets (user_id, token, expires_at) VALUES ($1, $2, $3)", [$user['id'], $token, $expires]);

    // Enviar correo
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.example.com'; // Cambia esto
        $mail->SMTPAuth = true;
        $mail->Username = 'correo@example.com';
        $mail->Password = 'clave';
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('correo@example.com', 'iTiket');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Restablecimiento de contraseña';
        $mail->Body = "
            <h2>Solicitud de restablecimiento</h2>
            <p>Haz clic en el enlace para restablecer tu contraseña:</p>
            <a href='http://tu_dominio/reset_password.php?token=$token'>Restablecer contraseña</a><br><br>
            <p>Este enlace expirará en 1 hora.</p>
        ";

        $mail->send();
        echo "Correo enviado con instrucciones.";
    } catch (Exception $e) {
        echo "No se pudo enviar el correo: {$mail->ErrorInfo}";
    }
} else {
    echo "Petición no válida.";
}

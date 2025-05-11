<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['token'])) {
    $token = $_GET['token'];

    $res = pg_query_params($conn,
        "SELECT * FROM password_resets WHERE token = $1 AND expires_at > NOW() AND usado = FALSE",
        [$token]
    );

    if (pg_num_rows($res) === 0) {
        die("El enlace no es válido o ha expirado.");
    }

    $_SESSION['reset_token'] = $token;
    echo '<form method="POST">
            <label>Nueva contraseña:</label><input type="password" name="new_password" required>
            <button type="submit">Restablecer</button>
          </form>';
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['reset_token'])) {
    $token = $_SESSION['reset_token'];
    $password = password_hash($_POST['new_password'], PASSWORD_DEFAULT);

    $res = pg_query_params($conn,
        "SELECT user_id FROM password_resets WHERE token = $1 AND expires_at > NOW() AND usado = FALSE",
        [$token]
    );

    if (pg_num_rows($res) === 0) {
        die("Token inválido o expirado.");
    }

    $data = pg_fetch_assoc($res);
    pg_query_params($conn, "UPDATE usuarios SET password = $1 WHERE id = $2", [$password, $data['user_id']]);
    pg_query_params($conn, "UPDATE password_resets SET usado = TRUE WHERE token = $1", [$token]);

    unset($_SESSION['reset_token']);
    echo "Contraseña actualizada. <a href='index.php'>Iniciar sesión</a>";
}
?>

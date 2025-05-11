<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirige al inicio de sesión si no está autenticado
    exit;
}

// Página principal después de iniciar sesión
echo "<h1>Bienvenido a iTiket</h1>";
echo "<p><a href='nuevo_tiket.php'>Crear un nuevo tiket</a></p>";
echo "<p><a href='ver_tikets.php'>Ver mis tikets</a></p>";
?>

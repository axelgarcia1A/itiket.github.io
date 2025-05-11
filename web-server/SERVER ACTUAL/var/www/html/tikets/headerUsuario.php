<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<header>
    <h1>iTiket</h1>
    <nav>
        <a href="index.php">Inicio</a>
        <?php if (isset($_SESSION['usuario_id'])): ?>
            <a href="menu.php">Menú</a>
            <a href="nuevo_tiket.php">Nuevo Tiket</a>
            <a href="work.php">Mis Tikets</a>
            <span>👤 <?= htmlspecialchars($_SESSION['usuario_nombre']) ?></span>
            <a href="logout.php">Cerrar sesión</a>
        <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        <?php endif; ?>
    </nav>
</header>

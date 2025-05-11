<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include_once 'includes/db.php';

$contador_notif = 0;

if (isset($_SESSION['user_id']) && $_SESSION['rol'] === 'usuario') {
    $res = pg_query_params($conn,
        "SELECT COUNT(*) FROM notificaciones WHERE user_id = $1 AND leida = FALSE",
        [$_SESSION['user_id']]
    );
    if ($row = pg_fetch_assoc($res)) {
        $contador_notif = $row['count'];
    }
}
?>

<nav style="background-color: #eee; padding: 10px;">
    <a href="menu.php">🏠 Menú</a> |
    <a href="ver_tikets.php">📋 Ver Tikets</a> |
    <a href="nuevo_tiket.php">📝 Crear Tiket</a> |
    <a href="menu.php#notificaciones">🔔 <?= $contador_notif ?></a> |
    <a href="logout.php">🔒 Cerrar sesión</a>
</nav>

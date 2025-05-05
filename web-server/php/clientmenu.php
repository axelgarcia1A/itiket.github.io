<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

include 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTiket</title>
    <link rel="stylesheet" href="./../style/style_cliente.css">
    <link rel="icon" type="image/png" href="./web/image/itiket_logo.png">
</head>
<body>
    <h1>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h1>
    
    <hr>
    
    <h2>📋 Acciones disponibles:</h2>
    <ul>
        <li><a href="ver_tikets.php">Ver mis tikets</a></li>
        <li><a href="nuevo_tiket.php">Crear nuevo tiket</a></li>
        <li><a href="index.php">Volver a inicio</a></li>
        <li><a href="logout.php">Cerrar sesión</a></li>
    </ul>
    
    <hr>
    
    <a name="notificaciones"></a>
    <h2>🔔 Notificaciones</h2>
    
    <?php
    $user_id = $_SESSION['user_id'];
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_leida'])) {
        $notif_id = $_POST['notif_id'];
        pg_query_params($conn, "UPDATE notificaciones SET leida = TRUE WHERE id = $1 AND user_id = $2", [$notif_id, $user_id]);
        header("Location: menu.php#notificaciones");
        exit;
    }
    
    $res = pg_query_params($conn,
        "SELECT * FROM notificaciones WHERE user_id = $1 ORDER BY fecha DESC",
        [$user_id]
    );
    
    if (pg_num_rows($res) > 0) {
        while ($notif = pg_fetch_assoc($res)) {
            $class = $notif['leida'] ? 'notification' : 'notification unread';
            echo "<div class='$class'>";
            echo htmlspecialchars($notif['mensaje']);
            echo "<small>".$notif['fecha']."</small>";
            if (!$notif['leida']) {
                echo "<form method='POST'><input type='hidden' name='notif_id' value='{$notif['id']}'>";
                echo "<button type='submit' name='marcar_leida'>Marcar como leída</button></form>";
            }
            echo "</div>";
        }
    } else {
        echo "<p>No tienes notificaciones.</p>";
    }
    ?>
    
    <script src="./../JS/script_cliente.js"></script>
</body>
</html>
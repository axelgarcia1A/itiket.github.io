<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// Marcar notificación como leída
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_leida'])) {
    $notif_id = $_POST['notif_id'];
    pg_query_params($conn, "UPDATE notificaciones SET leida = TRUE WHERE id = $1 AND user_id = $2", [$notif_id, $_SESSION['user_id']]);
    header("Location: menu.php#notificaciones");
    exit;
}

// Obtener notificaciones
$res = pg_query_params($conn,
    "SELECT * FROM notificaciones WHERE user_id = $1 ORDER BY fecha DESC",
    [$_SESSION['user_id']]
);
$notificaciones = pg_fetch_all($res) ?: [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTIket</title>
    <link rel="stylesheet" href="./../style/style_menu.css">
    <link rel="icon" type="image/png" href="./web/image/itiket_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="user-container">
        <?php include 'includes/header.php'; ?>

        <main class="user-main">
            <section class="welcome-section">
                <h1><i class="fas fa-user-circle"></i> Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h1>
                <p class="welcome-message">Gestiona tus tikets y notificaciones desde este panel.</p>
            </section>

            <section class="quick-actions">
                <h2><i class="fas fa-bolt"></i> Acciones rápidas</h2>
                <div class="action-grid">
                    <a href="ver_tikets.php" class="action-card">
                        <i class="fas fa-ticket-alt"></i>
                        <span>Mis Tikets</span>
                    </a>
                    <a href="nuevo_tiket.php" class="action-card">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nuevo Tiket</span>
                    </a>
                    <a href="index.php" class="action-card">
                        <i class="fas fa-home"></i>
                        <span>Volver a Inicio</span>
                    </a>
                    <a href="logout.php" class="action-card">
                        <i class="fas fa-sign-out-alt"></i>
                        <span>Cerrar Sesión</span>
                    </a>
                </div>
            </section>

            <section id="notificaciones" class="notifications-section">
                <div class="section-header">
                    <h2><i class="fas fa-bell"></i> Notificaciones</h2>
                    <span class="badge"><?= count(array_filter($notificaciones, fn($n) => !$n['leida'])) ?></span>
                </div>

                <?php if (!empty($notificaciones)): ?>
                    <div class="notifications-list">
                        <?php foreach ($notificaciones as $notif): ?>
                            <div class="notification-card <?= $notif['leida'] ? 'read' : 'unread' ?>">
                                <div class="notification-content">
                                    <p class="notification-message"><?= htmlspecialchars($notif['mensaje']) ?></p>
                                    <small class="notification-date"><?= $notif['fecha'] ?></small>
                                </div>
                                <?php if (!$notif['leida']): ?>
                                    <form method="POST" class="notification-form">
                                        <input type="hidden" name="notif_id" value="<?= $notif['id'] ?>">
                                        <button type="submit" name="marcar_leida" class="mark-read-btn">
                                            <i class="fas fa-check"></i> Marcar como leída
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-inbox"></i>
                        <p>No tienes notificaciones</p>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script src="./../JS/script_menu.js"></script>
</body>
</html>
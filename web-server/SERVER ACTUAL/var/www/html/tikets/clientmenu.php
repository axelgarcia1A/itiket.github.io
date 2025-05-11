<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}

// CONTENIDO DE HEADER.PHP INCLUIDO DIRECTAMENTE
$contador_notif = 0;

if ($_SESSION['rol'] === 'usuario') {
    $res = pg_query_params($conn,
        "SELECT COUNT(*) FROM notificaciones WHERE user_id = $1 AND leida = FALSE",
        [$_SESSION['user_id']]
    );
    if ($row = pg_fetch_assoc($res)) {
        $contador_notif = $row['count'];
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>iTiket</title>
    <link rel="icon" type="image/png" href="./image/itiket_logo.png">
    <style>
        :root {
        --primary: #0079D8;
        --primary-dark: #0065b8;
        --text-color: #2c3e50;
        --bg-color: #ffffff;
        --border-radius: 8px;
        --shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        line-height: 1.6;
        color: var(--text-color);
        background-color: #f5f7fa;
        margin: 0;
        padding: 0;
        }

        .container {
        max-width: 1200px;
        margin: 2rem auto;
        padding: 0 1rem;
        }

        /* Navbar */
        .navbar {
        background: var(--primary);
        padding: 1rem 2rem;
        border-radius: var(--border-radius);
        margin-bottom: 2rem;
        display: flex;
        justify-content: space-between;
        align-items: center;
        box-shadow: var(--shadow);
        }

        .navbar a {
        color: white !important;
        text-decoration: none;
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: var(--border-radius);
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        .navbar a:hover {
        background: rgba(255, 255, 255, 0.2);
        transform: translateY(-2px);
        }

        .navbar .badge {
        background: #e74c3c;
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 0.7rem;
        margin-left: 0.3rem;
        }

        /* Header */
        .header {
        text-align: center;
        margin-bottom: 2rem;
        }

        .header h1 {
        font-size: 2.5rem;
        color: var(--primary);
        margin-bottom: 0.5rem;
        }

        .admin-link {
        background: var(--primary-dark);
        color: white;
        padding: 0.5rem 1.5rem;
        border-radius: 20px;
        display: inline-block;
        font-size: 0.9rem;
        margin-top: 1rem;
        transition: all 0.3s ease;
        }

        .admin-link:hover {
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        }

        /* Secciones */
        .actions, .notifications-section {
        background: var(--bg-color);
        border-radius: var(--border-radius);
        padding: 2rem;
        margin-bottom: 2rem;
        box-shadow: var(--shadow);
        }

        .actions h2, .notifications-section h2 {
        font-size: 1.5rem;
        color: var(--primary);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        /* Listas */
        ul {
        list-style: none;
        padding: 0;
        margin: 0;
        }

        .actions li {
        margin-bottom: 1rem;
        }

        .actions li a {
        display: block;
        padding: 1rem;
        background: rgba(0, 121, 216, 0.05);
        border-radius: var(--border-radius);
        color: var(--text-color);
        text-decoration: none;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        }

        .actions li a:hover {
        background: rgba(0, 121, 216, 0.1);
        transform: translateX(5px);
        }

        /* Notificaciones */
        .notifications-list {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        }

        .notification {
        background: var(--bg-color);
        border-radius: var(--border-radius);
        padding: 1.5rem;
        box-shadow: var(--shadow);
        border-left: 4px solid #ddd;
        transition: all 0.3s ease;
        }

        .notification.unread {
        border-left-color: var(--primary);
        background: rgba(0, 121, 216, 0.05);
        animation: pulse 2s infinite;
        }

        .notification:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .notification .content p {
        margin: 0;
        font-size: 1rem;
        }

        .notification .date {
        color: #7f8c8d;
        font-size: 0.85rem;
        margin-top: 0.5rem;
        }

        /* Botones */
        button {
        background: var(--primary);
        color: white;
        border: none;
        padding: 0.8rem 1.5rem;
        border-radius: var(--border-radius);
        cursor: pointer;
        font-size: 1rem;
        transition: all 0.3s ease;
        margin-top: 1rem;
        }

        button:hover {
        background: var(--primary-dark);
        transform: translateY(-2px);
        box-shadow: var(--shadow);
        }

        /* Estado vacío */
        .empty {
        color: #7f8c8d;
        font-style: italic;
        text-align: center;
        padding: 2rem;
        background: rgba(0, 121, 216, 0.05);
        border-radius: var(--border-radius);
        }

        /* Animaciones */
        @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(0, 121, 216, 0.4); }
        70% { box-shadow: 0 0 0 10px rgba(0, 121, 216, 0); }
        100% { box-shadow: 0 0 0 0 rgba(0, 121, 216, 0); }
        }

        /* Responsive */
        @media (max-width: 768px) {
        .navbar {
            flex-direction: column;
            gap: 0.5rem;
            padding: 1rem;
        }
        
        .container {
            padding: 0.5rem;
        }
        
        .actions, .notifications-section {
            padding: 1rem;
        }
        }
    </style>
</head>
<body>
    <div class="container">

        <div class="header">
            <h1>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h1>
            <?php if ($_SESSION['rol'] === 'admin'): ?>
                <p class="admin-link"><a href='admin_tikets.php'>🔧 Panel de Administración</a></p>
            <?php endif; ?>
        </div>

        <hr>

        <div class="actions">
            <h2>Acciones disponibles</h2>
            <ul>
                <li><a href="ver_tikets.php">📋 Ver mis tikets</a></li>
                <li><a href="nuevo_tiket.php">📝 Crear nuevo tiket</a></li>
                <li><a href="index.php">🏠 Volver a inicio</a></li>
                <li><a href="clientmenu.php#notifications">🔔 Notificaciones</a></li>
                <li><a href="logout.php">🔒 Cerrar sesión</a></li>
            </ul>
        </div>

        <hr>

        <a name="notificaciones"></a>
        <div class="notifications-section" id="notifications">
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
                echo "<div class='notifications-list'>";
                while ($notif = pg_fetch_assoc($res)) {
                    $leida = $notif['leida'] ? "" : "unread";
                    echo "<div class='notification $leida'>";
                    echo "<div class='content'>";
                    echo "<p>" . htmlspecialchars($notif['mensaje']) . "</p>";
                    echo "<span class='date'>" . $notif['fecha'] . "</span>";
                    echo "</div>";
                    if (!$notif['leida']) {
                        echo "<form method='POST'>";
                        echo "<input type='hidden' name='notif_id' value='{$notif['id']}'>";
                        echo "<button type='submit' name='marcar_leida'>Marcar como leída</button>";
                        echo "</form>";
                    }
                    echo "</div>";
                }
                echo "</div>";
            } else {
                echo "<p class='empty'>No tienes notificaciones.</p>";
            }
            ?>
        </div>
    </div>

    <script>
        // Confirmación para cerrar sesión
        document.querySelector('a[href="logout.php"]')?.addEventListener('click', function(e) {
            if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                e.preventDefault();
            }
        });
        
        // Animación para las notificaciones no leídas
        document.querySelectorAll('.notification.unread').forEach(notif => {
            notif.style.animation = 'pulse 2s infinite';
        });
        
        // Efecto de hover en los elementos de lista
        document.querySelectorAll('.actions li').forEach(item => {
            item.addEventListener('mouseenter', function() {
                this.style.transform = 'translateX(5px)';
            });
            item.addEventListener('mouseleave', function() {
                this.style.transform = '';
            });
        });
    </script>
</body>
</html>
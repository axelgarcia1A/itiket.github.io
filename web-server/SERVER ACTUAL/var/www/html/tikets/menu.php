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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTiket</title>
    <link rel="icon" type="image/png" href="image/itiket_logo.png">
    <style>
        :root {
            --primary-color: #5865F2;
            --secondary-color: #404EED;
            --success-color: #4CAF50;
            --danger-color: #F44336;
            --warning-color: #FF9800;
            --info-color: #2196F3;
            --light-color: #f8f9fa;
            --dark-color: #343a40;
            --text-color: #333;
            --text-light: #f8f9fa;
            --border-color: #ddd;
            --nav-bg: #ffffff;
            --nav-hover: #f0f2ff;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f5f5;
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Barra de navegación */
        .navbar {
            display: flex;
            background-color: var(--nav-bg);
            border-radius: 10px;
            padding: 10px 20px;
            margin-bottom: 25px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
            flex-wrap: wrap;
        }

        .navbar a {
            color: var(--text-color);
            text-decoration: none;
            padding: 10px 15px;
            margin: 5px;
            border-radius: 6px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .navbar a:hover {
            background-color: var(--nav-hover);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .navbar a i {
            margin-right: 8px;
            font-size: 1.1em;
        }

        .badge {
            background-color: var(--danger-color);
            color: white;
            border-radius: 50%;
            width: 20px;
            height: 20px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 0.7em;
            margin-left: 5px;
        }

        /* Panel principal */
        .panel {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 25px;
        }

        .welcome-header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .welcome-header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
            font-size: 2.2em;
        }

        .admin-panel-link {
            display: inline-block;
            background-color: var(--primary-color);
            color: white;
            padding: 8px 15px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 10px;
            transition: all 0.3s;
        }

        .admin-panel-link:hover {
            background-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(88, 101, 242, 0.2);
        }

        /* Lista de acciones */
        .actions-list {
            list-style: none;
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .actions-list li a {
            display: flex;
            align-items: center;
            padding: 15px;
            background-color: white;
            border: 1px solid var(--border-color);
            border-radius: 8px;
            color: var(--text-color);
            text-decoration: none;
            transition: all 0.3s;
        }

        .actions-list li a:hover {
            border-color: var(--primary-color);
            box-shadow: 0 5px 15px rgba(88, 101, 242, 0.1);
            transform: translateY(-3px);
        }

        .actions-list li a i {
            margin-right: 10px;
            font-size: 1.3em;
            color: var(--primary-color);
        }

        /* Notificaciones */
        .notifications-container {
            background-color: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
        }

        .notifications-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid var(--border-color);
        }

        .notifications-header h2 {
            color: var(--primary-color);
            font-size: 1.5em;
        }

        .notifications-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .notification {
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            transition: all 0.3s;
        }

        .notification.unread {
            border-left: 4px solid var(--primary-color);
            background-color: rgba(88, 101, 242, 0.05);
        }

        .notification-content {
            margin-bottom: 10px;
        }

        .notification-date {
            color: #666;
            font-size: 0.85em;
        }

        .mark-read-btn {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 4px;
            padding: 6px 12px;
            cursor: pointer;
            transition: all 0.3s;
            float: right;
        }

        .mark-read-btn:hover {
            background-color: var(--secondary-color);
        }

        .empty-notifications {
            text-align: center;
            padding: 30px;
            color: #666;
        }

        /* Efecto de nueva notificación */
        @keyframes newNotification {
            0% { transform: scale(1); }
            50% { transform: scale(1.02); }
            100% { transform: scale(1); }
        }

        .new-notification {
            animation: newNotification 0.5s ease 2;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .navbar {
                flex-direction: column;
                align-items: flex-start;
            }
            
            .actions-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <nav class="navbar">
            <a href="menu.php"><i>🏠</i> Menú</a>
            <a href="ver_tikets.php"><i>📋</i> Ver Tikets</a>
            <a href="nuevo_tiket.php"><i>📝</i> Crear Tiket</a>
            <a href="menu.php#notifications" id="notifications-link">
                <i>🔔</i> Notificaciones 
                <?php if ($contador_notif > 0): ?>
                    <span class="badge" id="notification-counter"><?= $contador_notif ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php"><i>🔒</i> Cerrar sesión</a>
        </nav>

        <div class="panel">
            <div class="welcome-header">
                <h1>Bienvenido, <?= htmlspecialchars($_SESSION['username']) ?></h1>
                <?php if ($_SESSION['rol'] === 'admin'): ?>
                    <a href='admin_tikets.php' class="admin-panel-link">🔧 Panel de Administración</a>
                <?php endif; ?>
            </div>

            <h2>Acciones disponibles:</h2>
            <ul class="actions-list">
                <li><a href="ver_tikets.php"><i>📋</i> Ver mis tikets</a></li>
                <li><a href="nuevo_tiket.php"><i>📝</i> Crear nuevo tiket</a></li>
                <li><a href="index.php"><i>🏠</i> Volver a inicio</a></li>
                <li><a href="logout.php"><i>🔒</i> Cerrar sesión</a></li>
            </ul>
        </div>

        <div class="notifications-container" id="notifications">
            
            <div class="notifications-header">
                <h2><i>🔔</i> Notificaciones</h2>
            </div>

            <div class="notifications-list" id="notifications-list">
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
                        $leida = $notif['leida'] ? "" : "unread";
                        echo "<div class='notification $leida' data-id='{$notif['id']}'>";
                        echo "<div class='notification-content'>";
                        echo "<p>" . htmlspecialchars($notif['mensaje']) . "</p>";
                        echo "<span class='notification-date'>" . $notif['fecha'] . "</span>";
                        echo "</div>";
                        if (!$notif['leida']) {
                            echo "<form method='POST' class='mark-read-form'>";
                            echo "<input type='hidden' name='notif_id' value='{$notif['id']}'>";
                            echo "<button type='submit' name='marcar_leida' class='mark-read-btn'>Marcar como leída</button>";
                            echo "</form>";
                        }
                        echo "</div>";
                    }
                } else {
                    echo "<div class='empty-notifications'>";
                    echo "<p>No tienes notificaciones.</p>";
                    echo "</div>";
                }
                ?>
            </div>
        </div>
    </div>

    <script>
        // Función para actualizar notificaciones
        function checkNewNotifications() {
            fetch('check_notifications.php')
                .then(response => response.json())
                .then(data => {
                    if (data.newNotifications > 0) {
                        // Actualizar el contador
                        const counter = document.getElementById('notification-counter');
                        const currentCount = counter ? parseInt(counter.textContent) : 0;
                        const newCount = currentCount + data.newNotifications;
                        
                        if (!counter && newCount > 0) {
                            // Si no existe el badge y hay notificaciones nuevas
                            const link = document.getElementById('notifications-link');
                            link.innerHTML += `<span class="badge" id="notification-counter">${newCount}</span>`;
                        } else if (counter) {
                            counter.textContent = newCount;
                        }
                        
                        // Mostrar notificaciones nuevas
                        data.notifications.forEach(notification => {
                            const notificationsList = document.getElementById('notifications-list');
                            const emptyMsg = notificationsList.querySelector('.empty-notifications');
                            
                            if (emptyMsg) {
                                notificationsList.removeChild(emptyMsg);
                            }
                            
                            const newNotif = document.createElement('div');
                            newNotif.className = 'notification unread new-notification';
                            newNotif.dataset.id = notification.id;
                            newNotif.innerHTML = `
                                <div class="notification-content">
                                    <p>${notification.mensaje}</p>
                                    <span class="notification-date">Ahora</span>
                                </div>
                                <form method="POST" class="mark-read-form">
                                    <input type="hidden" name="notif_id" value="${notification.id}">
                                    <button type="submit" name="marcar_leida" class="mark-read-btn">Marcar como leída</button>
                                </form>
                            `;
                            
                            notificationsList.insertBefore(newNotif, notificationsList.firstChild);
                            
                            // Eliminar la clase de animación después de que termine
                            setTimeout(() => {
                                newNotif.classList.remove('new-notification');
                            }, 1000);
                        });
                        
                        // Notificación del navegador
                        if (Notification.permission === 'granted') {
                            new Notification('Tienes nuevas notificaciones', {
                                body: `Tienes ${data.newNotifications} nueva(s) notificación(es)`,
                                icon: 'https://cdn-icons-png.flaticon.com/512/565/565422.png'
                            });
                        }
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        // Solicitar permiso para notificaciones
        document.addEventListener('DOMContentLoaded', function() {
            if (Notification.permission !== 'granted' && Notification.permission !== 'denied') {
                Notification.requestPermission();
            }
            
            // Verificar nuevas notificaciones cada 30 segundos
            setInterval(checkNewNotifications, 30000);
            
            // Manejar el marcado como leído con AJAX
            document.querySelectorAll('.mark-read-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const notificationId = formData.get('notif_id');
                    const notificationElement = document.querySelector(`.notification[data-id="${notificationId}"]`);
                    
                    fetch('mark_as_read.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Actualizar la UI
                            notificationElement.classList.remove('unread');
                            const button = notificationElement.querySelector('.mark-read-btn');
                            if (button) {
                                button.remove();
                            }
                            
                            // Actualizar el contador
                            const counter = document.getElementById('notification-counter');
                            if (counter) {
                                const newCount = parseInt(counter.textContent) - 1;
                                if (newCount > 0) {
                                    counter.textContent = newCount;
                                } else {
                                    counter.remove();
                                }
                            }
                        }
                    })
                    .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
</body>
</html>
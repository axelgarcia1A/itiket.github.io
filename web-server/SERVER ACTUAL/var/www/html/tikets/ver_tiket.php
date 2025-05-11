<?php
session_start();
include 'includes/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo "Debes iniciar sesión para ver un tiket.";
    exit;
}

// Verificar si se ha pasado el ID del tiket
if (!isset($_GET['id'])) {
    echo "ID de tiket no especificado.";
    exit;
}

$tiket_id = $_GET['id'];
$user_id = $_SESSION['user_id'];

// Consultar los detalles del tiket
$sql = "SELECT * FROM tikets WHERE id = $1 AND user_id = $2";
$result = pg_query_params($conn, $sql, array($tiket_id, $user_id));

if (!$result || pg_num_rows($result) == 0) {
    echo "No se encontró el tiket o no tienes permiso para verlo.";
    exit;
}

// Obtener los detalles del tiket
$tiket = pg_fetch_assoc($result);
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
            --primary: #5865F2;
            --primary-dark: #404EED;
            --secondary: #4CAF50;
            --danger: #F44336;
            --warning: #FF9800;
            --info: #2196F3;
            --light: #f8f9fa;
            --dark: #343a40;
            --text-color: #333;
            --text-light: #f8f9fa;
            --border-color: #e0e0e0;
            --bg-color: #ffffff;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            --border-radius: 8px;
        }

        /* Estructura principal */
        .ticket-detail-container {
            max-width: 1100px;
            margin: 2rem auto;
            padding: 2rem;
            background: var(--bg-color);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            border: 1px solid rgba(0, 121, 216, 0.1);
        }

        /* Breadcrumb */
        .breadcrumb {
            margin-bottom: 1.5rem;
            font-size: 0.9rem;
            color: var(--text-color);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .breadcrumb a {
            color: var(--primary);
            text-decoration: none;
            transition: color 0.3s;
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .breadcrumb a:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        .breadcrumb span {
            color: #666;
        }

        /* Encabezado del ticket */
        .ticket-header {
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid var(--border-color);
        }

        .ticket-header h1 {
            font-size: 1.8rem;
            color: var(--dark);
            margin: 0 0 0.5rem 0;
            font-weight: 600;
        }

        .ticket-meta {
            display: flex;
            align-items: center;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
            flex-wrap: wrap;
        }

        /* Badges de estado */
        .status-badge {
            display: inline-block;
            padding: 0.3rem 0.8rem;
            border-radius: 1rem;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.abierto {
            background-color: var(--warning);
            color: white;
        }

        .status-badge.cerrado {
            background-color: var(--secondary);
            color: white;
        }

        /* Contenido principal */
        .ticket-content {
            display: grid;
            grid-template-columns: 1fr;
            gap: 2rem;
        }

        @media (min-width: 992px) {
            .ticket-content {
                grid-template-columns: 1fr 1fr;
            }
        }

        /* Sección de descripción */
        .ticket-description, 
        .ticket-responses {
            background: var(--bg-color);
            padding: 1.5rem;
            border-radius: var(--border-radius);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--border-color);
        }

        .ticket-description h2, 
        .ticket-responses h2 {
            font-size: 1.3rem;
            margin: 0 0 1rem 0;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .ticket-description p {
            line-height: 1.6;
            color: var(--text-color);
        }

        /* Lista de respuestas */
        .responses-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .response-card {
            background: #f9f9f9;
            border-radius: var(--border-radius);
            padding: 1.2rem;
            border-left: 3px solid var(--border-color);
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .response-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 3px 8px rgba(0, 0, 0, 0.1);
        }

        .response-card.own-response {
            border-left-color: var(--primary);
            background: rgba(88, 101, 242, 0.05);
        }

        .response-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.85rem;
        }

        .response-header small {
            color: #666;
        }

        .response-card p {
            margin: 0;
            line-height: 1.5;
            color: var(--text-color);
        }

        /* Estado vacío */
        .empty-state {
            text-align: center;
            padding: 2rem;
            color: #999;
            background: #fafafa;
            border-radius: var(--border-radius);
            border: 1px dashed var(--border-color);
        }

        .empty-state i {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            opacity: 0.7;
        }

        .empty-state p {
            margin: 0;
        }

        /* Formulario de respuesta */
        .response-form-container {
            margin-top: 2rem;
        }

        .response-form-container h3 {
            font-size: 1.2rem;
            margin: 0 0 1rem 0;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .response-form textarea {
            width: 100%;
            padding: 1rem;
            border: 1px solid var(--border-color);
            border-radius: var(--border-radius);
            min-height: 120px;
            margin-bottom: 0.8rem;
            font-family: inherit;
            resize: vertical;
            transition: border 0.3s, box-shadow 0.3s;
        }

        .response-form textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(88, 101, 242, 0.2);
        }

        .char-counter {
            font-size: 0.8rem;
            color: #7f8c8d;
            margin-bottom: 1rem;
            text-align: right;
        }

        .char-counter.warning {
            color: var(--warning);
            font-weight: 500;
        }

        .char-counter.danger {
            color: var(--danger);
            font-weight: 500;
        }

        /* Alertas */
        .alert {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            display: flex;
            align-items: center;
            gap: 0.7rem;
            font-size: 0.95rem;
        }

        .alert.info {
            background-color: rgba(33, 150, 243, 0.1);
            color: var(--info);
            border-left: 3px solid var(--info);
        }

        .alert.success {
            background-color: rgba(76, 175, 80, 0.1);
            color: var(--secondary);
            border-left: 3px solid var(--secondary);
        }

        .alert.error {
            background-color: rgba(244, 67, 54, 0.1);
            color: var(--danger);
            border-left: 3px solid var(--danger);
        }

        /* Botones */
        .btn {
            background-color: var(--primary);
            color: white;
            border: none;
            border-radius: var(--border-radius);
            padding: 0.8rem 1.5rem;
            cursor: pointer;
            transition: all 0.3s;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .btn:disabled {
            background-color: #cccccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        /* Responsive */
@media (max-width: 768px) {
    .ticket-detail-container {
        padding: 1.5rem;
    }
    
    .ticket-header h1 {
        font-size: 1.5rem;
    }
    
    .ticket-meta {
        gap: 0.7rem;
        font-size: 0.8rem;
    }
    
    .ticket-description, 
    .ticket-responses {
        padding: 1.2rem;
    }
}
    </style>
</head>
<body>
    <div class="ticket-detail-container">
        <div class="breadcrumb">
            <a href="menu.php">🏠 Menú</a> / <a href="ver_tikets.php">📋 Mis Tikets</a> / <span>Detalles</span>
        </div>

        <div class="ticket-header">
            <h1><?= htmlspecialchars($tiket['titulo']) ?></h1>
            <div class="ticket-meta">
                <span>ID: #<?= $tiket['id'] ?></span>
                <span class="status-badge <?= $tiket['estado'] ?>"><?= $tiket['estado'] ?></span>
                <span>Creado: <?= $tiket['fecha_creacion'] ?></span>
            </div>
        </div>

        <div class="ticket-content">
            <div class="ticket-description">
                <h2>📄 Descripción</h2>
                <p><?= nl2br(htmlspecialchars($tiket['descripcion'])) ?></p>
            </div>

            <div class="ticket-responses">
                <h2>💬 Respuestas</h2>
                
                <?php
                // Consultar las respuestas del tiket
                $sql_respuestas = "SELECT * FROM respuestas WHERE tiket_id = $1 ORDER BY fecha_respuesta ASC";
                $result_respuestas = pg_query_params($conn, $sql_respuestas, array($tiket_id));

                if (pg_num_rows($result_respuestas) > 0) {
                    echo '<div class="responses-list">';
                    while ($respuesta = pg_fetch_assoc($result_respuestas)) {
                        $is_own_response = ($respuesta['user_id'] == $user_id);
                        echo '<div class="response-card ' . ($is_own_response ? 'own-response' : '') . '">';
                        echo '<div class="response-header">';
                        echo '<span>' . ($is_own_response ? 'Tú' : 'Soporte') . '</span>';
                        echo '<small>' . $respuesta['fecha_respuesta'] . '</small>';
                        echo '</div>';
                        echo '<p>' . nl2br(htmlspecialchars($respuesta['respuesta'])) . '</p>';
                        echo '</div>';
                    }
                    echo '</div>';
                } else {
                    echo '<div class="empty-state">';
                    echo '<p>No hay respuestas aún</p>';
                    echo '</div>';
                }
                ?>

                <?php if ($tiket['estado'] == 'abierto'): ?>
                    <div class="response-form-container">
                        <h3>✏️ Responder</h3>
                        
                        <?php
                        // Mostrar mensajes de éxito/error
                        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['respuesta'])) {
                            $respuesta = $_POST['respuesta'];
                            
                            // Insertar la respuesta en la base de datos
                            $sql_respuesta = "INSERT INTO respuestas (tiket_id, user_id, respuesta, fecha_respuesta) 
                                            VALUES ($1, $2, $3, CURRENT_TIMESTAMP)";
                            $result_respuesta = pg_query_params($conn, $sql_respuesta, array($tiket_id, $user_id, $respuesta));

                            if ($result_respuesta) {
                                // Actualizar el estado del tiket a cerrado
                                $sql_update_estado = "UPDATE tikets SET estado = 'cerrado' WHERE id = $1";
                                pg_query_params($conn, $sql_update_estado, array($tiket_id));
                                
                                echo '<div class="alert success">✅ Respuesta enviada correctamente. El tiket ha sido cerrado.</div>';
                                // Actualizar el estado local para ocultar el formulario
                                $tiket['estado'] = 'cerrado';
                            } else {
                                echo '<div class="alert error">❌ Error al enviar la respuesta.</div>';
                            }
                        }
                        ?>
                        
                        <?php if ($tiket['estado'] == 'abierto'): ?>
                            <form method="post" class="response-form">
                                <div class="alert info">
                                    Al enviar una respuesta, el tiket se cerrará automáticamente.
                                </div>
                                <textarea name="respuesta" placeholder="Escribe tu respuesta aquí..." required></textarea>
                                <div class="char-counter"><span id="response-char-count">0</span>/2000 caracteres</div>
                                <button type="submit" class="btn">Enviar respuesta</button>
                            </form>
                        <?php else: ?>
                            <div class="alert info">
                                Este tiket está cerrado y no acepta nuevas respuestas.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Funciones específicas para ver_tiket.php
        function setupTicketDetailPage() {
            // Auto-expand textarea al escribir
            const textarea = document.querySelector('.response-form textarea');
            if (textarea) {
                textarea.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
                
                // Contador de caracteres
                const charCounter = document.querySelector('.char-counter');
                if (charCounter) {
                    textarea.addEventListener('input', function() {
                        const count = this.value.length;
                        document.getElementById('response-char-count').textContent = count;
                        
                        if (count > 1800) {
                            charCounter.style.color = '#e74c3c';
                        } else if (count > 1500) {
                            charCounter.style.color = '#f39c12';
                        } else {
                            charCounter.style.color = '#7f8c8d';
                        }
                    });
                }
            }
            
            // Confirmar antes de cerrar con cambios no guardados
            const responseForm = document.querySelector('.response-form');
            if (responseForm) {
                let formChanged = false;
                
                responseForm.addEventListener('change', () => formChanged = true);
                responseForm.addEventListener('input', () => formChanged = true);
                
                window.addEventListener('beforeunload', (e) => {
                    if (formChanged) {
                        e.preventDefault();
                        return e.returnValue = 'Tienes una respuesta sin enviar. ¿Seguro que quieres salir?';
                    }
                });
                
                responseForm.addEventListener('submit', () => {
                    formChanged = false;
                    const submitBtn = responseForm.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = '⏳ Enviando...';
                        submitBtn.disabled = true;
                    }
                });
            }
        }

        // Inicializar cuando el DOM esté listo
        document.addEventListener('DOMContentLoaded', setupTicketDetailPage);
    </script>
</body>
</html>
<?php
pg_close($conn);
?>
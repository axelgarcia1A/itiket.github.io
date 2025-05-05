<?php
session_start();
include 'includes/db.php';

// Verificar autenticación
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=ver_tiket&id=".$_GET['id'] ?? '');
    exit;
}

// Validar ID del tiket
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: ver_tikets.php?error=invalid_id");
    exit;
}

$tiket_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

// Obtener detalles del tiket
$sql = "SELECT t.*, u.username 
        FROM tikets t
        JOIN usuarios u ON t.user_id = u.id
        WHERE t.id = $1 AND (t.user_id = $2 OR $2 IN (SELECT id FROM usuarios WHERE rol = 'admin'))";
$result = pg_query_params($conn, $sql, [$tiket_id, $user_id]);

if (!$result || pg_num_rows($result) == 0) {
    header("Location: ver_tikets.php?error=tiket_not_found");
    exit;
}

$tiket = pg_fetch_assoc($result);

// Procesar respuesta
$success = '';
$error = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['respuesta'])) {
    $respuesta = trim($_POST['respuesta']);
    
    if (strlen($respuesta) < 10) {
        $error = "La respuesta debe tener al menos 10 caracteres";
    } else {
        $sql_respuesta = "INSERT INTO respuestas (tiket_id, user_id, respuesta, fecha_respuesta) 
                          VALUES ($1, $2, $3, CURRENT_TIMESTAMP)";
        $result_respuesta = pg_query_params($conn, $sql_respuesta, [$tiket_id, $user_id, $respuesta]);

        if ($result_respuesta) {
            // Actualizar estado si es el usuario (no admin)
            if ($_SESSION['rol'] !== 'admin') {
                pg_query_params($conn, "UPDATE tikets SET estado = 'cerrado' WHERE id = $1", [$tiket_id]);
                $tiket['estado'] = 'cerrado';
            }
            $success = "Respuesta enviada correctamente";
        } else {
            $error = "Error al enviar la respuesta: " . pg_last_error($conn);
        }
    }
}

// Obtener respuestas existentes
$respuestas = [];
$res_respuestas = pg_query_params($conn, 
    "SELECT r.*, u.username 
     FROM respuestas r
     JOIN usuarios u ON r.user_id = u.id
     WHERE r.tiket_id = $1
     ORDER BY r.fecha_respuesta ASC", 
    [$tiket_id]
);

if ($res_respuestas) {
    $respuestas = pg_fetch_all($res_respuestas) ?: [];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTiket</title>
    <link rel="stylesheet" href="./../style/style_see.css">
    <link rel="icon" type="image/png" href="./web/image/itiket_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="ticket-detail-container">
        <div class="breadcrumb">
            <a href="ver_tikets.php"><i class="fas fa-arrow-left"></i> Volver a mis tikets</a>
        </div>
        
        <div class="ticket-header">
            <h1>Tiket #<?= $tiket_id ?>: <?= htmlspecialchars($tiket['titulo']) ?></h1>
            <div class="ticket-meta">
                <span class="status-badge <?= $tiket['estado'] ?>"><?= $tiket['estado'] ?></span>
                <span>Creado por <?= htmlspecialchars($tiket['username']) ?> el <?= date('d/m/Y H:i', strtotime($tiket['fecha_creacion'])) ?></span>
            </div>
        </div>
        
        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        
        <div class="ticket-content">
            <div class="ticket-description">
                <h2><i class="fas fa-align-left"></i> Descripción</h2>
                <p><?= nl2br(htmlspecialchars($tiket['descripcion'])) ?></p>
            </div>
            
            <div class="ticket-responses">
                <h2><i class="fas fa-comments"></i> Respuestas</h2>
                
                <?php if (empty($respuestas)): ?>
                    <div class="empty-state">
                        <i class="fas fa-comment-slash"></i>
                        <p>No hay respuestas aún</p>
                    </div>
                <?php else: ?>
                    <div class="responses-list">
                        <?php foreach ($respuestas as $respuesta): ?>
                            <div class="response-card <?= $respuesta['user_id'] == $user_id ? 'own-response' : '' ?>">
                                <div class="response-header">
                                    <strong><?= htmlspecialchars($respuesta['username']) ?></strong>
                                    <small><?= date('d/m/Y H:i', strtotime($respuesta['fecha_respuesta'])) ?></small>
                                </div>
                                <div class="response-content">
                                    <?= nl2br(htmlspecialchars($respuesta['respuesta'])) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($tiket['estado'] == 'abierto' || $_SESSION['rol'] == 'admin'): ?>
                    <div class="response-form-container">
                        <h3><i class="fas fa-reply"></i> <?= $_SESSION['rol'] == 'admin' ? 'Responder como administrador' : 'Añadir respuesta' ?></h3>
                        <form method="POST" class="response-form">
                            <textarea name="respuesta" placeholder="Escribe tu respuesta aquí..." required></textarea>
                            <div class="form-actions">
                                <button type="submit" class="btn primary">
                                    <i class="fas fa-paper-plane"></i> Enviar respuesta
                                </button>
                                <?php if ($_SESSION['rol'] == 'admin' && $tiket['estado'] == 'abierto'): ?>
                                    <label class="close-ticket">
                                        <input type="checkbox" name="cerrar_tiket">
                                        Cerrar tiket después de responder
                                    </label>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert info">
                        <i class="fas fa-info-circle"></i> Este tiket está cerrado y no acepta nuevas respuestas.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <script src="./../JS/script_see.js"></script>
</body>
</html>
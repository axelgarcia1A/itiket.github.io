<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=ver_tikets");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$tikets = [];

// Obtener tikets del usuario
$sql = "SELECT t.*, 
               (SELECT COUNT(*) FROM respuestas WHERE tiket_id = t.id) as respuestas_count
        FROM tikets t 
        WHERE t.user_id = $1 
        ORDER BY t.fecha_creacion DESC";
$result = pg_query_params($conn, $sql, [$user_id]);

if (!$result) {
    $error = "Error al cargar los tikets: " . pg_last_error($conn);
} else {
    $tikets = pg_fetch_all($result) ?: [];
}

// Procesar mensajes de éxito/error
if (isset($_GET['success'])) {
    $success = match($_GET['success']) {
        'tiket_created' => 'Tiket creado exitosamente',
        'tiket_updated' => 'Tiket actualizado exitosamente',
        default => 'Operación realizada con éxito'
    };
}

if (isset($_GET['error'])) {
    $error = match($_GET['error']) {
        'invalid_id' => 'ID de tiket inválido',
        'tiket_not_found' => 'No se encontró el tiket solicitado',
        default => 'Ocurrió un error'
    };
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTiket</title>
    <link rel="stylesheet" href="./../style/style_tikets.css">
    <link rel="icon" type="image/png" href="./web/image/itiket_logo.png">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="tickets-container">
        <div class="page-header">
            <h1><i class="fas fa-ticket-alt"></i> Mis Tikets</h1>
            <a href="nuevo_tiket.php" class="btn primary">
                <i class="fas fa-plus"></i> Nuevo Tiket
            </a>
        </div>
        
        <?php if (isset($success)): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if (empty($tikets)): ?>
            <div class="empty-state">
                <i class="fas fa-inbox"></i>
                <p>No tienes tikets creados</p>
                <a href="nuevo_tiket.php" class="btn primary">
                    <i class="fas fa-plus"></i> Crear mi primer tiket
                </a>
            </div>
        <?php else: ?>
            <div class="tickets-table-container">
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Respuestas</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tikets as $tiket): ?>
                            <tr class="ticket-row <?= $tiket['estado'] ?>">
                                <td>#<?= $tiket['id'] ?></td>
                                <td><?= htmlspecialchars($tiket['titulo']) ?></td>
                                <td>
                                    <span class="status-badge <?= $tiket['estado'] ?>">
                                        <?= $tiket['estado'] ?>
                                    </span>
                                </td>
                                <td><?= $tiket['respuestas_count'] ?></td>
                                <td><?= date('d/m/Y H:i', strtotime($tiket['fecha_creacion'])) ?></td>
                                <td class="actions">
                                    <a href="ver_tiket.php?id=<?= $tiket['id'] ?>" class="btn small">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>

    <script src="./../JS/script_tikets.js"></script>
</body>
</html>
<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'No autenticado']);
    exit;
}

// Obtener la última notificación conocida por el cliente
$lastKnownId = isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0;

// Buscar nuevas notificaciones
$res = pg_query_params($conn,
    "SELECT id, mensaje FROM notificaciones 
     WHERE user_id = $1 AND id > $2 AND leida = FALSE 
     ORDER BY id DESC",
    [$_SESSION['user_id'], $lastKnownId]
);

$notifications = [];
while ($row = pg_fetch_assoc($res)) {
    $notifications[] = $row;
}

echo json_encode([
    'newNotifications' => count($notifications),
    'notifications' => $notifications
]);
?>
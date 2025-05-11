<?php
session_start();
include 'includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['notif_id'])) {
    echo json_encode(['success' => false, 'error' => 'Datos insuficientes']);
    exit;
}

$result = pg_query_params($conn,
    "UPDATE notificaciones SET leida = TRUE WHERE id = $1 AND user_id = $2",
    [$_POST['notif_id'], $_SESSION['user_id']]
);

echo json_encode([
    'success' => (bool)$result
]);
?>
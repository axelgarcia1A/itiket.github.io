<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php?redirect=nuevo_tiket");
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = pg_escape_string($conn, $_POST['titulo']);
    $descripcion = pg_escape_string($conn, $_POST['descripcion']);
    $user_id = $_SESSION['user_id'];

    // Validación adicional
    if (empty($titulo) || empty($descripcion)) {
        $error = "Todos los campos son obligatorios";
    } else {
        $query = "INSERT INTO tikets (titulo, descripcion, user_id, estado, fecha_creacion) 
                 VALUES ($1, $2, $3, 'abierto', NOW())";
        $result = pg_query_params($conn, $query, [$titulo, $descripcion, $user_id]);

        if ($result) {
            $success = "Tiket creado con éxito. Serás redirigido...";
            header("Refresh: 2; url=ver_tikets.php");
        } else {
            $error = "Error al crear el tiket: " . pg_last_error($conn);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTiket</title>
    <link rel="stylesheet" href="./../style/style_new.css">
    <link rel="icon" type="image/png" href="./web/image/itiket_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="ticket-container">
        <h1><i class="fas fa-plus-circle"></i> Crear Nuevo Tiket</h1>
        
        <?php if ($error): ?>
            <div class="alert error"><?= $error ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert success"><?= $success ?></div>
        <?php endif; ?>
        
        <form action="nuevo_tiket.php" method="POST" class="ticket-form">
            <div class="form-group">
                <label for="titulo">Título</label>
                <input type="text" name="titulo" id="titulo" required 
                       placeholder="Ejemplo: Problema con la impresora">
            </div>
            
            <div class="form-group">
                <label for="descripcion">Descripción detallada</label>
                <textarea name="descripcion" id="descripcion" rows="8" required
                          placeholder="Describe el problema con todos los detalles necesarios..."></textarea>
                <div class="char-counter"><span id="char-count">0</span>/1000 caracteres</div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn primary">
                    <i class="fas fa-paper-plane"></i> Enviar Tiket
                </button>
                <a href="ver_tikets.php" class="btn secondary">
                    <i class="fas fa-list"></i> Ver mis Tikets
                </a>
            </div>
        </form>
    </main>

    <script src="./../JS/script_new.js"></script>
</body>
</html>
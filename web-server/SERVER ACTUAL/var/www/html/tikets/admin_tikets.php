<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// --- Manejar acciones del formulario ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['responder']) && $_POST['respuesta_admin'] !== '') {
        $id = $_POST['tiket_id'];
        $respuesta = $_POST['respuesta_admin'];
        $query = "UPDATE tikets SET respuesta_admin = $1, estado = 'cerrado' WHERE id = $2";
        pg_prepare($conn, "responder_tiket", $query);
        pg_execute($conn, "responder_tiket", [$respuesta, $id]);
    }

    if (isset($_POST['cambiar_estado'])) {
        $id = $_POST['tiket_id'];
        $nuevo_estado = $_POST['nuevo_estado'];
        $query = "UPDATE tikets SET estado = $1 WHERE id = $2";
        pg_prepare($conn, "cambiar_estado", $query);
        pg_execute($conn, "cambiar_estado", [$nuevo_estado, $id]);
    }

    if (isset($_POST['crear_tiket_admin'])) {
        $titulo = $_POST['titulo'];
        $descripcion = $_POST['descripcion'];
        $admin_id = $_SESSION['user_id'];
        $query = "INSERT INTO tikets (user_id, titulo, descripcion, estado, fecha_creacion) VALUES ($1, $2, $3, 'abierto', NOW())";
        pg_prepare($conn, "crear_tiket_admin", $query);
        pg_execute($conn, "crear_tiket_admin", [$admin_id, $titulo, $descripcion]);
    }

    if (isset($_POST['eliminar_tiket'])) {
        $id = $_POST['tiket_id'];
        $query = "DELETE FROM tikets WHERE id = $1";
        pg_prepare($conn, "eliminar_tiket", $query);
        pg_execute($conn, "eliminar_tiket", [$id]);
    }

    header("Location: admin_tikets.php?estado=" . ($_GET['estado'] ?? ''));
    exit;
}

// --- Mostrar tikets ---
$filtro = $_GET['estado'] ?? '';
$query = "SELECT tikets.*, usuarios.username FROM tikets JOIN usuarios ON tikets.user_id = usuarios.id";
if ($filtro === 'abierto' || $filtro === 'cerrado') {
    $query .= " WHERE estado = '$filtro'";
}
$query .= " ORDER BY fecha_creacion DESC";
$result = pg_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>iTiket</title>
    <link rel="stylesheet" href="./style/style_admin.css">
    <link rel="icon" type="image/png" href="./../image/itiket_logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <header class="admin-header">
            <h1><i class="fas fa-ticket-alt"></i> Panel de Administración</h1>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar sesión</a>
        </header>

        <div class="admin-content">
            <div class="filters-section">
                <form method="GET" class="filter-form">
                    <label>Filtrar por estado:</label>
                    <select name="estado">
                        <option value="">Todos</option>
                        <option value="abierto" <?= $filtro == 'abierto' ? 'selected' : '' ?>>Abiertos</option>
                        <option value="cerrado" <?= $filtro == 'cerrado' ? 'selected' : '' ?>>Cerrados</option>
                    </select>
                    <button type="submit" class="filter-btn"><i class="fas fa-filter"></i> Filtrar</button>
                </form>
            </div>

            <div class="create-ticket-section">
                <h2><i class="fas fa-plus-circle"></i> Crear nuevo tiket</h2>
                <form method="POST" class="ticket-form">
                    <div class="form-group">
                        <input type="text" name="titulo" placeholder="Título" required>
                    </div>
                    <div class="form-group">
                        <textarea name="descripcion" placeholder="Descripción" required></textarea>
                    </div>
                    <button type="submit" name="crear_tiket_admin" class="submit-btn">
                        <i class="fas fa-paper-plane"></i> Crear tiket
                    </button>
                </form>
            </div>

            <div class="tickets-list">
                <h2><i class="fas fa-list"></i> Listado de Tikets</h2>
                <div class="responsive-table">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Título</th>
                                <th>Usuario</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($row = pg_fetch_assoc($result)) : ?>
                                <tr class="<?= $row['estado'] === 'abierto' ? 'open' : 'closed' ?>">
                                    <td><?= htmlspecialchars($row['id']) ?></td>
                                    <td><?= htmlspecialchars($row['titulo']) ?></td>
                                    <td><?= htmlspecialchars($row['username']) ?></td>
                                    <td>
                                        <span class="status-badge <?= $row['estado'] ?>">
                                            <?= htmlspecialchars($row['estado']) ?>
                                        </span>
                                    </td>
                                    <td><?= date('d/m/Y H:i', strtotime($row['fecha_creacion'])) ?></td>
                                    <td class="actions">
                                        <div class="ticket-actions">
                                            <button class="action-btn view-btn" onclick="toggleDetails(<?= $row['id'] ?>)">
                                                <i class="fas fa-eye"></i> Detalles
                                            </button>
                                            
                                            <div class="ticket-details" id="details-<?= $row['id'] ?>">
                                                <div class="details-content">
                                                    <h4>Descripción:</h4>
                                                    <p><?= nl2br(htmlspecialchars($row['descripcion'])) ?></p>
                                                    
                                                    <h4>Respuesta:</h4>
                                                    <form method="POST" class="response-form">
                                                        <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                                                        <textarea name="respuesta_admin" placeholder="Escribe tu respuesta..."><?= htmlspecialchars($row['respuesta_admin']) ?></textarea>
                                                        <div class="form-actions">
                                                            <button type="submit" name="responder" class="submit-btn small">
                                                                <i class="fas fa-reply"></i> Responder
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                            
                                            <form method="POST" class="status-form">
                                                <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                                                <select name="nuevo_estado" class="status-select">
                                                    <option value="abierto" <?= $row['estado'] === 'abierto' ? 'selected' : '' ?>>Abierto</option>
                                                    <option value="cerrado" <?= $row['estado'] === 'cerrado' ? 'selected' : '' ?>>Cerrado</option>
                                                </select>
                                                <button type="submit" name="cambiar_estado" class="action-btn status-btn">
                                                    <i class="fas fa-sync-alt"></i> Cambiar
                                                </button>
                                            </form>
                                            
                                            <form method="POST" onsubmit="return confirmDelete();" class="delete-form">
                                                <input type="hidden" name="tiket_id" value="<?= $row['id'] ?>">
                                                <button type="submit" name="eliminar_tiket" class="action-btn delete-btn">
                                                    <i class="fas fa-trash-alt"></i> Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="./JS/script_admin.js"></script>
</body>
</html>

<?php
session_start();
include 'includes/db.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    echo "Debes iniciar sesión para ver tus tikets.";
    exit;
}

$user_id = $_SESSION['user_id'];

// Consulta parametrizada para obtener los tikets del usuario autenticado
$sql = "SELECT * FROM tikets WHERE user_id = $1 ORDER BY fecha_creacion DESC";
$result = pg_query_params($conn, $sql, array($user_id));

if (!$result) {
    echo "Error al consultar los tikets.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>iTiket</title>
    <link rel="icon" type="image/png" href="image/itiket_logo.png">
    <style>
        
        :root {
        --primary: #5865F2;
        --primary-dark: #404EED;
        --primary-light: rgba(88, 101, 242, 0.1);
        --secondary: #57F287;
        --danger: #ED4245;
        --warning: #FEE75C;
        --info: #EB459E;
        --text: #2C2F33;
        --text-light: #99AAB5;
        --bg: #FFFFFF;
        --bg-secondary: #F2F3F5;
        --border: #E3E5E8;
        --border-radius: 12px;
        --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.08);
        --shadow-md: 0 4px 12px rgba(0, 0, 0, 0.1);
        --transition: all 0.2s ease;
        }
        /* === Botón "Ver" Mejorado === */
        .btn-view {
        /* Estructura */
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 8px;
        
        /* Estilo */
        background: linear-gradient(135deg, var(--primary) 0%, var(--primary-dark) 100%);
        color: white;
        border: none;
        cursor: pointer;
        text-decoration: none;
        font-weight: 600;
        font-size: 14px;
        font-family: 'Inter', sans-serif;
        
        /* Efectos */
        box-shadow: 0 2px 8px rgba(88, 101, 242, 0.3);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        position: relative;
        overflow: hidden;
        }

        /* Efecto hover */
        .btn-view:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(88, 101, 242, 0.4);
        background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
        }

        /* Efecto active */
        .btn-view:active {
        transform: translateY(0);
        box-shadow: 0 2px 4px rgba(88, 101, 242, 0.4);
        }

        /* Efecto de onda al hacer clic */
        .btn-view::after {
        content: "";
        position: absolute;
        top: 50%;
        left: 50%;
        width: 5px;
        height: 5px;
        background: rgba(255, 255, 255, 0.5);
        opacity: 0;
        border-radius: 100%;
        transform: scale(1, 1) translate(-50%);
        transform-origin: 50% 50%;
        }

        .btn-view:focus:not(:active)::after {
        animation: ripple 0.6s ease-out;
        }

        /* Icono (usando pseudo-elemento) */
        .btn-view::before {
        content: "👁️";
        display: inline-block;
        font-size: 16px;
        transition: transform 0.3s ease;
        }

        .btn-view:hover::before {
        transform: scale(1.1);
        }

        /* Animación de onda */
        @keyframes ripple {
        0% {
            transform: scale(0, 0);
            opacity: 0.5;
        }
        100% {
            transform: scale(20, 20);
            opacity: 0;
        }
        }

        /* Versión pequeña para tablas */
        .tickets-table .btn-view {
        padding: 6px 12px;
        font-size: 13px;
        border-radius: 6px;
        }

        /* Estado deshabilitado */
        .btn-view:disabled {
        background: #cccccc;
        cursor: not-allowed;
        box-shadow: none;
        transform: none !important;
        }

        /* Reset y Estilos Base */
        * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        }

        body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
        line-height: 1.6;
        color: var(--text);
        background-color: var(--bg-secondary);
        padding: 20px;
        }

        /* Contenedor Principal */
        .tickets-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 0 15px;
        }

        /* Header */
        .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
        }

        .page-header h2 {
        font-size: 28px;
        font-weight: 700;
        color: var(--primary);
        display: flex;
        align-items: center;
        gap: 10px;
        }

        .page-header h2::before {
        content: "📋";
        font-size: 24px;
        }

        /* Filtros */
        .filter-controls {
        display: flex;
        align-items: center;
        gap: 12px;
        }

        .filter-controls label {
        font-weight: 500;
        color: var(--text);
        }

        .filter-controls select {
        padding: 8px 16px;
        border-radius: var(--border-radius);
        border: 1px solid var(--border);
        background: var(--bg);
        font-family: inherit;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        }

        .filter-controls select:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 2px rgba(88, 101, 242, 0.2);
        }

        /* Tabla de Tickets */
        .tickets-table-container {
        background: var(--bg);
        border-radius: var(--border-radius);
        box-shadow: var(--shadow-md);
        overflow: hidden;
        border: 1px solid var(--border);
        }

        .tickets-table {
        width: 100%;
        border-collapse: collapse;
        }

        .tickets-table th {
        background-color: var(--primary);
        color: white;
        font-weight: 600;
        text-align: left;
        padding: 16px 20px;
        position: sticky;
        top: 0;
        }

        .tickets-table td {
        padding: 16px 20px;
        border-bottom: 1px solid var(--border);
        vertical-align: middle;
        }

        .tickets-table tr:last-child td {
        border-bottom: none;
        }

        .tickets-table tr:hover {
        background-color: var(--primary-light);
        }

        /* Badges de Estado */
        .status-badge {
        display: inline-flex;
        align-items: center;
        padding: 6px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        gap: 6px;
        }

        .status-badge::before {
        content: "";
        display: inline-block;
        width: 8px;
        height: 8px;
        border-radius: 50%;
        }

        .status-badge.abierto {
        background-color: rgba(254, 231, 92, 0.2);
        color: #B38C00;
        }

        .status-badge.abierto::before {
        background-color: #B38C00;
        }

        .status-badge.cerrado {
        background-color: rgba(87, 242, 135, 0.2);
        color: #1F8B4C;
        }

        .status-badge.cerrado::before {
        background-color: #1F8B4C;
        }

        /* Botones */
        .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 8px 16px;
        border-radius: var(--border-radius);
        font-weight: 500;
        font-size: 14px;
        cursor: pointer;
        transition: var(--transition);
        text-decoration: none;
        gap: 6px;
        border: none;
        }

        .btn-view {
        background-color: var(--primary);
        color: white;
        }

        .btn-view:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
        }

        .btn-primary {
        background-color: var(--primary);
        color: white;
        }

        .btn-primary:hover {
        background-color: var(--primary-dark);
        transform: translateY(-1px);
        box-shadow: var(--shadow-sm);
        }

        /* Estado Vacío */
        .empty-state {
        text-align: center;
        padding: 60px 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 16px;
        }

        .empty-state i {
        font-size: 48px;
        opacity: 0.7;
        color: var(--text-light);
        }

        .empty-state p {
        font-size: 16px;
        color: var(--text-light);
        max-width: 400px;
        margin: 0 auto;
        }

        /* Sección de Bienvenida */
        .welcome-section {
        background: var(--bg);
        border-radius: var(--border-radius);
        padding: 30px;
        margin-bottom: 30px;
        box-shadow: var(--shadow-sm);
        border: 1px solid var(--border);
        }

        .welcome-title {
        font-size: 24px;
        font-weight: 700;
        color: var(--primary);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        }

        .checklist {
        list-style: none;
        display: flex;
        flex-direction: column;
        gap: 12px;
        }

        .checklist-item {
        padding: 16px 20px;
        background: var(--bg-secondary);
        border-radius: var(--border-radius);
        display: flex;
        align-items: center;
        transition: var(--transition);
        border-left: 4px solid transparent;
        }

        .checklist-item:hover {
        transform: translateX(5px);
        background: var(--primary-light);
        border-left-color: var(--primary);
        }

        .checklist-item a {
        color: var(--text);
        text-decoration: none;
        font-weight: 500;
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        }

        .checklist-item i {
        font-size: 20px;
        color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }
        
        .tickets-table {
            display: block;
            overflow-x: auto;
            white-space: nowrap;
        }
        
        .welcome-section {
            padding: 20px;
        }
        
        .checklist-item {
            padding: 12px 16px;
        }
        }

        /* Animaciones */
        @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
        }

        .tickets-table tbody tr {
        animation: fadeIn 0.3s ease forwards;
        opacity: 0;
        }

        .tickets-table tbody tr:nth-child(1) { animation-delay: 0.1s; }
        .tickets-table tbody tr:nth-child(2) { animation-delay: 0.2s; }
        .tickets-table tbody tr:nth-child(3) { animation-delay: 0.3s; }
        .tickets-table tbody tr:nth-child(4) { animation-delay: 0.4s; }
        .tickets-table tbody tr:nth-child(5) { animation-delay: 0.5s; }
    </style>
</head>
<body>
    <div class="welcome-section">
        <h1 class="welcome-title">Bienvenido, Imao</h1>
        
        <ul class="checklist">
            <li class="checklist-item" data-checked="true" data-icon="logout">
                <a href="menu.php">Menu</a>
            </li>
            <li class="checklist-item" data-checked="false" data-icon="tickets">
                <a href="ver_tikets.php">Ver Mis Tikets</a>
            </li>
            <li class="checklist-item" data-checked="true" data-icon="new">
                <a href="nuevo_tiket.php">Crear Nuevo Tiket</a>
            </li>
            <li class="checklist-item" data-checked="true" data-icon="home">
                <a href="index.php">Volver a Inicio</a>
            </li>
            <li class="checklist-item" data-checked="true" data-icon="logout">
                <a href="logout.php">Cerrar Sesión</a>
            </li>
        </ul>

    <div class="tickets-container">
        <div class="page-header">
            <div class="checklist-divider"></div>
            <h2>Mis Tikets</h2>
            <div>
                <label for="filter-status">Filtrar por estado:</label>
                <select id="filter-status">
                    <option value="all">Todos</option>
                    <option value="abierto">Abiertos</option>
                    <option value="cerrado">Cerrados</option>
                </select>
            </div>
        </div>

        <div class="tickets-table-container">
            <?php if (pg_num_rows($result) > 0): ?>
                <table class="tickets-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Título</th>
                            <th>Estado</th>
                            <th>Fecha de Creación</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = pg_fetch_assoc($result)): ?>
                            <tr class="ticket-row <?= htmlspecialchars($row['estado']) ?>">
                                <td><?= htmlspecialchars($row['id']) ?></td>
                                <td><?= htmlspecialchars($row['titulo']) ?></td>
                                <td>
                                    <span class="status-badge <?= htmlspecialchars($row['estado']) ?>">
                                        <?= htmlspecialchars($row['estado']) ?>
                                    </span>
                                </td>
                                <td><?= htmlspecialchars($row['fecha_creacion']) ?></td>
                                <td class="actions">
                                    <a href="ver_tiket.php?id=<?= $row['id'] ?>" class="btn-view">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <i>📭</i>
                    <p>No tienes tikets creados todavía.</p>
                    <a href="nuevo_tiket.php" class="btn">Crear mi primer tiket</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Confirmación antes de acciones importantes
            const deleteLinks = document.querySelectorAll('.delete-tiket');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('¿Estás seguro de querer eliminar este tiket?')) {
                        e.preventDefault();
                    }
                });
            });

            // Filtrado de tikets por estado
            const filterSelect = document.getElementById('filter-status');
            if (filterSelect) {
                filterSelect.addEventListener('change', function() {
                    const status = this.value;
                    const rows = document.querySelectorAll('.ticket-row');
                    
                    rows.forEach(row => {
                        if (status === 'all' || row.classList.contains(status)) {
                            row.style.display = '';
                        } else {
                            row.style.display = 'none';
                        }
                    });
                });
            }

            // Animación al pasar el ratón sobre filas
            const ticketRows = document.querySelectorAll('.ticket-row');
            ticketRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateX(5px)';
                    this.style.transition = 'transform 0.2s ease';
                });
                
                row.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                });
            });
        });
    </script>

    <?php pg_close($conn); ?>
</body>
</html>
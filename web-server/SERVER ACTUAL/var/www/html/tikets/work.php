<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.php");
    exit();
}

require 'conexion.php';

$usuario_id = $_SESSION['usuario_id'];

$sql = "SELECT id, titulo, estado, fecha_creacion FROM tikets WHERE usuario_id = $1 AND estado != 'Cerrado' ORDER BY fecha_creacion DESC";
$resultado = pg_query_params($conexion, $sql, [$usuario_id]);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tikets en curso | iTiket</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<header>
    <h1>iTiket</h1>
    <nav>
        <a href="menu.php">Menú</a>
        <a href="nuevo_tiket.php">Nuevo Tiket</a>
        <a href="logout.php">Cerrar sesión</a>
    </nav>
</header>

<main>
    <h2>Tikets en Curso</h2>

    <?php if (pg_num_rows($resultado) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Título</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($fila = pg_fetch_assoc($resultado)): ?>
                    <tr>
                        <td><?= htmlspecialchars($fila['id']) ?></td>
                        <td><?= htmlspecialchars($fila['titulo']) ?></td>
                        <td><?= htmlspecialchars($fila['estado']) ?></td>
                        <td><?= htmlspecialchars($fila['fecha_creacion']) ?></td>
                        <td>
                            <a href="ver_tiket.php?id=<?= $fila['id'] ?>">Ver</a> |
                            <a href="update.html?id=<?= $fila['id'] ?>">Actualizar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No tienes tikets en curso.</p>
    <?php endif; ?>

</main>

<footer>
    <p>&copy; 2025 iTiket. Todos los derechos reservados.</p>
</footer>
</body>
</html>

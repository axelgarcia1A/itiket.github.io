<?php
session_start();
include 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    echo "<div class='error-message'>Debes iniciar sesión para crear un tiket.</div>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $titulo = pg_escape_string($conn, $_POST['titulo']);
    $descripcion = pg_escape_string($conn, $_POST['descripcion']);
    $user_id = $_SESSION['user_id'];

    $sql = "INSERT INTO tikets (titulo, descripcion, user_id) VALUES ($1, $2, $3)";
    $result = pg_query_params($conn, $sql, [$titulo, $descripcion, $user_id]);
    
    if ($result) {
        $success_message = "Tiket creado con éxito.";
        // Limpiar los campos del formulario
        $_POST['titulo'] = '';
        $_POST['descripcion'] = '';
    } else {
        $error_message = "Error al crear el tiket: " . pg_last_error($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>iTiket</title>
    <link rel="stylesheet" type="text/css" href="./../style/style_bg.css"/>
    <link rel="icon" type="image/png" href="./../image/itiket_logo.png">
    <style>
        /* Estilos consistentes con la página de precios */
        .ticket-section {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .ticket-section h1 {
            font-size: 2.5rem;
            color: var(--primary);
            margin-bottom: 0.5rem;
            font-family: 'Coolvetica', Arial, sans-serif;
            text-align: center;
        }

        .ticket-section .subtitle {
            font-size: 1.1rem;
            color: var(--text-color);
            margin-bottom: 2rem;
            opacity: 0.8;
            text-align: center;
        }

        .ticket-form-container {
            background: var(--bg-color);
            border-radius: var(--border-radius);
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 121, 216, 0.2);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: var(--border-radius);
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            border-color: var(--primary);
            outline: none;
            box-shadow: 0 0 0 2px rgba(0, 121, 216, 0.2);
        }

        .form-group textarea {
            min-height: 200px;
            resize: vertical;
        }

        .char-counter {
            text-align: right;
            font-size: 0.8rem;
            color: var(--text-color);
            opacity: 0.7;
            margin-top: 0.3rem;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .button {
            padding: 0;
            border-radius: var(--border-radius);
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
        }

        .button.primary {
            background: var(--primary);
            color: white;
        }

        .button.primary:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .button.secondary {
            background: transparent;
            color: var(--primary);
            border: 1px solid var(--primary);
        }

        .button.secondary:hover {
            background: rgba(0, 121, 216, 0.1);
        }

        .error-message,
        .success-message {
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: var(--border-radius);
            font-weight: 500;
        }

        .error-message {
            background-color: #fdecea;
            color: #d32f2f;
            border-left: 4px solid #f44336;
        }

        .success-message {
            background-color: #e8f5e9;
            color: #388e3c;
            border-left: 4px solid #4caf50;
        }

        /* Estilos para la barra de navegación */
        .ticket-nav {
            display: flex;
            justify-content: center;
            gap: 1.5rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }

        .ticket-nav a {
            color: var(--text-color);
            text-decoration: none;
            font-weight: 500;
            padding: 0.5rem 1rem;
            border-radius: var(--border-radius);
            transition: all 0.3s ease;
        }

        .ticket-nav a:hover {
            color: var(--primary);
            background: rgba(0, 121, 216, 0.1);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
            }
            
            .button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="ticket-section">
        <h1>Crear Nuevo Tiket</h1>
        <p class="subtitle">Describe detalladamente tu consulta o problema</p>

        <div class="ticket-nav">
            <a href="menu.php">🏠 Menú</a>
            <a href="ver_tikets.php">📋 Ver Tikets</a>
            <a href="nuevo_tiket.php">📝 Crear Tiket</a>
            <a href="menu.php#notificaciones">🔔 Notificaciones</a>
            <a href="logout.php">🔒 Cerrar sesión</a>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="error-message"><?= $error_message ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="success-message"><?= $success_message ?></div>
        <?php endif; ?>

        <div class="ticket-form-container">
            <form action="nuevo_tiket.php" method="POST" class="ticket-form">
                <div class="form-group">
                    <label for="titulo">Título del tiket</label>
                    <input type="text" id="titulo" name="titulo" required 
                           placeholder="Ejemplo: Problema con el inicio de sesión"
                           value="<?= isset($_POST['titulo']) ? htmlspecialchars($_POST['titulo']) : '' ?>">
                </div>
                
                <div class="form-group">
                    <label for="descripcion">Descripción detallada</label>
                    <textarea id="descripcion" name="descripcion" required
                              placeholder="Describe el problema o solicitud con todos los detalles necesarios..."><?= isset($_POST['descripcion']) ? htmlspecialchars($_POST['descripcion']) : '' ?></textarea>
                    <div class="char-counter">
                        Caracteres: <span id="char-count">0</span>/1000
                    </div>
                </div>
                
                <div class="form-actions">
                    <button type="submit" class="button primary">
                        Crear Tiket
                    </button>
                    <a href="ver_tikets.php" class="button secondary">
                        Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Contador de caracteres para la descripción
            const descripcion = document.getElementById('descripcion');
            const charCount = document.getElementById('char-count');
            
            if (descripcion && charCount) {
                // Inicializar contador
                charCount.textContent = descripcion.value.length;
                
                descripcion.addEventListener('input', function() {
                    const currentLength = this.value.length;
                    charCount.textContent = currentLength;
                    
                    // Cambiar color si se acerca al límite
                    if (currentLength > 900) {
                        charCount.style.color = '#e74c3c';
                    } else if (currentLength > 700) {
                        charCount.style.color = '#f39c12';
                    } else {
                        charCount.style.color = '';
                    }
                });
            }
            
            // Validación de formulario antes de enviar
            const form = document.querySelector('.ticket-form');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const titulo = this.elements['titulo'].value.trim();
                    const descripcion = this.elements['descripcion'].value.trim();
                    
                    if (titulo.length < 5) {
                        alert('El título debe tener al menos 5 caracteres');
                        e.preventDefault();
                        return;
                    }
                    
                    if (descripcion.length < 20) {
                        alert('La descripción debe tener al menos 20 caracteres');
                        e.preventDefault();
                        return;
                    }
                    
                    // Mostrar spinner de carga
                    const submitBtn = this.querySelector('button[type="submit"]');
                    if (submitBtn) {
                        submitBtn.innerHTML = 'Enviando...';
                        submitBtn.disabled = true;
                    }
                });
            }
            
            // Confirmación para cerrar sesión
            document.querySelector('a[href="logout.php"]')?.addEventListener('click', function(e) {
                if (!confirm('¿Estás seguro de que deseas cerrar sesión?')) {
                    e.preventDefault();
                }
            });
        });
    </script>
</body>
</html>
<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'conexion_bd.php';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare('SELECT id, usuario, email FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION = array();
        session_destroy();
        header('Location: login.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('Error al obtener datos del usuario: ' . $e->getMessage());
    $error = "Ha ocurrido un error al cargar tu perfil.";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="manga_verso.css">
    <title>Mi Perfil - Manga_verso</title>
</head>
<body>
    <header>
        <div>
            <h1>Manga_verso</h1>
            <p>Tu portal de manga</p>
        </div>
        <div class="auth-logged">
            <a class="user-link"><?php echo htmlspecialchars($usuario['usuario']); ?></a>
            <a class="logout-btn" href="logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="profile-container">
        <div class="profile-card">
            <h2>Mi Perfil</h2>

            <?php if (isset($error)): ?>
                <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <div class="profile-info">
                <div class="info-group">
                    <label>Usuario</label>
                    <p><?php echo htmlspecialchars($usuario['usuario']); ?></p>
                </div>

                <div class="info-group">
                    <label>Correo</label>
                    <p><?php echo htmlspecialchars($usuario['email']); ?></p>
                </div>
            </div>

            <div class="profile-actions">
                <a class="btn-primary" href="index.php">Volver al Inicio</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; 2025 Manga_verso</p>
    </footer>
</body>
</html>
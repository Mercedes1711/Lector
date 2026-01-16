<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header('Location: ../public/login.php');
    exit();
}

require_once __DIR__ . '/../src/conexion_bd.php';

// Obtener datos del usuario
try {
    $stmt = $conn->prepare('SELECT id, usuario, email FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        $_SESSION = array();
        session_destroy();
        header('Location: ../public/login.php');
        exit();
    }
} catch (PDOException $e) {
    error_log('Error al obtener datos del usuario: ' . $e->getMessage());
    $error = "Ha ocurrido un error al cargar tu perfil.";
}

// Manejo de cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    try {
        // Obtener la contraseña actual desde la base de datos
        $stmt = $conn->prepare('SELECT contraseña FROM usuarios WHERE id = ?');
        $stmt->execute([$_SESSION['user_id']]);
        $user_data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user_data) {
            $error = "Usuario no encontrado.";
        } elseif (!password_verify($current_password, $user_data['contraseña'])) {
            $error = "La contraseña actual es incorrecta.";
        } elseif ($new_password !== $confirm_password) {
            $error = "La nueva contraseña y la confirmación no coinciden.";
        } elseif (strlen($new_password) < 6) {
            $error = "La nueva contraseña debe tener al menos 6 caracteres.";
        } else {
            // Actualizar la contraseña
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE usuarios SET contraseña = ? WHERE id = ?');
            $stmt->execute([$hashed_password, $_SESSION['user_id']]);
            $success = "Contraseña actualizada correctamente.";
        }
    } catch (PDOException $e) {
        error_log('Error al actualizar contraseña: ' . $e->getMessage());
        $error = "No se pudo actualizar la contraseña.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../public/manga_verso.css">
    <title>Mi Perfil - Manga_verso</title>
</head>
<body>
    <header>
        <div>
            <h1>Manga_verso</h1>
            <p>Tu portal de manga</p>
        </div>
        <div class="auth-logged">
            <a class="user-link" href="perfil.php"><?php echo htmlspecialchars($usuario['usuario']); ?></a>
            <a class="logout-btn" href="../public/logout.php">Cerrar sesión</a>
        </div>
    </header>

    <main class="profile-container">
        <div class="profile-card">
            <h2>Mi Perfil</h2>

            <?php if (isset($error)): ?>
                <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
                <div class="form-success"><?php echo htmlspecialchars($success); ?></div>
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
                <a class="btn-primary" href="../public/index.php">Volver al Inicio</a>
            </div>

            <hr>

            <div class="password-change">
                <h3>Cambiar Contraseña</h3>
                <form method="POST">
                    <div class="info-group">
                        <label>Contraseña actual</label>
                        <input type="password" name="current_password" required>
                    </div>
                    <div class="info-group">
                        <label>Nueva contraseña</label>
                        <input type="password" name="new_password" required>
                    </div>
                    <div class="info-group">
                        <label>Confirmar nueva contraseña</label>
                        <input type="password" name="confirm_password" required>
                    </div>
                    <button class="btn-primary" type="submit" name="cambiar_password">Actualizar Contraseña</button>
                </form>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; <?php echo date("Y"); ?> Manga_verso</p>
    </footer>
</body>
</html>

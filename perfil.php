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
    <title>Mi Perfil</title>
</head>
<body>
    <h1>Mi Perfil</h1>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <div>
        <p>Usuario: <?php echo htmlspecialchars($usuario['usuario']); ?></p>
        <p>Email: <?php echo htmlspecialchars($usuario['email']); ?></p>
    </div>

    <p>
        <a href="index.html">Volver al Inicio</a>
    </p>
</body>
</html>
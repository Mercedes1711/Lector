<?php
session_start();
include __DIR__ . '/../src/conexion_bd.php'; // Conexión PDO

$message = '';
$show_form = false;

// Comprobar si existe el token en la URL
$token = $_GET['token'] ?? '';

if (!$token) {
    $message = "Token inválido.";
} else {
    // Buscar token válido en password_resets
    $stmt = $conn->prepare("
        SELECT pr.id AS reset_id, pr.user_id, pr.expires_at, u.usuario
        FROM password_resets pr
        JOIN usuarios u ON u.id = pr.user_id
        WHERE pr.token = :token
    ");
    $stmt->bindParam(':token', $token);
    $stmt->execute();
    $reset = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reset) {
        $message = "Token inválido o ya utilizado.";
    } elseif (strtotime($reset['expires_at']) < time()) {
        $message = "El token ha expirado.";
    } else {
        $show_form = true; // Mostrar formulario para nueva contraseña
    }
}

// Procesar formulario al enviar nueva contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $show_form) {
    $password = $_POST['password'] ?? '';
    $password_confirm = $_POST['password_confirm'] ?? '';

    if (empty($password) || empty($password_confirm)) {
        $message = "Todos los campos son obligatorios.";
    } elseif ($password !== $password_confirm) {
        $message = "Las contraseñas no coinciden.";
    } else {
        // Hashear la nueva contraseña
        $password_hashed = password_hash($password, PASSWORD_DEFAULT);

        // Actualizar la contraseña en usuarios
        $stmt = $conn->prepare("UPDATE usuarios SET contraseña = :password WHERE id = :user_id");
        $stmt->bindParam(':password', $password_hashed);
        $stmt->bindParam(':user_id', $reset['user_id']);
        $stmt->execute();

        // Borrar el token para que no se pueda reutilizar
        $stmt = $conn->prepare("DELETE FROM password_resets WHERE id = :reset_id");
        $stmt->bindParam(':reset_id', $reset['reset_id']);
        $stmt->execute();

        // Redirigir al login después de restablecer contraseña
        header("Location: login.php");
        exit; // Muy importante para que no se ejecute más código
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Restablecer Contraseña</title>
<link rel="stylesheet" href="css/css/manga_verso.css">
</head>
<body>
<div class="container">
<h2>Restablecer Contraseña</h2>
<?php if ($message): ?>
    <div class="message <?= $show_form ? '' : 'success' ?>"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>

<?php if ($show_form): ?>
<form method="POST" action="">
    <input type="password" name="password" placeholder="Nueva contraseña" required>
    <input type="password" name="password_confirm" placeholder="Repetir contraseña" required>
    <input type="submit" value="Actualizar contraseña">
</form>
<?php endif; ?>
</div>
</body>
</html>

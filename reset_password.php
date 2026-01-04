<?php
session_start();
include 'conexion_bd.php'; // Conexión PDO

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
<style>
body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; background:#f0f0f0; }
.container { background:white; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); width:350px; text-align:center; }
input[type=password] { width:100%; padding:10px; margin:10px 0; border-radius:5px; border:1px solid #ccc; }
input[type=submit] { padding:10px 20px; border:none; border-radius:5px; background:#28a745; color:white; cursor:pointer; }
input[type=submit]:hover { background:#218838; }
.message { margin:15px 0; color:red; }
.success { color:green; }
</style>
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

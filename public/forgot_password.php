<?php
session_start();
include __DIR__ . '/../src/conexion_bd.php'; // Tu conexión PDO
include __DIR__ . '/../src/correo.php';       // Tu función enviarCorreo()

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);

    if (!$email) {
        $message = "Por favor, introduce un correo válido.";
    } else {
        // Buscar usuario en la tabla 'usuarios'
        $stmt = $conn->prepare("SELECT id, usuario, email FROM usuarios WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Crear tabla password_resets si no existe
            $conn->exec("
                CREATE TABLE IF NOT EXISTS password_resets (
                    id INT AUTO_INCREMENT PRIMARY KEY,
                    user_id INT NOT NULL,
                    token VARCHAR(100) NOT NULL,
                    expires_at DATETIME NOT NULL,
                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE CASCADE
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
            ");

            // Generar token seguro
            $token = bin2hex(random_bytes(50));
            $expiry = date("Y-m-d H:i:s", strtotime("+1 hour"));

            // Guardar token
            $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (:user_id, :token, :expires_at)");
            $stmt->bindParam(':user_id', $user['id']);
            $stmt->bindParam(':token', $token);
            $stmt->bindParam(':expires_at', $expiry);
            $stmt->execute();

            // Preparar correo
            $reset_link = "http://localhost/dashboard/Lector/public/reset_password.php?token=" . $token;
            $asunto = "Restablece tu contraseña";
            $cuerpoHTML = "
                <p>Hola {$user['usuario']},</p>
                <p>Haz clic en este enlace para restablecer tu contraseña:</p>
                <p><a href='{$reset_link}'>Restablecer contraseña</a></p>
                <p>Este enlace expirará en 1 hora.</p>
            ";

            // Enviar correo solo si $user existe
            $resultado = enviarCorreo($user['email'], $user['usuario'], $asunto, $cuerpoHTML);

            // Registrar error si falla PHPMailer
            if (!$resultado['exito']) {
                error_log("Error PHPMailer: " . ($resultado['error_detalle'] ?? 'No disponible'));
            }

            // Redirigir automáticamente al login después de enviar enlace
            header("Location: login.php");
            exit;
        }

        // Mensaje genérico por seguridad (solo se mostraría si el correo no existe)
        $message = "Si tu correo está registrado, recibirás un enlace para restablecer la contraseña.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Recuperar Contraseña</title>
<style>
body { font-family: Arial; display:flex; justify-content:center; align-items:center; height:100vh; background:#f0f0f0; }
.container { background:white; padding:30px; border-radius:10px; box-shadow:0 0 15px rgba(0,0,0,0.1); width:350px; text-align:center; }
input[type=email] { width:100%; padding:10px; margin:15px 0; border-radius:5px; border:1px solid #ccc; }
input[type=submit] { padding:10px 20px; border:none; border-radius:5px; background:#007BFF; color:white; cursor:pointer; }
input[type=submit]:hover { background:#0056b3; }
.message { margin:15px 0; color:green; }
</style>
</head>
<body>
<div class="container">
<h2>Recuperar Contraseña</h2>
<?php if ($message !== ''): ?>
    <div class="message"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<form method="POST" action="">
    <input type="email" name="email" placeholder="Tu correo" required>
    <input type="submit" value="Enviar enlace">
</form>
</div>
</body>
</html>

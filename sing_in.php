<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>sing_in</title>
</head>
<body>
  <?php
include 'conexion_bd.php'; 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];
    $correo = $_POST['correo'];

    $hash = password_hash($contraseña, PASSWORD_DEFAULT);

    // 🔹 INSERTAR nuevo usuario
    $sql = "INSERT INTO usuarios (usuario, contraseña, email) VALUES (?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario, $hash, $correo]);

    // 🔹 Verificar contraseña (igual que tu código original)
    $sql = "SELECT contraseña FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario]);
    $resultado = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($resultado) {
        if (password_verify($contraseña, $resultado['contraseña'])) {
            // Incluir la funcionalidad de envío de correo y enviar antes de redirigir
            include 'correo.php';

            $asunto = 'Bienvenido a Mi Sitio';
            $cuerpo = '<p>Hola ' . htmlspecialchars($usuario) . ',</p>'
                    . '<p>Gracias por registrarte en nuestro sitio. Tu cuenta ha sido creada correctamente.</p>'
                    . '<p>Tu usuario es: <strong>' . htmlspecialchars($usuario) . '</strong></p>';

            // Intentar enviar el correo y registrar el resultado
            try {
                $resultadoCorreo = enviarCorreo($correo, $usuario, $asunto, $cuerpo);
                if (!(isset($resultadoCorreo['exito']) && $resultadoCorreo['exito'])) {
                    error_log('Fallo envío correo de bienvenida: ' . json_encode($resultadoCorreo));
                }
            } catch (Throwable $e) {
                error_log('Excepción al enviar correo de bienvenida: ' . $e->getMessage());
            }

            // Redirigir al usuario después de intentar enviar el correo
            header("Location: index.html");
            exit;
        }
    }
}



 ?>
  <form action= <?php echo htmlspecialchars($_SERVER['PHP_SELF']);?> method= "POST">
    <label>usuario</label>
    <input name= "usuario" type="text">
    <label>contraseña</label>
    <input name= "contraseña" type="password">
    <label>correo</label>
    <input name = "correo" type="text">
    <input type="submit">
</form>
</body>
</html>
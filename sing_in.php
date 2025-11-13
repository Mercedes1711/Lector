<?php
session_start();
include 'conexion_bd.php';

$error = '';

if (!empty($_SESSION['usuario'])) {
    header('Location: perfil.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';
    $correo = trim($_POST['correo'] ?? '');

    if ($usuario === '' || $contraseña === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = 'Rellena todos los campos correctamente.';
    } else {
        // Comprobar si el usuario ya existe
        $sql = "SELECT id FROM usuarios WHERE usuario = ? OR email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario, $correo]);
        if ($stmt->fetch(PDO::FETCH_ASSOC)) {
            $error = 'El usuario o correo ya está en uso.';
        } else {
            $hash = password_hash($contraseña, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (usuario, contraseña, email) VALUES (?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $ok = $stmt->execute([$usuario, $hash, $correo]);

            if ($ok) {
                $id = $conn->lastInsertId();
                $_SESSION['user_id'] = $id;
                $_SESSION['usuario'] = $usuario;
                $_SESSION['email'] = $correo;

                // Enviar correo de bienvenida si existe la función
                if (file_exists(__DIR__ . '/correo.php')) {
                    include_once __DIR__ . '/correo.php';
                    $asunto = 'Bienvenido a Manga_verso';
                    $cuerpo = '<p>Hola ' . htmlspecialchars($usuario) . ',</p>'
                           . '<p>Gracias por registrarte en Manga_verso. Tu cuenta ha sido creada correctamente.</p>';
                    try {
                        if (function_exists('enviarCorreo')) {
                            enviarCorreo($correo, $usuario, $asunto, $cuerpo);
                        }
                    } catch (Throwable $e) {
                        error_log('Error enviando correo: ' . $e->getMessage());
                    }
                }

                header('Location: index.php');
                exit;
            } else {
                $error = 'No se pudo crear la cuenta. Intenta de nuevo.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Registrarse - Manga_verso</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="manga_verso.css">
</head>
<body>
<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div></div>
</header>

<main class="login-container">
    <div class="login-card">
        <h2>Crear cuenta</h2>

        <?php if (!empty($error)): ?>
            <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="login-form" novalidate>
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" type="text" required autocomplete="username" value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="correo">Correo</label>
                <input id="correo" name="correo" type="email" required autocomplete="email" value="<?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <input id="contraseña" name="contraseña" type="password" required autocomplete="new-password">
            </div>

            <button type="submit" class="login-btn">Registrarse</button>

            <div class="form-footer">
                <a class="small-link" href="login.php">¿Ya tienes cuenta? Inicia sesión</a>
            </div>
        </form>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>
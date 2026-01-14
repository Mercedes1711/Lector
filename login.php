<?php
session_start();
include 'conexion_bd.php'; // conexión PDO

$error = '';

if (!isset($_SESSION['login_fails'])) {
    $_SESSION['login_fails'] = 0;
}

if (!empty($_SESSION['usuario'])) {
    header('Location: perfil.php');
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = trim($_POST['usuario'] ?? '');
    $contraseña = $_POST['contraseña'] ?? '';

    if ($usuario === '' || $contraseña === '') {
        $error = 'Rellena todos los campos.';
    } else {
        $sql = "SELECT id, usuario, contraseña, email FROM usuarios WHERE usuario = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$usuario]);
        $fila = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($fila && password_verify($contraseña, $fila['contraseña'])) {
            $_SESSION['user_id'] = $fila['id'];
            $_SESSION['usuario'] = $fila['usuario'];
            $_SESSION['email'] = $fila['email'];

            $_SESSION['login_fails'] = 0;

            header("Location: index.php");
            exit;
        } else {

            $_SESSION['login_fails']++;

            $error = 'Usuario o contraseña incorrectos.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="manga_verso.css">
    <title>Login - Manga_verso</title>
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
        <h2>Iniciar sesión</h2>

        <?php if ($error): ?>
            <div class="form-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="POST" class="login-form" novalidate>
            <div class="form-group">
                <label for="usuario">Usuario</label>
                <input id="usuario" name="usuario" type="text" required autocomplete="username" value="<?php echo isset($usuario) ? htmlspecialchars($usuario) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="contraseña">Contraseña</label>
                <input id="contraseña" name="contraseña" type="password" required autocomplete="current-password">
            </div>

            <button type="submit" class="login-btn">Entrar</button>

            <?php if ($_SESSION['login_fails'] >= 3): ?>
                <div class="form-footer">
                    <a class="small-link" href="forgot_password.php">
                        ¿Has olvidado tu contraseña?
                    </a>
                </div>
            <?php endif; ?>

            <div class="form-footer">
                <a class="small-link" href="crear_cuenta.php">¿No tienes cuenta? Regístrate</a>
            </div>
        </form>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>

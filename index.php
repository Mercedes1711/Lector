<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta name="autor" content="Adrian Arenas vega y Mercedes Lizcano Mora">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="manga_verso.css">
    <title>Manga_verso</title>
</head>
<body>
   <header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>

    <?php if (!empty($_SESSION['usuario'])): ?>
        <div class="auth-logged">
            <a class="user-link" href="perfil.php"><?php echo htmlspecialchars($_SESSION['usuario']); ?></a>
            <a class="logout-btn" href="logout.php">Cerrar sesión</a>
        </div>
    <?php else: ?>
        <div class="auth-buttons">
            <a class="login-btn" href="login.php">Login</a>
        </div>
    <?php endif; ?>
   </header>
    
    <main>
        <h1>Bienvenido a Manga_verso</h1>
        <p>Explora y disfruta de tus mangas favoritos. Regístrate o inicia sesión para acceder a más funciones.</p>
    </main>
    
    <footer>
        <p>&copy; 2025 Manga_verso - Creado por Adrian Arenas Vega y Mercedes Lizcano Mora</p>
    </footer>
</body>
</html>
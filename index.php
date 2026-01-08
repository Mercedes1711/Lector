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
    <p>Explora y disfruta de tus mangas favoritos.</p>

    <?php if (!empty($_SESSION['usuario'])): ?>
        <div style="margin:20px 0; text-align:center;">
            <a class="btn-primary" href="subirManga.php">📚 Subir manga</a>
        </div>
    <?php endif; ?>

    <h2>Mangas disponibles</h2>

    <div class="mangas-container">
        <?php
        $carpetaBase = "Manga/";
        $archivos = glob($carpetaBase . "manga_*.txt");

        if ($archivos) {
            foreach ($archivos as $archivo) {
                $contenido = file_get_contents($archivo);
                list($titulo, $descripcion, $portada, $manga) = explode("|", $contenido);
                ?>
                <div class="manga-card">
                    <img src="<?php echo $portada; ?>" alt="Portada del manga">
                    <h3><?php echo htmlspecialchars($titulo); ?></h3>
                    <p><?php echo htmlspecialchars($descripcion); ?></p>
                    <a href="<?php echo $manga; ?>" target="_blank">📖 Ver manga</a>
                </div>
                <?php
            }
        } else {
            echo "<p>No hay mangas subidos todavía.</p>";
        }
        ?>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso - Creado por Adrian Arenas Vega y Mercedes Lizcano Mora</p>
</footer>

</body>
</html>

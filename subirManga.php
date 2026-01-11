<?php
session_start();
if (empty($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subir Manga | Manga_verso</title>
<link rel="stylesheet" href="manga_verso.css">
</head>
<body>

<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div class="auth-logged">
        <a href="perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="logout.php" class="logout-btn">Cerrar sesión</a>
    </div>
</header>

<main>
    <h1>Subir nuevo manga</h1>

    <div class="form-container">
        <form action="procesar_manga.php" method="POST" enctype="multipart/form-data" class="upload-form">
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" id="titulo" required>

            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="5" required></textarea>

            <label for="portada">Portada (JPG/PNG):</label>
            <input type="file" name="portada" id="portada" accept="image/*" required>

            <button type="submit" class="btn-primary">Subir manga</button>
        </form>
    </div>

    <br>
    <a href="index.php" class="btn-primary">⬅ Volver al inicio</a>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>

</body>
</html>

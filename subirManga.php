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
</head>
<body>

<h1>Subir nuevo manga</h1>

<form action="procesar_manga.php" method="POST" enctype="multipart/form-data">

    <label for="titulo">Título del manga:</label><br>
    <input type="text" name="titulo" id="titulo" required><br><br>

    <label for="descripcion">Descripción:</label><br>
    <textarea name="descripcion" id="descripcion" rows="5" required></textarea><br><br>

    <label for="portada">Portada (JPG / PNG):</label><br>
    <input type="file" name="portada" id="portada" accept="image/*" required><br><br>

    <label for="archivo">Archivo del manga (PDF o ZIP):</label><br>
    <input type="file" name="archivo" id="archivo" accept=".pdf,.zip" required><br><br>

    <button type="submit">Subir manga</button>

</form>

<br>
<a href="index.php">⬅ Volver al inicio</a>

</body>
</html>

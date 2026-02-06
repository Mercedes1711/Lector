<?php
session_start();
if (empty($_SESSION['usuario'])) {
    header("Location: ../public/login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Subir Manga | Manga_verso</title>
<link rel="stylesheet" href="../css/css/manga_verso.css">

</head>
<body>

<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div class="auth-logged">
        <a href="perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="../public/logout.php" class="logout-btn">Cerrar sesión</a>
    </div>
</header>

<main>
    <h1>Subir nuevo manga</h1>

    <?php if (isset($_SESSION['error'])): ?>
        <div style="color: red; margin-bottom: 20px; border: 1px solid red; padding: 10px; background-color: #ffe6e6;">
            <?= htmlspecialchars($_SESSION['error']); ?>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <div class="form-container">
        <form action="procesar_manga.php" method="POST" enctype="multipart/form-data" class="upload-form">
            <label for="titulo">Título:</label>
            <input type="text" name="titulo" id="titulo" required>

            <label for="descripcion">Descripción:</label>
            <textarea name="descripcion" id="descripcion" rows="5" required></textarea>

            <label for="categoria">Categoría:</label>
            <select name="categoria_id" id="categoria" required>
                <option value="">Selecciona una categoría</option>
                <?php
                require __DIR__ . "/../src/conexion_bd.php";
                $stmt = $conn->query("SELECT id, nombre FROM categorias ORDER BY nombre");
                while ($cat = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    echo "<option value='{$cat['id']}'>{$cat['nombre']}</option>";
                }
                ?>
            </select>

            <label style="display: flex; align-items: center; gap: 10px; margin-top: 15px;">
                <input type="checkbox" name="es_original" id="es_original" value="1" style="width: auto;">
                <span>✨ Este manga es de mi creación original</span>
            </label>

            <label for="portada">Portada (JPG/PNG):</label>
            <input type="file" name="portada" id="portada" accept="image/*" required>

            <button type="submit" class="btn-primary">Subir manga</button>
        </form>
    </div>

    <br>
    <a href="../public/index.php" class="btn-primary">⬅ Volver al inicio</a>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>

</body>
</html>

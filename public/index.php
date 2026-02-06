<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";
$usuario_id = $_SESSION['user_id']; 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="../css/css/manga_verso.css">
    <title>Manga_verso - Inicio</title>
</head>
<body>
<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div class="auth-logged">
        <a href="../pages/perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</header>

<main>
    <h1>Bienvenido a Manga_verso</h1>
    <p style="text-align: center; color: #666; font-size: 16px; margin-bottom: 30px;">
        Selecciona una opción para continuar
    </p>
    
    <div class="menu-container">
        <a href="../pages/biblioteca.php" class="menu-card cyan">
            <div class="menu-card-icon">📚</div>
            <div class="menu-card-title">Mi Biblioteca</div>
            <div class="menu-card-desc">Tus mangas y tu colección personal</div>
        </a>
        
        <a href="../pages/mangas_compartidos.php" class="menu-card verde">
            <div class="menu-card-icon">📢</div>
            <div class="menu-card-title">Mangas Originales</div>
            <div class="menu-card-desc">Descubre mangas originales de otros creadores</div>
        </a>
        
        <a href="../pages/subirManga.php" class="menu-card azul">
            <div class="menu-card-icon">⬆️</div>
            <div class="menu-card-title">Subir Nuevo Manga</div>
            <div class="menu-card-desc">Comparte tu creación con la comunidad</div>
        </a>
    </div>

</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>


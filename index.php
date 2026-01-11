<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . "/conexion_bd.php";
$usuario_id = $_SESSION['user_id']; // <-- corregido para coincidir con login.php
?>

<!DOCTYPE html>
<html lang="es">
<head>
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
    <div class="auth-logged">
        <a href="perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="logout.php">Cerrar sesión</a>
    </div>
</header>

<main>
    <h1>Bienvenido a Manga_verso</h1>
    <div style="text-align:center; margin:20px 0;">
        <a class="btn-primary" href="subirManga.php">📚 Subir manga</a>
    </div>

    <h2>Mis mangas</h2>
    <div class="mangas-container">
        <?php
        $stmt = $conn->prepare("SELECT * FROM mangas WHERE usuario_id = ? ORDER BY fecha_subida DESC");
        $stmt->execute([$usuario_id]);
        $mangas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($mangas) {
            foreach ($mangas as $row): ?>
                <div class="manga-card">
                    <img src="<?= $row['portada']; ?>" alt="Portada del manga">
                    <h3><?= htmlspecialchars($row['titulo']); ?></h3>
                    <p><?= htmlspecialchars($row['descripcion']); ?></p>

                    <a href="capitulos.php?manga=<?= $row['id']; ?>">📖 Ver manga</a>

                    <form action="eliminar_manga.php" method="POST" style="margin-top:10px;">
                        <input type="hidden" name="manga_id" value="<?= $row['id']; ?>">
                        <button type="submit" class="btn-primary" style="background-color:#dc3545; border:none;">
                            🗑️ Eliminar manga
                        </button>
                    </form>
                </div>
            <?php endforeach;
        } else {
            echo "<p>No tienes mangas subidos todavía.</p>";
        }
        ?>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso - Creado por Adrian Arenas Vega y Mercedes Lizcano Mora</p>
</footer>
</body>
</html>

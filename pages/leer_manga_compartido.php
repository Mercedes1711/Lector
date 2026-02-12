<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Validar que venga el ID del manga
if (empty($_GET['manga']) || !is_numeric($_GET['manga'])) {
    die("Manga inválido. Ve a <a href='../public/index.php'>inicio</a>");
}
$manga_id = (int)$_GET['manga'];

// Obtener el manga compartido (no puede ser del usuario actual)
$stmt = $conn->prepare("
    SELECT m.*, u.usuario as autor
    FROM mangas m
    JOIN usuarios u ON m.usuario_id = u.id
    JOIN mangas_compartidos mc ON m.id = mc.manga_id
    WHERE m.id = ? AND mc.activo = 1 AND m.usuario_id != ?
");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    die("Este manga no está disponible o no tienes permiso para verlo. Ve a <a href='mangas_compartidos.php'>mangas compartidos</a>");
}

// Obtener todos los capítulos del manga
$stmt_chapters = $conn->prepare("SELECT id, titulo FROM capitulos WHERE manga_id = ? ORDER BY fecha_subida ASC");
$stmt_chapters->execute([$manga_id]);
$capitulos = $stmt_chapters->fetchAll(PDO::FETCH_ASSOC);

// Verificar si el manga está en la biblioteca del usuario
$stmt_biblioteca = $conn->prepare("SELECT id FROM biblioteca_usuario WHERE usuario_id = ? AND manga_id = ?");
$stmt_biblioteca->execute([$usuario_id, $manga_id]);
$en_biblioteca = $stmt_biblioteca->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/css/manga_verso.css">
<title>Viendo: <?= htmlspecialchars($manga['titulo']); ?> - Manga_verso</title>
</head>
<body>

<header>
    <div>
        <h1>Manga_verso</h1>
        <p>Tu portal de manga</p>
    </div>
    <div class="auth-logged">
        <a href="perfil.php"><?= htmlspecialchars($_SESSION['usuario']); ?></a>
        <a href="../public/logout.php">Cerrar sesión</a>
    </div>
</header>

<main>
    <div class="manga-header">
        <div class="manga-portada">
            <img src="../<?= htmlspecialchars($manga['portada']); ?>" 
                 alt="<?= htmlspecialchars($manga['titulo']); ?>"
                 onerror="this.src='../img/placeholder.png'">
        </div>
        
        <div class="manga-detalles">
            <h1><?= htmlspecialchars($manga['titulo']); ?></h1>
            
            <div class="manga-autor">
                👤 Compartido por: <strong><?= htmlspecialchars($manga['autor']); ?></strong>
            </div>
            
            <div class="manga-descripcion">
                <?= nl2br(htmlspecialchars($manga['descripcion'])); ?>
            </div>
            
            <div class="manga-info-row">
                <div class="manga-info-item">
                    <span class="manga-info-label">Capítulos</span>
                    <span class="manga-info-value"><?= count($capitulos); ?></span>
                </div>
                <div class="manga-info-item">
                    <span class="manga-info-label">Compartido</span>
                    <span class="manga-info-value"><?= date('d/m/Y', strtotime($manga['fecha_subida'])); ?></span>
                </div>
            </div>
        </div>
    </div>
    
    <div class="capitulos-container">
        <h2>📖 Capítulos disponibles</h2>
        
        <?php if ($capitulos): ?>
            <div class="capitulos-list">
                <?php foreach ($capitulos as $cap): ?>
                    <div class="capitulo-item">
                        <span class="capitulo-nombre"><?= htmlspecialchars($cap['titulo']); ?></span>
                        <a href="leer_capitulo_compartido.php?capitulo=<?= $cap['id']; ?>" class="capitulo-leer">📖 Leer</a>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-gray-100 rounded py-5 text-center text-gray-600">
                No hay capítulos disponibles para este manga.
            </div>
        <?php endif; ?>
    </div>
    
    <div style="text-align: center; margin-top: 40px; display: flex; gap: 10px; justify-content: center; flex-wrap: wrap;">
        <form action="procesar_biblioteca.php" method="POST" style="display: inline;">
            <input type="hidden" name="manga_id" value="<?= $manga_id; ?>">
            <input type="hidden" name="referer" value="leer_manga_compartido.php">
            <?php if ($en_biblioteca): ?>
                <input type="hidden" name="accion" value="eliminar">
                <button type="submit" class="btn-secondary" style="background-color: #dc3545;">📚 Eliminar de mi biblioteca</button>
            <?php else: ?>
                <input type="hidden" name="accion" value="agregar">
                <button type="submit" class="btn-primary" style="background-color: #28a745;">📚 Agregar a mi biblioteca</button>
            <?php endif; ?>
        </form>
        <a href="mangas_compartidos.php" class="btn-secondary">⬅ Volver a mangas compartidos</a>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>

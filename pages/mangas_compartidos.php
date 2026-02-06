<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Obtener término de búsqueda si existe
$busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

// Obtener mangas compartidos con información completa
$query = "
    SELECT m.id, m.titulo, m.descripcion, m.portada, m.fecha_subida, m.categoria_id, m.es_original, u.usuario as autor,
           COUNT(c.id) as total_capitulos, mc.fecha_comparticion
    FROM mangas_compartidos mc
    JOIN mangas m ON mc.manga_id = m.id
    JOIN usuarios u ON m.usuario_id = u.id
    LEFT JOIN capitulos c ON m.id = c.manga_id
    WHERE mc.activo = 1 AND m.usuario_id != ?
";

$params = [$usuario_id];

if (!empty($busqueda)) {
    $query .= " AND (m.titulo LIKE ? OR u.usuario LIKE ?)";
    $params[] = "%" . $busqueda . "%";
    $params[] = "%" . $busqueda . "%";
}

$query .= " GROUP BY m.id ORDER BY mc.fecha_comparticion DESC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$mangas_compartidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener mis mangas compartidos
$stmt_mios = $conn->prepare("
    SELECT m.id, m.titulo, m.descripcion, m.portada, m.fecha_subida, m.categoria_id, m.es_original,
           COUNT(c.id) as total_capitulos, mc.activo, mc.fecha_comparticion
    FROM mangas_compartidos mc
    JOIN mangas m ON mc.manga_id = m.id
    LEFT JOIN capitulos c ON m.id = c.manga_id
    WHERE m.usuario_id = ?
    GROUP BY m.id
    ORDER BY mc.fecha_comparticion DESC
");
$stmt_mios->execute([$usuario_id]);
$mis_mangas_compartidos = $stmt_mios->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/css/manga_verso.css">
<title>Mangas Compartidos - Manga_verso</title>
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
    <h1>📚 Mangas Compartidos</h1>
    
    <!-- MANGAS DE OTROS USUARIOS -->
    <div class="section-title">Mangas compartidos</div>
    
    <!-- Buscador -->
    <div class="buscador-container">
        <form method="GET" class="buscador-form">
            <input type="text" name="busqueda" placeholder="Buscar por título o autor..." value="<?= htmlspecialchars($busqueda); ?>">
            <button type="submit">🔍 Buscar</button>
            <?php if (!empty($busqueda)): ?>
                <a href="mangas_compartidos.php">✕ Limpiar</a>
            <?php endif; ?>
        </form>
    </div>
    
    <?php if ($mangas_compartidos): ?>
        <p style="color: #666; margin-bottom: 20px;">Se encontraron <?= count($mangas_compartidos); ?> manga(s)</p>
        <div class="mangas-grid">
            <?php foreach ($mangas_compartidos as $manga): ?>
                <div class="manga-card">
                    <img src="../<?= htmlspecialchars($manga['portada']); ?>" 
                         alt="<?= htmlspecialchars($manga['titulo']); ?>" 
                         class="manga-card-img"
                         onerror="this.src='../img/placeholder.png'">
                    <div class="manga-card-info">
                        <div class="manga-card-titulo"><?= htmlspecialchars($manga['titulo']); ?></div>
                        <?php if ($manga['es_original']): ?>
                            <div class="manga-card-autor" style="color: #FFD700; font-weight: bold;">✨ Original por <?= htmlspecialchars($manga['autor']); ?></div>
                        <?php else: ?>
                            <div class="manga-card-autor">👤 <?= htmlspecialchars($manga['autor']); ?></div>
                        <?php endif; ?>
                        <div class="manga-card-capitulos">📖 <?= $manga['total_capitulos']; ?> capítulo(s)</div>
                        <div class="manga-card-actions">
                            <a href="leer_manga_compartido.php?manga=<?= $manga['id']; ?>" class="btn-leer">📖 Leer</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="empty-message">
            <p>No hay mangas compartidos disponibles en este momento.</p>
            <p>¡Sé el primero en compartir tu manga!</p>
        </div>
    <?php endif; ?>
    
    <div style="text-align: center; margin-top: 40px;">
        <a href="../public/index.php" class="btn-secondary">🏠 Volver al inicio</a>
    </div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>

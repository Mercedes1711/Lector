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

// Verificar que el manga pertenece al usuario logueado
$stmt = $conn->prepare("SELECT titulo FROM mangas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    die("No tienes permiso para ver este manga.");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../public/manga_verso.css">
<title>Capítulos de <?= htmlspecialchars($manga['titulo']); ?></title>
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
<h1>Capítulos de <?= htmlspecialchars($manga['titulo']); ?></h1>

<?php if (isset($_SESSION['error'])): ?>
    <div style="color: red; margin-bottom: 20px; border: 1px solid red; padding: 10px; background-color: #ffe6e6;">
        <?= htmlspecialchars($_SESSION['error']); ?>
    </div>
    <?php unset($_SESSION['error']); ?>
<?php endif; ?>

<!-- Formulario para subir capítulo -->
<form action="subir_capitulo.php" method="POST" enctype="multipart/form-data" style="margin-bottom:30px;">
    <input type="hidden" name="manga_id" value="<?= $manga_id; ?>">

    <label>Título del capítulo:</label><br>
    <input type="text" name="titulo" required><br><br>

    <label>Archivo del capítulo (PDF o ZIP):</label><br>
    <input type="file" name="archivo" accept=".pdf,.zip" required><br><br>

    <button type="submit" class="btn-primary">Subir capítulo</button>
</form>

<h2>Capítulos existentes</h2>

<!-- Formulario de búsqueda -->
<form method="GET" action="" style="margin-bottom:20px;">
    <input type="hidden" name="manga" value="<?= $manga_id; ?>">
    <input type="text" name="buscar" placeholder="Buscar capítulo por título..." value="<?= htmlspecialchars($_GET['buscar'] ?? ''); ?>" style="padding:8px; width:300px; border:1px solid #ccc; border-radius:4px;">
    <button type="submit" class="btn-primary" style="padding:8px 15px;">🔍 Buscar</button>
    <?php if (!empty($_GET['buscar'])): ?>
        <a href="capitulos.php?manga=<?= $manga_id; ?>" style="padding:8px 15px; background-color:#6c757d; color:white; border-radius:4px; text-decoration:none; display:inline-block;">✕ Limpiar</a>
    <?php endif; ?>
</form>

<ul>
<?php
$query = "SELECT * FROM capitulos WHERE manga_id = ?";
$params = [$manga_id];

// Si hay búsqueda, añadir filtro
if (!empty($_GET['buscar'])) {
    $query .= " AND titulo LIKE ?";
    $params[] = "%" . trim($_GET['buscar']) . "%";
}

$query .= " ORDER BY fecha_subida ASC";

$stmt = $conn->prepare($query);
$stmt->execute($params);
$capitulos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($capitulos) {
    foreach ($capitulos as $cap) {
        echo "<li style='margin-bottom:10px;'>" . htmlspecialchars($cap['titulo']) . " - <a href='leer_capitulo.php?capitulo=" . $cap['id'] . "'>📖 Leer</a> 
        <form action='eliminar_capitulo.php' method='POST' style='display:inline; margin-left:10px;' onsubmit=\"return confirm('¿Eliminar este capítulo?');\">
            <input type='hidden' name='capitulo_id' value='" . $cap['id'] . "'>
            <button type='submit' style='background-color:#dc3545; color:white; border:none; padding:5px 10px; border-radius:3px; cursor:pointer;'>🗑️ Eliminar</button>
        </form></li>";
    }
} else {
    $mensaje = !empty($_GET['buscar']) ? "No se encontraron capítulos con ese título." : "No hay capítulos subidos todavía.";
    echo "<li>" . $mensaje . "</li>";
}
?>
</ul>

<a href="../public/index.php">⬅ Volver al inicio</a>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>

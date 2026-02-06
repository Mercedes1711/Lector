<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Validar que venga el ID del capítulo
if (empty($_GET['capitulo']) || !is_numeric($_GET['capitulo'])) {
    die("Capítulo inválido. Ve a <a href='../public/login.php'>inicio</a>");
}
$capitulo_id = (int)$_GET['capitulo'];

// Obtener el capítulo y verificar que está en un manga compartido
$stmt = $conn->prepare("
    SELECT c.*, m.titulo AS manga_titulo, m.usuario_id, u.usuario as autor
    FROM capitulos c
    JOIN mangas m ON c.manga_id = m.id
    JOIN usuarios u ON m.usuario_id = u.id
    JOIN mangas_compartidos mc ON m.id = mc.manga_id
    WHERE c.id = ? AND mc.activo = 1 AND m.usuario_id != ?
");
$stmt->execute([$capitulo_id, $usuario_id]);
$capitulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$capitulo) {
    die("No tienes permiso para ver este capítulo. Ve a <a href='mangas_compartidos.php'>mangas compartidos</a>");
}

// Obtener el siguiente capítulo del mismo manga
$stmt_next = $conn->prepare("
    SELECT c.id
    FROM capitulos c
    WHERE c.manga_id = ? AND c.fecha_subida > ?
    ORDER BY c.fecha_subida ASC
    LIMIT 1
");
$stmt_next->execute([$capitulo['manga_id'], $capitulo['fecha_subida']]);
$next_capitulo = $stmt_next->fetch(PDO::FETCH_ASSOC);

// Obtener el capítulo anterior
$stmt_prev = $conn->prepare("
    SELECT c.id
    FROM capitulos c
    WHERE c.manga_id = ? AND c.fecha_subida < ?
    ORDER BY c.fecha_subida DESC
    LIMIT 1
");
$stmt_prev->execute([$capitulo['manga_id'], $capitulo['fecha_subida']]);
$prev_capitulo = $stmt_prev->fetch(PDO::FETCH_ASSOC);

// Obtener todos los capítulos para el selector
$stmt_all = $conn->prepare("SELECT id, titulo FROM capitulos WHERE manga_id = ? ORDER BY fecha_subida ASC");
$stmt_all->execute([$capitulo['manga_id']]);
$all_capitulos = $stmt_all->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="../css/css/manga_verso.css">
<title>Leyendo: <?= htmlspecialchars($capitulo['titulo']); ?> - <?= htmlspecialchars($capitulo['manga_titulo']); ?></title>
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
<h1>Leyendo: <?= htmlspecialchars($capitulo['titulo']); ?> de <?= htmlspecialchars($capitulo['manga_titulo']); ?></h1>
<p>Por: <?= htmlspecialchars($capitulo['autor']); ?></p>

<div class="text-center py-5">
    <?php if ($prev_capitulo): ?>
        <a href="leer_capitulo_compartido.php?capitulo=<?= $prev_capitulo['id']; ?>" class="btn-secondary">⬅️ Capítulo anterior</a>
    <?php endif; ?>

    <select onchange="location.href='leer_capitulo_compartido.php?capitulo='+this.value;" class="mx-2">
        <option value="">-- Selecciona un capítulo --</option>
        <?php foreach ($all_capitulos as $chap): ?>
            <option value="<?= $chap['id']; ?>" <?= $chap['id'] == $capitulo_id ? 'selected' : ''; ?>>
                <?= htmlspecialchars($chap['titulo']); ?>
            </option>
        <?php endforeach; ?>
    </select>

    <?php if ($next_capitulo): ?>
        <a href="leer_capitulo_compartido.php?capitulo=<?= $next_capitulo['id']; ?>" class="btn-primary">➡️ Siguiente capítulo</a>
    <?php endif; ?>
</div>

<div class="pdf-container">
    <embed src="../<?= htmlspecialchars($capitulo['archivo']); ?>" type="application/pdf">
</div>

<div class="text-center py-5">
    <a href="leer_manga_compartido.php?manga=<?= $capitulo['manga_id']; ?>" class="btn-secondary">⬅ Volver al manga</a>
</div>
</main>

<footer>
    <p>&copy; 2025 Manga_verso</p>
</footer>
</body>
</html>

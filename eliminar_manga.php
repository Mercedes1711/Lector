<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . "/conexion_bd.php";
$usuario_id = $_SESSION['user_id'];

// Validar que se pase el ID del manga por POST
if (!isset($_POST['manga_id']) || !is_numeric($_POST['manga_id'])) {
    die("Manga inválido.");
}

$manga_id = (int)$_POST['manga_id'];

// Comprobar que el manga pertenece al usuario logueado
$stmt = $conn->prepare("SELECT portada FROM mangas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    die("No tienes permiso para eliminar este manga.");
}

// Borrar archivo de portada si existe
if (file_exists($manga['portada'])) {
    unlink($manga['portada']);
}

// Borrar manga de la base de datos
$stmt = $conn->prepare("DELETE FROM mangas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$manga_id, $usuario_id]);

// Borrar capítulos asociados
$stmt = $conn->prepare("SELECT archivo FROM capitulos WHERE manga_id = ?");
$stmt->execute([$manga_id]);
$capitulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($capitulos as $cap) {
    if (file_exists($cap['archivo'])) unlink($cap['archivo']);
}

// Borrar capítulos de la base de datos
$stmt = $conn->prepare("DELETE FROM capitulos WHERE manga_id = ?");
$stmt->execute([$manga_id]);

// Alerta y redirección
echo "<script>
        alert('¡Manga eliminado correctamente!');
        window.location.href = 'index.php';
      </script>";
exit;
?>

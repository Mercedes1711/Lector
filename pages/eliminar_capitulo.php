<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";
$usuario_id = $_SESSION['user_id'];

// Validar que se pase el ID del capítulo por POST
if (!isset($_POST['capitulo_id']) || !is_numeric($_POST['capitulo_id'])) {
    die("Capítulo inválido.");
}

$capitulo_id = (int)$_POST['capitulo_id'];

// Obtener capítulo y verificar que pertenece al usuario
$stmt = $conn->prepare("
    SELECT c.*, m.id as manga_id
    FROM capitulos c
    JOIN mangas m ON c.manga_id = m.id
    WHERE c.id = ? AND m.usuario_id = ?
");
$stmt->execute([$capitulo_id, $usuario_id]);
$capitulo = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$capitulo) {
    die("No tienes permiso para eliminar este capítulo.");
}

$manga_id = $capitulo['manga_id'];

// Borrar archivo del capítulo si existe
$rutaCapitulo = "../" . $capitulo['archivo'];
if (file_exists($rutaCapitulo)) {
    unlink($rutaCapitulo);
}

// Borrar capítulo de la base de datos
$stmt = $conn->prepare("DELETE FROM capitulos WHERE id = ?");
$stmt->execute([$capitulo_id]);

// Alerta y redirección
echo "<script>
        alert('¡Capítulo eliminado correctamente!');
        window.location.href = 'capitulos.php?manga=" . $manga_id . "';
      </script>";
exit;
?>

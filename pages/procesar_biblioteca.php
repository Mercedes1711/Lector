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
if (empty($_POST['manga_id']) || !is_numeric($_POST['manga_id'])) {
    $_SESSION['error'] = "Manga inválido.";
    header("Location: mangas_compartidos.php");
    exit;
}

$manga_id = (int)$_POST['manga_id'];
$accion = $_POST['accion'] ?? '';

// Verificar que el manga existe y está compartido (y no es del usuario)
$stmt = $conn->prepare("
    SELECT m.id
    FROM mangas m
    JOIN mangas_compartidos mc ON m.id = mc.manga_id
    WHERE m.id = ? AND mc.activo = 1 AND m.usuario_id != ?
");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    $_SESSION['error'] = "Este manga no está disponible.";
    header("Location: mangas_compartidos.php");
    exit;
}

if ($accion === 'agregar') {
    // Verificar si ya está en la biblioteca
    $stmt_check = $conn->prepare("SELECT id FROM biblioteca_usuario WHERE usuario_id = ? AND manga_id = ?");
    $stmt_check->execute([$usuario_id, $manga_id]);
    $existe = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if ($existe) {
        $_SESSION['error'] = "Este manga ya está en tu biblioteca.";
    } else {
        // Agregar a la biblioteca
        $stmt_insert = $conn->prepare("INSERT INTO biblioteca_usuario (usuario_id, manga_id) VALUES (?, ?)");
        if ($stmt_insert->execute([$usuario_id, $manga_id])) {
            $_SESSION['exito'] = "✅ Manga agregado a tu biblioteca!";
        } else {
            $_SESSION['error'] = "Error al agregar el manga.";
        }
    }
} elseif ($accion === 'eliminar') {
    // Eliminar de la biblioteca
    $stmt_delete = $conn->prepare("DELETE FROM biblioteca_usuario WHERE usuario_id = ? AND manga_id = ?");
    if ($stmt_delete->execute([$usuario_id, $manga_id])) {
        $_SESSION['exito'] = "✅ Manga eliminado de tu biblioteca.";
    } else {
        $_SESSION['error'] = "Error al eliminar el manga.";
    }
}

// Redirigir de vuelta
$referer = $_POST['referer'] ?? 'mangas_compartidos.php';
header("Location: " . $referer . (strpos($referer, '?') !== false ? '&' : '?') . "manga=" . $manga_id);
exit;

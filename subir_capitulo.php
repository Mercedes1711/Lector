<?php
session_start();
require __DIR__ . "/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Formulario no enviado. Ve a <a href='index.php'>inicio</a>");
}

// Recoger datos
$manga_id = (int)($_POST['manga_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$archivo = $_FILES['archivo'] ?? null;

if ($manga_id <= 0 || $titulo === '' || !$archivo) {
    die("Datos incompletos. Asegúrate de completar todos los campos.");
}

// Verificar que el manga pertenece al usuario logueado
$stmt = $conn->prepare("SELECT id FROM mangas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    die("No tienes permiso para subir capítulos a este manga.");
}

// Carpeta para almacenar capítulos
$carpeta = "Manga/capitulos/$manga_id/";
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// Validar archivo
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['pdf', 'zip'])) {
    die("Solo archivos PDF o ZIP están permitidos.");
}

// Nombre único
$nombreArchivo = uniqid("cap_") . "." . $ext;
$rutaArchivo = $carpeta . $nombreArchivo;

// Subir archivo
if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
    die("Error al subir el archivo. Verifica permisos en la carpeta.");
}

// Insertar en base de datos
$stmt = $conn->prepare("INSERT INTO capitulos (manga_id, titulo, archivo) VALUES (?, ?, ?)");
$stmt->execute([$manga_id, $titulo, $rutaArchivo]);

// Redirigir al manga
header("Location: capitulos.php?manga=$manga_id");
exit;

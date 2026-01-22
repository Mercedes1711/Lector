<?php
session_start();
require __DIR__ . "/../src/conexion_bd.php";

// Verificar sesión
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

$usuario_id = $_SESSION['user_id'];

// Solo permitir POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Formulario no enviado. Ve a <a href='../public/index.php'>inicio</a>");
}

// Recoger datos
$manga_id = (int)($_POST['manga_id'] ?? 0);
$titulo = trim($_POST['titulo'] ?? '');
$archivo = $_FILES['archivo'] ?? null;

if ($manga_id <= 0 || $titulo === '' || !$archivo) {
    $_SESSION['error'] = "Datos incompletos. Asegúrate de completar todos los campos.";
    header("Location: capitulos.php?manga=$manga_id");
    exit;
}

// Verificar que el manga pertenece al usuario logueado
$stmt = $conn->prepare("SELECT id FROM mangas WHERE id = ? AND usuario_id = ?");
$stmt->execute([$manga_id, $usuario_id]);
$manga = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$manga) {
    $_SESSION['error'] = "No tienes permiso para subir capítulos a este manga.";
    header("Location: capitulos.php?manga=$manga_id");
    exit;
}

// Carpeta para almacenar capítulos
$carpeta = "../Manga/capitulos/$manga_id/";
if (!is_dir($carpeta)) {
    mkdir($carpeta, 0777, true);
}

// Validar archivo
$ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['pdf', 'zip'])) {
    $_SESSION['error'] = "Solo archivos PDF o ZIP están permitidos.";
    header("Location: capitulos.php?manga=$manga_id");
    exit;
}

// Nombre único
$nombreArchivo = uniqid("cap_") . "." . $ext;
$rutaArchivo = $carpeta . $nombreArchivo;

// Subir archivo
if (!move_uploaded_file($archivo['tmp_name'], $rutaArchivo)) {
    $_SESSION['error'] = "Error al subir el archivo. Verifica permisos en la carpeta.";
    header("Location: capitulos.php?manga=$manga_id");
    exit;
}

// Verificar que no existe un capítulo con el mismo título en este manga
$stmt = $conn->prepare("SELECT id FROM capitulos WHERE manga_id = ? AND titulo = ?");
$stmt->execute([$manga_id, $titulo]);
$capitulo_duplicado = $stmt->fetch(PDO::FETCH_ASSOC);

if ($capitulo_duplicado) {
    // Eliminar archivo subido
    unlink($rutaArchivo);
    $_SESSION['error'] = "Ya existe un capítulo con el título '" . htmlspecialchars($titulo) . "' en este manga.";
    header("Location: capitulos.php?manga=$manga_id");
    exit;
}

// Verificar que no existe un archivo duplicado en toda la base de datos
$stmt = $conn->prepare("SELECT id FROM capitulos WHERE archivo = ?");
$stmt->execute([$rutaArchivoBD]);
$archivo_duplicado = $stmt->fetch(PDO::FETCH_ASSOC);

if ($archivo_duplicado) {
    // Eliminar archivo subido
    unlink($rutaArchivo);
    $_SESSION['error'] = "Este archivo ya ha sido subido anteriormente.";
    header("Location: capitulos.php?manga=$manga_id");
    exit;
}

// Guardar en BD con ruta relativa a la raíz
$rutaArchivoBD = "Manga/capitulos/$manga_id/$nombreArchivo";

// Insertar en base de datos
$stmt = $conn->prepare("INSERT INTO capitulos (manga_id, titulo, archivo) VALUES (?, ?, ?)");
$stmt->execute([$manga_id, $titulo, $rutaArchivoBD]);

// Redirigir al manga
header("Location: capitulos.php?manga=$manga_id");
exit;

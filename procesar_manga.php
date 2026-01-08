<?php
session_start();

// Solo usuarios logueados
if (empty($_SESSION['usuario'])) {
    header("Location: login.php");
    exit;
}

// Validar que el formulario y archivos existan
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || 
    !isset($_POST['titulo'], $_POST['descripcion'], $_FILES['portada'], $_FILES['archivo'])) {
    die("Datos incompletos o archivo demasiado grande. Asegúrate de que los archivos no excedan el límite de PHP.");
}

// Recoger datos
$titulo = trim($_POST['titulo']);
$descripcion = trim($_POST['descripcion']);

// Carpetas dentro del proyecto
$carpetaBase    = "Manga";
$carpetaPortada = $carpetaBase . "/portadas/";
$carpetaArchivo = $carpetaBase . "/archivos/";

// Crear carpetas si no existen
if (!is_dir($carpetaPortada)) mkdir($carpetaPortada, 0777, true);
if (!is_dir($carpetaArchivo)) mkdir($carpetaArchivo, 0777, true);

// Archivos subidos
$portada = $_FILES['portada'];
$archivo = $_FILES['archivo'];

// Extensiones permitidas
$extPortadasPermitidas = ['jpg', 'jpeg', 'png'];
$extMangasPermitidas   = ['pdf', 'zip'];

$extPortada = strtolower(pathinfo($portada['name'], PATHINFO_EXTENSION));
$extManga   = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

// Validaciones de extensión
if (!in_array($extPortada, $extPortadasPermitidas)) {
    die("La portada debe ser JPG o PNG");
}

if (!in_array($extManga, $extMangasPermitidas)) {
    die(" El manga debe ser PDF o ZIP");
}

// Nombres únicos para evitar sobreescritura
$nombrePortada = uniqid("portada_") . "." . $extPortada;
$nombreManga   = uniqid("manga_") . "." . $extManga;

// Rutas finales
$rutaPortada = $carpetaPortada . $nombrePortada;
$rutaManga   = $carpetaArchivo . $nombreManga;

// Subir archivos
if (!move_uploaded_file($portada['tmp_name'], $rutaPortada)) {
    die(" Error al subir la portada. Verifica permisos de la carpeta Manga/portadas.");
}

if (!move_uploaded_file($archivo['tmp_name'], $rutaManga)) {
    die(" Error al subir el archivo del manga. Verifica permisos de la carpeta Manga/archivos.");
}

// Guardar info del manga
$datosManga = [
    $titulo,
    $descripcion,
    $rutaPortada,
    $rutaManga
];

$archivoInfo = $carpetaBase . "/manga_" . uniqid() . ".txt";
file_put_contents($archivoInfo, implode("|", $datosManga));

// Redirigir automáticamente al index
header("Location: index.php");
exit;

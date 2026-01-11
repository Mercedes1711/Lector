<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

require __DIR__ . "/conexion_bd.php";

// ID del usuario logueado
$usuario_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $titulo = trim($_POST['titulo']);
    $descripcion = trim($_POST['descripcion']);

    if ($titulo === '' || $descripcion === '' || !isset($_FILES['portada'])) {
        die("Datos incompletos. Rellena todos los campos.");
    }

    // Carpeta para guardar portadas
    $carpetaPortada = "img/";
    if (!is_dir($carpetaPortada)) mkdir($carpetaPortada, 0777, true);

    $portada = $_FILES['portada'];
    $ext = strtolower(pathinfo($portada['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ['jpg','jpeg','png'])) die("Solo JPG/PNG permitidos");

    $nombrePortada = uniqid("portada_") . "." . $ext;
    $rutaPortada = $carpetaPortada . $nombrePortada;

    if (!move_uploaded_file($portada['tmp_name'], $rutaPortada)) {
        die("Error al subir la portada.");
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("
        INSERT INTO mangas (usuario_id, titulo, descripcion, portada)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$usuario_id, $titulo, $descripcion, $rutaPortada]);

    // ---- ALERTA ----
    echo "<script>
        alert('¡Manga subido correctamente!');
        window.location.href = 'index.php';
    </script>";
    exit;

} else {
    echo "Acceso no permitido.";
}

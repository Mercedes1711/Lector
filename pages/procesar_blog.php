<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";

$usuario_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $es_spoiler = isset($_POST['es_spoiler']) ? 1 : 0;
    
    if (empty($titulo) || empty($contenido)) {
        $_SESSION['error'] = "El título y el contenido son obligatorios.";
        header("Location: crear_blog.php");
        exit;
    }

    $rutaPortadaBD = null;

    // Manejo de la portada si se subió una
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $carpetaPortada = "../img/";
        if (!is_dir($carpetaPortada)) mkdir($carpetaPortada, 0777, true);

        $portada = $_FILES['portada'];
        $ext = strtolower(pathinfo($portada['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $nombrePortada = uniqid("blog_") . "." . $ext;
            $rutaPortada = $carpetaPortada . $nombrePortada;

            if (move_uploaded_file($portada['tmp_name'], $rutaPortada)) {
                $rutaPortadaBD = "img/" . $nombrePortada;
            }
        }
    }

    try {
        $stmt = $conn->prepare("
            INSERT INTO articulos_blog (titulo, contenido, autor_id, portada, es_spoiler) 
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([$titulo, $contenido, $usuario_id, $rutaPortadaBD, $es_spoiler]);
        
        $_SESSION['exito'] = "¡Artículo de blog publicado correctamente!";
        header("Location: blog.php");
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al guardar el artículo: " . $e->getMessage();
        header("Location: crear_blog.php");
    }
    exit;
} else {
    header("Location: blog.php");
    exit;
}

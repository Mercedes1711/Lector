<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";

$usuario_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $titulo = trim($_POST['titulo']);
    $contenido = trim($_POST['contenido']);
    $es_spoiler = isset($_POST['es_spoiler']) ? 1 : 0;

    if ($post_id <= 0 || empty($titulo) || empty($contenido)) {
        $_SESSION['error'] = "Datos incompletos.";
        header("Location: editar_blog.php?id=" . $post_id);
        exit;
    }

    // Verificar autoría
    $stmt_check = $conn->prepare("SELECT portada FROM articulos_blog WHERE id = ? AND autor_id = ?");
    $stmt_check->execute([$post_id, $usuario_id]);
    $current_post = $stmt_check->fetch(PDO::FETCH_ASSOC);

    if (!$current_post) {
        $_SESSION['error'] = "Acceso denegado.";
        header("Location: blog.php");
        exit;
    }

    $rutaPortadaBD = $current_post['portada'];

    // Manejo de nueva portada
    if (isset($_FILES['portada']) && $_FILES['portada']['error'] === UPLOAD_ERR_OK) {
        $carpetaPortada = "../img/";
        $portada = $_FILES['portada'];
        $ext = strtolower(pathinfo($portada['name'], PATHINFO_EXTENSION));
        
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            $nombrePortada = uniqid("blog_") . "." . $ext;
            $rutaPortada = $carpetaPortada . $nombrePortada;

            if (move_uploaded_file($portada['tmp_name'], $rutaPortada)) {
                // Opcional: Eliminar portada antigua si existe
                if ($rutaPortadaBD && file_exists("../" . $rutaPortadaBD)) {
                   // @unlink("../" . $rutaPortadaBD); 
                }
                $rutaPortadaBD = "img/" . $nombrePortada;
            }
        }
    }

    try {
        $stmt = $conn->prepare("
            UPDATE articulos_blog 
            SET titulo = ?, contenido = ?, portada = ?, es_spoiler = ? 
            WHERE id = ? AND autor_id = ?
        ");
        $stmt->execute([$titulo, $contenido, $rutaPortadaBD, $es_spoiler, $post_id, $usuario_id]);
        
        $_SESSION['exito'] = "¡Artículo actualizado correctamente!";
        header("Location: post.php?id=" . $post_id);
    } catch (PDOException $e) {
        $_SESSION['error'] = "Error al actualizar: " . $e->getMessage();
        header("Location: editar_blog.php?id=" . $post_id);
    }
    exit;
} else {
    header("Location: blog.php");
    exit;
}

<?php
session_start();
if (empty($_SESSION['usuario']) || empty($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit;
}

require __DIR__ . "/../src/conexion_bd.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $articulo_id = isset($_POST['articulo_id']) ? (int)$_POST['articulo_id'] : 0;
    $usuario_id = $_SESSION['user_id'];
    $comentario = isset($_POST['comentario']) ? trim($_POST['comentario']) : '';
    $parent_id = !empty($_POST['parent_id']) ? (int)$_POST['parent_id'] : null;

    if ($articulo_id > 0 && !empty($comentario)) {
        try {
            $stmt = $conn->prepare("
                INSERT INTO comentarios_blog (articulo_id, usuario_id, comentario, parent_id) 
                VALUES (?, ?, ?, ?)
            ");
            $stmt->execute([$articulo_id, $usuario_id, $comentario, $parent_id]);
            $_SESSION['exito'] = "¡Comentario publicado con éxito!";
        } catch (PDOException $e) {
            $_SESSION['error'] = "Error al publicar el comentario.";
        }
    } else {
        $_SESSION['error'] = "El comentario no puede estar vacío.";
    }

    header("Location: post.php?id=" . $articulo_id);
    exit;
} else {
    header("Location: blog.php");
    exit;
}

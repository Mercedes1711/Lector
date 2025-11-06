<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>login</title>
</head>
<body>
  <?php
include 'conexion_bd.php'; // 👈 conexión PDO


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $contraseña = $_POST['contraseña'];

    // Buscar la contraseña del usuario en la base de datos
    $sql = "SELECT contraseña FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$usuario]);
    $fila = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($fila) { // Si existe el usuario
        if (password_verify($contraseña, $fila['contraseña'])) {
            echo "✅ Acceso concedido. Bienvenido, $usuario!";
            header("Location: index.html"); // 👈 corregido (era 'Locate')
            exit; // siempre después de header()
        } else {
            echo "❌ Contraseña incorrecta.";
        }
    } else {
        echo "❌ Usuario no encontrado. Vuelve a intentarlo o regístrate.";
    }
}

  
  ?>
  <form action= <?php echo htmlspecialchars($_SERVER['PHP_SELF']);?> method= "POST">
    <label>usuario</label>
    <input name= "usuario" type="text">
    <label>contraseña</label>
    <input name= "contraseña" type="password">
    <label> 
    <input type="submit">
</form>
<p> No tienes cuenta <a href="sing_in.php">Registrate</a> </p>
</body>
</html>
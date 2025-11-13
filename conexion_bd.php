<?php

// Datos de conexión
$host = "localhost";     // Servidor (localhost si usas XAMPP)
$dbname = "Manga_verso";    // Nombre de tu base de datos
$username = "root";      // Usuario de MySQL (por defecto en XAMPP)
$password = "";          // Contraseña (vacía por defecto)

// Intentar conexión usando PDO
try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);

    // Configurar modo de errores para que lance excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}










?>
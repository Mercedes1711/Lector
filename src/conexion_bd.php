<?php

// Define la base URL de la aplicación
// Define la base URL de la aplicación
if (!defined('BASE_URL')) {
    // Si existe la variable de entorno BASE_URL (Docker), usarla preferentemente.
    $env_url = getenv('BASE_URL');
    if ($env_url) {
        define('BASE_URL', rtrim($env_url, '/') . '/');
    } else {
        // Detección automática para local/XAMPP o accesos via IP
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        // Si hay una variable de entorno de DB, estamos en Docker y la raíz es '/'
        if (getenv('DB_HOST')) {
            $path = '/';
        } else {
            // Fuera de Docker (XAMPP tradicional)
            $path = '/dashboard/Lector/';
        }
        define('BASE_URL', "$protocol://$host$path");
    }
}

// Datos de conexión (Prioriza variables de entorno para Docker, fallback a XAMPP)
$host     = getenv('DB_HOST') ?: "localhost";
$port     = getenv('DB_PORT') ?: "3306";
$dbname   = getenv('DB_NAME') ?: "Manga_verso";
$username = getenv('DB_USER') ?: "root";
$password = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";

// Intentar conexión usando PDO
try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname;charset=utf8", $username, $password);

    // Configurar modo de errores para que lance excepciones
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch (PDOException $e) {
    // Error genérico para producción (evita revelar detalles técnicos)
    die("Error de conexión al servidor del Manga_verso. Por favor, inténtalo más tarde.");
}










?>
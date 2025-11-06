<?php
// Mercedes Lizcano Mora - 15/10/2025
// Tarea 3.3 - Desarrollador web en entorno servidor
// Archivo: conexion.php
// Descripción: Función para establecer la conexión a la base de datos (BBDD) 'miniportal_db' usando PDO.

/**
 * Establece la conexión a la base de datos 'miniportal_db' usando PDO.
 *
 * @return PDO|null Objeto PDO si la conexión es exitosa, o null en caso de error.
 */
function conectarDB() {
    // Configuración de la BBDD (asumiendo XAMPP o configuración local por defecto)
    $db_host = 'localhost';
    $db_name = 'miniportal_db'; // Nombre de la BD según miniportal.sql
    $db_user = 'root';     // Usuario por defecto (si usas XAMPP/MAMP)
    $db_pass = '';         // Contraseña por defecto (vacía si usas XAMPP/MAMP)
    
    // Data Source Name (DSN)
    $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

    // Opciones recomendadas de PDO
    $opciones = [
        // Lanzar excepciones en caso de error (fundamental para manejar transacciones)
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        
        // Devolver arrays asociativos por defecto (más fácil de usar)
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC    
    ];

    try {
        $pdo = new PDO($dsn, $db_user, $db_pass, $opciones);
        return $pdo;
    } catch (\PDOException $e) {
        // En caso de fallo de conexión (ej. el servidor no está encendido),
        // registramos el error y detenemos la ejecución.
        error_log("Error de conexión PDO: " . $e->getMessage());
        // En un entorno de producción, esto podría ser solo un mensaje genérico.
        return null;
    }
}
?>
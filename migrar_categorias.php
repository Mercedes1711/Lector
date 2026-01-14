<?php
require __DIR__ . "/conexion_bd.php";

// Crear tabla de categorías
$sql_categorias = "
CREATE TABLE IF NOT EXISTS categorias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

try {
    $conn->exec($sql_categorias);
    echo "Tabla 'categorias' creada exitosamente.<br>";
} catch (PDOException $e) {
    echo "Error creando tabla categorias: " . $e->getMessage() . "<br>";
}

// Agregar columna categoria_id a mangas si no existe
$sql_alter_mangas = "
ALTER TABLE mangas
ADD COLUMN IF NOT EXISTS categoria_id INT DEFAULT NULL,
ADD CONSTRAINT fk_mangas_categoria FOREIGN KEY (categoria_id) REFERENCES categorias(id) ON DELETE SET NULL;
";

try {
    $conn->exec($sql_alter_mangas);
    echo "Columna 'categoria_id' agregada a tabla 'mangas' exitosamente.<br>";
} catch (PDOException $e) {
    echo "Error alterando tabla mangas: " . $e->getMessage() . "<br>";
}

// Insertar categorías por defecto
$categorias_default = [
    ['Shonen', 'Mangas dirigidos a adolescentes varones, con acción y aventuras'],
    ['Shojo', 'Mangas dirigidos a adolescentes mujeres, con romance y drama'],
    ['Seinen', 'Mangas para adultos jóvenes, con temas más maduros'],
    ['Josei', 'Mangas para mujeres adultas, con romance y vida cotidiana'],
    ['Kodomo', 'Mangas para niños'],
    ['Hentai', 'Mangas para adultos con contenido explícito'],
    ['Yaoi', 'Mangas con temática homosexual masculina'],
    ['Yuri', 'Mangas con temática homosexual femenina'],
    ['Mecha', 'Mangas con robots y máquinas gigantes'],
    ['Horror', 'Mangas de terror y suspense'],
    ['Comedia', 'Mangas cómicos y humorísticos'],
    ['Drama', 'Mangas con temas dramáticos y emocionales'],
    ['Romance', 'Mangas centrados en historias de amor'],
    ['Fantasía', 'Mangas con elementos fantásticos y magia'],
    ['Ciencia Ficción', 'Mangas con temas futuristas y tecnológicos'],
    ['Aventura', 'Mangas de aventuras y exploración'],
    ['Misterio', 'Mangas con enigmas y detectives'],
    ['Deportes', 'Mangas sobre deportes y competición'],
    ['Histórico', 'Mangas ambientados en épocas históricas'],
    ['Otro', 'Categorías no especificadas']
];

$stmt = $conn->prepare("INSERT IGNORE INTO categorias (nombre, descripcion) VALUES (?, ?)");
foreach ($categorias_default as $categoria) {
    try {
        $stmt->execute($categoria);
    } catch (PDOException $e) {
        echo "Error insertando categoría {$categoria[0]}: " . $e->getMessage() . "<br>";
    }
}

echo "Categorías por defecto insertadas exitosamente.<br>";
echo "Migración completada.";
?>


-- Tabla de artículos de blog
CREATE TABLE IF NOT EXISTS `articulos_blog` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `contenido` TEXT NOT NULL,
  `autor_id` INT(11) NOT NULL,
  `portada` VARCHAR(255) DEFAULT NULL,
  `es_spoiler` TINYINT(1) NOT NULL DEFAULT 0,
  `fecha_creacion` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`autor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla de comentarios de blog
CREATE TABLE IF NOT EXISTS `comentarios_blog` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `articulo_id` INT(11) NOT NULL,
  `usuario_id` INT(11) NOT NULL,
  `parent_id` INT(11) DEFAULT NULL,
  `comentario` TEXT NOT NULL,
  `fecha_comentario` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
  PRIMARY KEY (`id`),
  FOREIGN KEY (`articulo_id`) REFERENCES `articulos_blog` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  FOREIGN KEY (`parent_id`) REFERENCES `comentarios_blog` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar un artículo de prueba si no existe ninguno
INSERT INTO `articulos_blog` (`titulo`, `contenido`, `autor_id`, `portada`) 
SELECT 'Bienvenidos al Blog de Manga_verso', 'Estamos muy emocionados de lanzar este nuevo blog donde compartiremos noticias, reseñas y actualizaciones sobre el mundo del manga.', (SELECT id FROM usuarios LIMIT 1), 'img/blog_placeholder.png'
WHERE NOT EXISTS (SELECT 1 FROM `articulos_blog`);

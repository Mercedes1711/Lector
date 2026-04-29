-- Script para agregar tabla de biblioteca de usuario

CREATE TABLE IF NOT EXISTS `biblioteca_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `manga_id` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `usuario_manga` (`usuario_id`, `manga_id`),
  KEY `usuario_id` (`usuario_id`),
  KEY `manga_id` (`manga_id`),
  CONSTRAINT `biblioteca_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `biblioteca_usuario_ibfk_2` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Listo! Ahora los usuarios pueden agregar mangas a su biblioteca.

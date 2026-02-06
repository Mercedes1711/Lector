-- Script para agregar la funcionalidad de compartir mangas
-- Ejecuta este script en PhpMyAdmin o en tu cliente MySQL

-- Crear tabla de mangas compartidos
CREATE TABLE IF NOT EXISTS `mangas_compartidos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `manga_id` int(11) NOT NULL,
  `fecha_comparticion` timestamp NOT NULL DEFAULT current_timestamp(),
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `manga_id` (`manga_id`),
  KEY `activo` (`activo`),
  CONSTRAINT `mangas_compartidos_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Listo! La tabla ha sido creada. Ahora puedes usar la funcionalidad de compartir.

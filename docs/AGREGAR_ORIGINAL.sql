-- Script para agregar el campo es_original a la tabla mangas

ALTER TABLE `mangas` ADD COLUMN `es_original` TINYINT(1) NOT NULL DEFAULT 0 AFTER `categoria_id`;

-- Listo! Ahora puedes usar la funcionalidad de marcar mangas como originales.

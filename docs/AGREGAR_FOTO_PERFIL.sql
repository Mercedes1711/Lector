-- Agregar columna de foto de perfil a la tabla usuarios
-- Ejecutar este script en tu base de datos manga_verso

ALTER TABLE usuarios ADD COLUMN foto_perfil VARCHAR(255) NULL DEFAULT NULL;

-- Para verificar que se agregó correctamente:
-- DESC usuarios;

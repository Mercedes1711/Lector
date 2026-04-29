-- Manual Repair Script for Missing Keys
USE `Manga_verso`;

-- Fix usuarios
ALTER TABLE `usuarios` ADD PRIMARY KEY (`id`);
ALTER TABLE `usuarios` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `usuarios` ADD UNIQUE KEY `usuario` (`usuario`);
ALTER TABLE `usuarios` ADD UNIQUE KEY `email` (`email`);

-- Fix mangas
ALTER TABLE `mangas` ADD PRIMARY KEY (`id`);
ALTER TABLE `mangas` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `mangas` ADD KEY `usuario_id` (`usuario_id`);
ALTER TABLE `mangas` ADD KEY `fk_mangas_categoria` (`categoria_id`);

-- Fix capitulos
ALTER TABLE `capitulos` ADD PRIMARY KEY (`id`);
ALTER TABLE `capitulos` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `capitulos` ADD KEY `manga_id` (`manga_id`);
ALTER TABLE `capitulos` ADD UNIQUE KEY `manga_titulo_unico` (`manga_id`, `titulo`);
ALTER TABLE `capitulos` ADD UNIQUE KEY `archivo_unico` (`archivo`);

-- Fix categorias
ALTER TABLE `categorias` ADD PRIMARY KEY (`id`);
ALTER TABLE `categorias` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `categorias` ADD UNIQUE KEY `nombre` (`nombre`);

-- Fix password_resets
ALTER TABLE `password_resets` ADD PRIMARY KEY (`id`);
ALTER TABLE `password_resets` MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
ALTER TABLE `password_resets` ADD KEY `user_id` (`user_id`);

-- Add foreign keys
ALTER TABLE `capitulos` ADD CONSTRAINT `capitulos_repair_fk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE;
ALTER TABLE `mangas` ADD CONSTRAINT `mangas_repair_fk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
ALTER TABLE `mangas` ADD CONSTRAINT `mangas_repair_fk_2` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL;
ALTER TABLE `password_resets` ADD CONSTRAINT `password_resets_repair_fk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

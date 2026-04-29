-- Fix missing keys and constraints
USE `Manga_verso`;

-- Indices de la tabla `mangas_compartidos`
ALTER TABLE `mangas_compartidos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `manga_id` (`manga_id`),
  ADD KEY `activo` (`activo`);

-- Indices de la tabla `capitulos`
ALTER TABLE `capitulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manga_id` (`manga_id`),
  ADD UNIQUE KEY `manga_titulo_unico` (`manga_id`, `titulo`),
  ADD UNIQUE KEY `archivo_unico` (`archivo`);

-- Indices de la tabla `categorias`
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

-- Indices de la tabla `mangas`
ALTER TABLE `mangas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_mangas_categoria` (`categoria_id`);

-- Indices de la tabla `password_resets`
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

-- Indices de la tabla `usuarios`
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

-- AUTO_INCREMENT de la tabla `mangas_compartidos`
ALTER TABLE `mangas_compartidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

-- AUTO_INCREMENT de la tabla `capitulos`
ALTER TABLE `capitulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- AUTO_INCREMENT de la tabla `categorias`
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

-- AUTO_INCREMENT de la tabla `mangas`
ALTER TABLE `mangas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

-- AUTO_INCREMENT de la tabla `password_resets`
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

-- AUTO_INCREMENT de la tabla `usuarios`
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

-- Restricciones para tablas volcadas

-- Filtros para la tabla `mangas_compartidos`
ALTER TABLE `mangas_compartidos`
  ADD CONSTRAINT `mangas_compartidos_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE;

-- Filtros para la tabla `capitulos`
ALTER TABLE `capitulos`
  ADD CONSTRAINT `capitulos_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE;

-- Filtros para la tabla `mangas`
ALTER TABLE `mangas`
  ADD CONSTRAINT `fk_mangas_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mangas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

-- Filtros para la tabla `password_resets`
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

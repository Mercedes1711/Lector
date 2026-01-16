-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 16-01-2026 a las 16:16:31
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `manga_verso`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `capitulos`
--

CREATE TABLE `capitulos` (
  `id` int(11) NOT NULL,
  `manga_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `archivo` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `capitulos`
--

INSERT INTO `capitulos` (`id`, `manga_id`, `titulo`, `archivo`, `fecha_subida`) VALUES
(5, 8, '01', 'Manga/capitulos/8/cap_6969043995f9c.pdf', '2026-01-15 15:14:01'),
(6, 8, '02', 'Manga/capitulos/8/cap_69690445ba76c.pdf', '2026-01-15 15:14:13'),
(7, 8, '03', 'Manga/capitulos/8/cap_69690499d3559.pdf', '2026-01-15 15:15:37'),
(9, 10, 'one shot', 'Manga/capitulos/10/cap_696923375df6c.pdf', '2026-01-15 17:26:15'),
(10, 8, '02', 'Manga/capitulos/8/cap_6969248cf076b.pdf', '2026-01-15 17:31:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Shonen', 'Mangas dirigidos a adolescentes varones, con acción y aventuras'),
(2, 'Shojo', 'Mangas dirigidos a adolescentes mujeres, con romance y drama'),
(3, 'Seinen', 'Mangas para adultos jóvenes, con temas más maduros'),
(4, 'Josei', 'Mangas para mujeres adultas, con romance y vida cotidiana'),
(5, 'Kodomo', 'Mangas para niños'),
(6, 'Hentai', 'Mangas para adultos con contenido explícito'),
(7, 'Yaoi', 'Mangas con temática homosexual masculina'),
(8, 'Yuri', 'Mangas con temática homosexual femenina'),
(9, 'Mecha', 'Mangas con robots y máquinas gigantes'),
(10, 'Horror', 'Mangas de terror y suspense'),
(11, 'Comedia', 'Mangas cómicos y humorísticos'),
(12, 'Drama', 'Mangas con temas dramáticos y emocionales'),
(13, 'Romance', 'Mangas centrados en historias de amor'),
(14, 'Fantasía', 'Mangas con elementos fantásticos y magia'),
(15, 'Ciencia Ficción', 'Mangas con temas futuristas y tecnológicos'),
(16, 'Aventura', 'Mangas de aventuras y exploración'),
(17, 'Misterio', 'Mangas con enigmas y detectives'),
(18, 'Deportes', 'Mangas sobre deportes y competición'),
(19, 'Histórico', 'Mangas ambientados en épocas históricas'),
(20, 'Otro', 'Categorías no especificadas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mangas`
--

CREATE TABLE `mangas` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `descripcion` text NOT NULL,
  `portada` varchar(255) NOT NULL,
  `fecha_subida` timestamp NOT NULL DEFAULT current_timestamp(),
  `categoria_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `mangas`
--

INSERT INTO `mangas` (`id`, `usuario_id`, `titulo`, `descripcion`, `portada`, `fecha_subida`, `categoria_id`) VALUES
(8, 23, 'Shadow', 'manga basado en sonic x shadow generations', 'img/portada_6969041e40021.png', '2026-01-15 15:13:34', 1),
(10, 26, 'shadow one shot', 'pequeño one shot sobre la historia de shadow en sonic 3 la pelicula', 'img/portada_69692318ad008.png', '2026-01-15 17:25:44', 16);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `contraseña` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `usuario`, `contraseña`, `email`, `fecha_registro`) VALUES
(23, 'si', '$2y$10$e84TqZl1XYtKC3Y8We4q9usH6WrW9EfrnZl/x/Dk3XygVxuQoFqJy', 'adrareveg@alu.edu.gva.es', '2026-01-08 17:56:17'),
(26, 'Adri', '$2y$10$nJT1INqY4rkUaAlO21VSwOwOFKZ3UHGY9YRq7.9aMbIkStzWAvJyS', 'adrianarenasvega@gmail.com', '2026-01-15 14:53:40');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `capitulos`
--
ALTER TABLE `capitulos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `manga_id` (`manga_id`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `mangas`
--
ALTER TABLE `mangas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fk_mangas_categoria` (`categoria_id`);

--
-- Indices de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `capitulos`
--
ALTER TABLE `capitulos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `mangas`
--
ALTER TABLE `mangas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `capitulos`
--
ALTER TABLE `capitulos`
  ADD CONSTRAINT `capitulos_ibfk_1` FOREIGN KEY (`manga_id`) REFERENCES `mangas` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mangas`
--
ALTER TABLE `mangas`
  ADD CONSTRAINT `fk_mangas_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `mangas_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `password_resets`
--
ALTER TABLE `password_resets`
  ADD CONSTRAINT `password_resets_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

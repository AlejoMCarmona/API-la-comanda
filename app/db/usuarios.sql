-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2023 a las 20:45:07
-- Versión del servidor: 10.4.28-MariaDB
-- Versión de PHP: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `la_comanda_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(30) NOT NULL,
  `apellido` varchar(30) NOT NULL,
  `dni` int(11) NOT NULL,
  `puesto` varchar(10) NOT NULL,
  `sector` varchar(20) DEFAULT NULL,
  `fechaAlta` datetime NOT NULL DEFAULT current_timestamp(),
  `email` varchar(50) NOT NULL,
  `clave` varchar(60) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellido`, `dni`, `puesto`, `sector`, `fechaAlta`, `email`, `clave`, `activo`) VALUES
(1, 'Tomas', 'Perez', 42147264, 'socio', NULL, '2023-11-20 20:38:23', 'tomas@test.com', '$2y$10$qGAbQXhkbmOO9Km/bTlwr.L8xHvlU/whp4bSr1/yB7u04CdroNZ1e', 1),
(2, 'Matias', 'Gomez', 42147265, 'socio', NULL, '2023-11-20 20:39:13', 'matias@test.com', '$2y$10$OO2aHgz6FPPqiJkklQlblOqfZxIzWJcHri0B4rZaoi1CfQggP6Sba', 1),
(3, 'Ezequiel', 'Perez', 42147266, 'socio', NULL, '2023-11-20 20:39:18', 'ezequiel@test.com', '$2y$10$uXNwuXYEgnjqoB2zWIfKG.jPfgKOyktNg4iwqZISau3sM74ciJucS', 1),
(4, 'Pedro', 'Perez', 42147259, 'cocinero', 'cocina', '2023-11-20 20:39:23', 'pedro@test.com', '$2y$10$fudNzwB9r1Z1FZ8dAutOM.RYCjsdp29.Wl1klEnHbPFdM9DZl4et6', 1),
(5, 'Alejandro', 'Gomez', 42147260, 'cervecero', 'barraChoperas', '2023-11-20 20:39:27', 'alejandro@test.com', '$2y$10$54Kok/O/dATyYl8X8HRQtewoJWvVaPYX2AQXGh966qp6ZWWwskfE6', 1),
(6, 'Jose', 'Fernandez', 42147261, 'cocinero', 'candyBar', '2023-11-20 20:39:32', 'jose@test.com', '$2y$10$ZNNvhKCzSSvAY0fh4mcHJOBZoabCClo5a1ZVdFRwbSOt0/EsILwmu', 1),
(7, 'Roberto', 'Hernandez', 42147262, 'bartender', 'barraTragos', '2023-11-20 20:39:37', 'roberto@test.com', '$2y$10$RZswzOPl6xgz1h5UFRXF4O3FixWvjqllJM1SLMIjxrXki7of1RUru', 1),
(8, 'Raul', 'Espinoza', 42147263, 'mozo', NULL, '2023-11-20 20:39:42', 'raul@test.com', '$2y$10$0iEFUgLEf.9YVl7lPrTf5OwK1wLdAmeGZv2OP72H4vgWz1buzu/pa', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

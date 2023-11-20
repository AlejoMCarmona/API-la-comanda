-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 20-11-2023 a las 20:23:12
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
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `tipo` varchar(20) NOT NULL,
  `sector` varchar(20) NOT NULL,
  `precio` decimal(10,2) NOT NULL,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `fechaIncorporacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `tipo`, `sector`, `precio`, `activo`, `fechaIncorporacion`) VALUES
(1, 'gnocchi', 'comida', 'cocina', 4000.00, 1, '2023-11-18 06:10:06'),
(2, 'spaghetti', 'comida', 'cocina', 3200.00, 1, '2023-11-18 06:10:06'),
(3, 'flan', 'comida', 'candyBar', 1500.00, 1, '2023-11-18 06:10:06'),
(4, 'ipa', 'bebida', 'barraChoperas', 1000.00, 1, '2023-11-18 06:10:06'),
(5, 'apa', 'bebida', 'barraChoperas', 1000.00, 0, '2023-11-18 06:10:06'),
(6, 'daikiri', 'bebida', 'barraTragos', 2000.00, 1, '2023-11-18 06:10:06'),
(7, 'sex on the beach', 'bebida', 'barraTragos', 2000.00, 1, '2023-11-18 06:10:06'),
(8, 'pizza ', 'comida', 'cocina', 3000.00, 1, '2023-11-18 06:10:06'),
(9, 'agua', 'bebida', 'cocina', 800.00, 1, '2023-11-18 06:10:06'),
(10, 'hamburguesa', 'comida', 'cocina', 3000.00, 0, '2023-11-18 06:10:06'),
(11, 'milanesa', 'comida', 'cocina', 2000.00, 1, '2023-11-18 07:47:30'),
(12, 'milanesa a caballo', 'comida', 'cocina', 2500.00, 1, '2023-11-20 18:02:24'),
(13, 'hamburguesa de garbanzo', 'comida', 'cocina', 2500.00, 1, '2023-11-20 18:02:37'),
(14, 'corona', 'bebida', 'barraChoperas', 2000.00, 1, '2023-11-20 18:02:59');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

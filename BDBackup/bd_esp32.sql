-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 06-11-2024 a las 21:47:59
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
-- Base de datos: `bd_esp32`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `controlcamaras`
--

CREATE TABLE `controlcamaras` (
  `id` int(11) NOT NULL,
  `idcamara` int(11) NOT NULL,
  `estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `controlcamaras`
--

INSERT INTO `controlcamaras` (`id`, `idcamara`, `estado`) VALUES(1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `servomotores`
--

CREATE TABLE `servomotores` (
  `id` int(11) NOT NULL,
  `NobreServoMotor` varchar(50) NOT NULL,
  `Estado` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `servomotores`
--

INSERT INTO `servomotores` (`id`, `NobreServoMotor`, `Estado`) VALUES(5, 'ServoZoom', 0);
INSERT INTO `servomotores` (`id`, `NobreServoMotor`, `Estado`) VALUES(6, 'ServoMovHorizontal', 120);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `controlcamaras`
--
ALTER TABLE `controlcamaras`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `servomotores`
--
ALTER TABLE `servomotores`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `controlcamaras`
--
ALTER TABLE `controlcamaras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `servomotores`
--
ALTER TABLE `servomotores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

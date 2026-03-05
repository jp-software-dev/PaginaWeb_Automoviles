-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-03-2026 a las 06:09:32
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
-- Base de datos: `collection_cars`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL COMMENT 'Nombre de usuario',
  `password_hash` varchar(255) NOT NULL COMMENT 'Hash de la contraseña',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `admins`
--

INSERT INTO `admins` (`id`, `username`, `password_hash`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2026-03-05 05:09:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehicles`
--

CREATE TABLE `vehicles` (
  `id` int(11) NOT NULL COMMENT 'Identificador único del vehículo',
  `brand` varchar(100) NOT NULL COMMENT 'Marca del vehículo',
  `model` varchar(100) NOT NULL COMMENT 'Modelo del vehículo',
  `year` int(11) NOT NULL COMMENT 'Año del vehículo',
  `mileage` varchar(50) NOT NULL COMMENT 'Kilometraje (puede incluir "N/A")',
  `exterior_color` varchar(100) NOT NULL COMMENT 'Color exterior',
  `interior_color` varchar(100) NOT NULL COMMENT 'Color interior',
  `engine` varchar(100) NOT NULL COMMENT 'Motor (ej. 3.8L Carrera S)',
  `price` decimal(10,2) NOT NULL COMMENT 'Precio en millones (MDP)',
  `price_unit` varchar(10) DEFAULT 'MDP' COMMENT 'Unidad del precio (MDP por defecto)',
  `potencia` varchar(100) DEFAULT NULL COMMENT 'Potencia en HP',
  `aceleracion` varchar(100) DEFAULT NULL COMMENT 'Aceleración 0-100 km/h',
  `velocidad_max` varchar(100) DEFAULT NULL COMMENT 'Velocidad máxima',
  `transmision` varchar(100) DEFAULT NULL COMMENT 'Tipo de transmisión',
  `traccion` varchar(100) DEFAULT NULL COMMENT 'Tipo de tracción',
  `consumo` varchar(100) DEFAULT NULL COMMENT 'Consumo (puede ser N/A)',
  `image_base` varchar(255) DEFAULT NULL COMMENT 'Nombre base de las imágenes (ej. car_15)',
  `image_extension` varchar(10) DEFAULT NULL COMMENT 'Extensión de las imágenes (ej. .webp)',
  `total_images` int(11) DEFAULT 0 COMMENT 'Cantidad total de imágenes',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() COMMENT 'Fecha de creación',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp() COMMENT 'Última actualización'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehicle_features`
--

CREATE TABLE `vehicle_features` (
  `id` int(11) NOT NULL,
  `vehicle_id` int(11) NOT NULL COMMENT 'ID del vehículo relacionado',
  `feature` text NOT NULL COMMENT 'Característica (ej. "Sistema de escape deportivo")'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indices de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `vehicle_features`
--
ALTER TABLE `vehicle_features`
  ADD PRIMARY KEY (`id`),
  ADD KEY `vehicle_id` (`vehicle_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT COMMENT 'Identificador único del vehículo';

--
-- AUTO_INCREMENT de la tabla `vehicle_features`
--
ALTER TABLE `vehicle_features`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `vehicle_features`
--
ALTER TABLE `vehicle_features`
  ADD CONSTRAINT `vehicle_features_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

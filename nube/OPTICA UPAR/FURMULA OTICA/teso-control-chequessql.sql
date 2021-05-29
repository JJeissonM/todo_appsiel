-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 07-05-2021 a las 13:06:18
-- Versión del servidor: 10.4.17-MariaDB
-- Versión de PHP: 7.3.27

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `appsiel_2020`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `teso_control_cheques`
--

CREATE TABLE `teso_control_cheques` (
  `id` int(10) UNSIGNED NOT NULL,
  `fuente` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tercero_id` int(10) UNSIGNED NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_cobro` date NOT NULL,
  `numero_cheque` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `referencia_cheque` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `entidad_financiera_id` int(10) UNSIGNED NOT NULL,
  `valor` double NOT NULL,
  `detalle` longtext COLLATE utf8_unicode_ci NOT NULL,
  `creado_por` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `modificado_por` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `core_tipo_transaccion_id_origen` int(10) UNSIGNED NOT NULL,
  `core_tipo_doc_app_id_origen` int(10) UNSIGNED NOT NULL,
  `consecutivo` int(11) NOT NULL,
  `core_tipo_transaccion_id_consumo` int(11) UNSIGNED NOT NULL,
  `core_tipo_doc_app_id_consumo` int(11) UNSIGNED NOT NULL,
  `consecutivo_doc_consumo` int(11) NOT NULL,
  `teso_caja_id` int(10) UNSIGNED NOT NULL,
  `tipo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `estado` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `teso_control_cheques`
--
ALTER TABLE `teso_control_cheques`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teso_control_cheques_tercero_id_index` (`tercero_id`),
  ADD KEY `teso_control_cheques_entidad_financiera_id_index` (`entidad_financiera_id`),
  ADD KEY `teso_control_cheques_core_tipo_transaccion_id_origen_index` (`core_tipo_transaccion_id_origen`),
  ADD KEY `teso_control_cheques_core_tipo_doc_app_id_origen_index` (`core_tipo_doc_app_id_origen`),
  ADD KEY `teso_control_cheques_teso_caja_id_index` (`teso_caja_id`),
  ADD KEY `core_tipo_transaccion_id_consumo` (`core_tipo_transaccion_id_consumo`),
  ADD KEY `core_tipo_doc_app_id_consumo` (`core_tipo_doc_app_id_consumo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `teso_control_cheques`
--
ALTER TABLE `teso_control_cheques`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

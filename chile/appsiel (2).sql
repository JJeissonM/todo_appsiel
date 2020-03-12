-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-03-2020 a las 15:08:08
-- Versión del servidor: 10.1.30-MariaDB
-- Versión de PHP: 7.2.2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `appsiel`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_aboutuses`
--

CREATE TABLE `pw_aboutuses` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `mision` text COLLATE utf8_unicode_ci,
  `vision` text COLLATE utf8_unicode_ci,
  `valores` text COLLATE utf8_unicode_ci,
  `imagen` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `resenia` longtext COLLATE utf8_unicode_ci NOT NULL,
  `mision_icono` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `vision_icono` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `valor_icono` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `resenia_icono` varchar(250) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_aboutuses`
--

INSERT INTO `pw_aboutuses` (`id`, `titulo`, `descripcion`, `mision`, `vision`, `valores`, `imagen`, `widget_id`, `created_at`, `updated_at`, `resenia`, `mision_icono`, `vision_icono`, `valor_icono`, `resenia_icono`) VALUES
(1, 'SERVNORT INGENIERÍA Y SERVICIOS', 'Es una empresa que ofrece un servicio integrado de diseño, fabricación, reparación, instalación, mantenimiento, automatización, modificaciones y asesoría para el sector industrial y minero en las áreas metal mecánica.', '<p>Ofrecer servicios profesionales de gestión y desarrollo de proyectos de reparación y mantención del área industrial y minera, trabajando de forma responsable y comprometida con nuestros clientes, con altos estándares de seguridad, calidad y medio ambiente y un gran respeto por la comunidad, lográndolo de forma sustentable, obteniendo los resultados esperados por nuestros accionistas de forma sostenible.</p>\r\n', '<p>Ser la empresa preferida por nuestros clientes en la innovación y desarrollo de nuestros productos y servicios en la II Región. Entregando soluciones diferenciadoras que agreguen valor a cada trabajo desarrollado, manteniendo y generando estrategias a largo plazo, con el fin de ser parte de la solución a sus necesidades en el servicio integrado de diseño, fabricación, reparación y mantenimiento, para el sector industrial y minero, basados en la responsabilidad social, calidad, seguridad, respeto al medioambiente y a las comunidades.</p>\r\n', '<p>SERVNORT se ha preocupado y encargado de sobremanera por nuestro personal, realizando un exhaustivo plan de selección, capacitación y certificación de todos nuestros trabajadores. Contamos además con un personal administrativo multidisciplinario, altamente calificado, profesional y comprometido con todos sus requerimientos y necesidades, con tiempos de respuesta cada vez mas breves, logrando así una atención y servicios cada vez mejores.</p>\r\n', 'http://localhost/Appsiel/img/1583921281servnort.jpg', 4, '2020-02-25 19:54:49', '2020-03-11 10:08:01', '', 'bank', 'bullseye', 'compass', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_albums`
--

CREATE TABLE `pw_albums` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `galeria_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_archivoitems`
--

CREATE TABLE `pw_archivoitems` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `file` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'VISIBLE',
  `archivo_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_archivoitems`
--

INSERT INTO `pw_archivoitems` (`id`, `titulo`, `descripcion`, `file`, `estado`, `archivo_id`, `created_at`, `updated_at`) VALUES
(5, '', '', 'pw-paginas-tabla-y-datossql-1583259908.txt', 'VISIBLE', 1, '2020-03-03 18:25:08', '2020-03-03 18:25:08'),
(6, '', '', 'boletines-del-curso-sexto-a-7pdf-1583259916.pdf', 'VISIBLE', 1, '2020-03-03 18:25:16', '2020-03-03 18:25:16'),
(7, '', '', 'boletines-del-curso-jardin-46pdf-1583259928.pdf', 'VISIBLE', 1, '2020-03-03 18:25:28', '2020-03-03 18:25:28'),
(8, '', '', 'boletines-del-curso-jardin-45pdf-1583259928.pdf', 'VISIBLE', 1, '2020-03-03 18:25:28', '2020-03-03 18:25:28'),
(11, '', '', 'boletines-del-curso-jardin-42pdf-1583259929.pdf', 'VISIBLE', 1, '2020-03-03 18:25:29', '2020-03-03 18:25:29'),
(12, 'Php', '', 'indexphp-1583777843.bin', 'VISIBLE', 1, '2020-03-09 18:17:24', '2020-03-09 18:17:24');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_archivos`
--

CREATE TABLE `pw_archivos` (
  `id` int(10) UNSIGNED NOT NULL,
  `formato` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_archivos`
--

INSERT INTO `pw_archivos` (`id`, `formato`, `titulo`, `descripcion`, `widget_id`, `created_at`, `updated_at`) VALUES
(1, '', 'Documentos y archivos', 'EL SUSCRITO REPRESENTANTE LEGAL DE nombre_entidad Da a conocer a la sociedad civil la siguiente información para que sea de conocimiento público y de ser el caso se reciban comentarios a cerca de nuestra solicitud; de conformidad con el numeral 3 artículo', 11, '2020-03-03 14:26:05', '2020-03-03 14:26:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_articles`
--

CREATE TABLE `pw_articles` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(250) COLLATE utf8_unicode_ci NOT NULL,
  `contenido` text COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `estado` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'VISIBLE',
  `articlesetup_id` int(10) UNSIGNED NOT NULL,
  `imagen` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_articles`
--

INSERT INTO `pw_articles` (`id`, `titulo`, `contenido`, `descripcion`, `estado`, `articlesetup_id`, `imagen`, `created_at`, `updated_at`) VALUES
(2, 'Chuleta de cerdo', '<p><span class=\"marker\">se toma el pollo</span></p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p><span class=\"marker\">cerdo</span></p>\r\n', '', 'VISIBLE', 1, 'img/articles/1583257861foto 1.jpg', '2020-02-29 19:24:22', '2020-03-03 17:51:39'),
(3, 'Los cuentos de pepe', '<h1>Pepe castro: antes y despu&eacute;s</h1>\r\n\r\n<pre>\r\nEl valle del cacique Upar.</pre>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>Sin formato</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', '', 'OCULTO', 1, '', '2020-02-29 19:25:11', '2020-02-29 19:27:27'),
(4, 'diagrama sistema de gestion educativa', '<h1>Diagrama sistema de gestion educativa</h1>\r\n', '', 'VISIBLE', 1, '', '2020-03-03 17:20:03', '2020-03-03 17:20:03'),
(5, 'La vie rose', '<h1>La vie rose</h1>\r\n\r\n<p>Normal</p>\r\n', '', 'VISIBLE', 1, '', '2020-03-03 17:23:31', '2020-03-03 17:23:31'),
(17, 'sistema', '<p>asdfasfds</p>\r\n', '', 'VISIBLE', 1, 'img/articles/1583257275diagrama sistema de gestion educativa.png', '2020-03-03 17:41:14', '2020-03-03 17:41:15'),
(18, 'AVIPOULET', '<p>AVIPULET</p>\r\n', '', 'VISIBLE', 1, 'img/articles1583257543diagrama sistema de gestion educativa.png', '2020-03-03 17:41:41', '2020-03-03 17:45:43'),
(19, 'Diagrama sistema de gestion educativa', '<h2>Diagrama sistema de gestion educativa</h2>\r\n', '', 'VISIBLE', 1, 'img/articles/1583257964diagrama sistema de gestion educativa.png', '2020-03-03 17:52:44', '2020-03-03 17:52:44'),
(20, 'Chuleta de cerdo', '<p>dasdsada</p>\r\n\r\n<p>asdd</p>\r\n\r\n<p>as</p>\r\n\r\n<p>d</p>\r\n\r\n<p>asd</p>\r\n\r\n<p>as</p>\r\n\r\n<p>dasd</p>\r\n', '', 'VISIBLE', 1, 'img/articles/1583258042diagrama sistema de gestion educativa.png', '2020-03-03 17:54:02', '2020-03-03 17:54:02'),
(21, 'Propuesta APPSIEL', '<p>AFSADFDSF</p>\r\n\r\n<p>SF</p>\r\n\r\n<p>SD</p>\r\n\r\n<p>F</p>\r\n\r\n<p>DS</p>\r\n\r\n<p>FSD</p>\r\n', '', 'VISIBLE', 1, 'img/articles/propuesta-appsielpng-1583259703.png', '2020-03-03 17:55:02', '2020-03-03 18:21:43'),
(22, 'Horario', '<p>Horario</p>\r\n', 'Mi Horario', 'VISIBLE', 1, 'img/articles/student-849821-1920jpg-1583259668.jpeg', '2020-03-03 18:06:30', '2020-03-03 19:04:15'),
(23, 'Blog', '<p>assafds</p>\r\n', 'Mi blog ', 'VISIBLE', 1, 'img/articles/vale-uniformejpg-1583262792.jpeg', '2020-03-03 19:01:23', '2020-03-03 19:13:12'),
(24, 'La tierra', '<p>Nueva historia</p>\r\n', 'Muy bien asdfdsds sdf dsf dsf sd f ds f ds fds fds f ds f dsasdfdsds sdf dsf dsf sd f ds f ds fds fds f ds f dsasdfdsds sdf dsf dsf sd f ds f ds fds fds f ds f dsasdfdsds sdf dsf dsf sd f ds f ds fds fds f ds f ', 'VISIBLE', 1, 'img/articles/student-849821-1920jpg-1583262718.jpeg', '2020-03-03 19:11:58', '2020-03-03 19:12:14'),
(25, 'MANGUERAS HIDRÁULICAS', '<p>Con una l&iacute;nea de mangueras hidr&aacute;ulicas de calidad que cumplen y exceden las normas internacionales tales como SAE, ISO,DIN, RMA Y FDA.<br />\r\nNuestra eficiencia con nuestros servicios t&eacute;cnicos y productos de alta calidad.</p>\r\n', 'Con una línea de mangueras hidráulicas de calidad que cumplen y exceden las normas internacionales tales como SAE, ISO,DIN, RMA Y FDA.\r\nNuestra eficiencia con nuestros servicios técnicos y productos de alta calidad.', 'VISIBLE', 2, 'img/articles/img1jpg-1583922048.jpeg', '2020-03-11 00:23:02', '2020-03-11 10:55:51'),
(26, 'MANGUERAS INDUSTRIALES', '', 'Una gama de mangueras flexible y mezclas en caucho, naturales y sintéticos para uso industrial la variedad de empleo de la manguera es muy amplia y variada. ', 'VISIBLE', 2, '', '2020-03-11 10:31:57', '2020-03-11 10:31:57'),
(27, 'ACOPLES', '<p>Permanentes y reutilizables para sistemas hidr&aacute;ulicos de:</p>\r\n\r\n<ul>\r\n	<li>Baja</li>\r\n	<li>Media</li>\r\n	<li>Alta y muy alta presi&oacute;n</li>\r\n</ul>\r\n\r\n<p>Accesorios importados de muy alta calidad y rendimientos en la industria hidr&aacute;ulica.</p>\r\n\r\n<p>&nbsp;</p>\r\n\r\n<p>&nbsp;</p>\r\n', 'Accesorios importados de muy alta calidad y rendimientos en la industria hidráulica.', 'VISIBLE', 2, 'img/articles/acoples3png-1583923446.png', '2020-03-11 10:44:06', '2020-03-11 10:44:06'),
(28, 'ADAPTADORES HIDRAULICOS', '<p>Materiales altamente resistentes dise&ntilde;ados para trabajos de mediana, alta y extrema presi&oacute;n utilizados para el acoplamiento de mangueras. Industriales e hidr&aacute;ulicas,ca&ntilde;er&iacute;as, bombas neum&aacute;ticas y automotrices e otros importados de muy alta calidad y rendimientos en la industria hidr&aacute;ulica.</p>\r\n', 'Materiales altamente resistentes diseñados para trabajos de mediana, alta y extrema presión utilizados para el acoplamiento de mangueras. Industriales e hidráulicas,cañerías, bombas neumáticas y automotrices.', 'VISIBLE', 2, 'img/articles/adaptadoreshidraulicospng-1583924527.png', '2020-03-11 11:02:07', '2020-03-11 11:02:07'),
(29, 'VÁLVULAS HIDRÁULICAS E INDUSTRIALES', '<p>V&aacute;lvulas de esfera, paso total con accionamiento manual con cuerpo de bronce cromados y acero inoxidable AISI 316</p>\r\n\r\n<p>de 2 y 3 cuerpos de PN20,1000WOG y 500BAR</p>\r\n', 'Válvulas de esfera, paso total con accionamiento manual con cuerpo de bronce cromados y acero inoxidable AISI 316  de 2 y 3 cuerpos de PN20,1000WOG y 500BAR', 'VISIBLE', 2, 'img/articles/valvulaspng-1583924722.png', '2020-03-11 11:05:22', '2020-03-11 11:05:22'),
(31, 'lÍNEA NEUMÁTICA', '<p>Ideal para la aplicaci&oacute;n en la miner&iacute;a industrial, mec&aacute;nica, construcci&oacute;n, automotriz, petroleo y equipo de compresiones de aires agua, etc.&nbsp;</p>\r\n', 'Ideal para la aplicación en la minería industrial, mecánica, construcción, automotriz, petroleo y equipo de compresiones de aires agua, etc. ', 'VISIBLE', 2, 'img/articles/lineaneumaticapng-1583925547.png', '2020-03-11 11:19:06', '2020-03-11 11:19:07'),
(32, 'CONEXIONES HIDRÁULICAS', '<p>Utilizados para mangueras de 1,2,4 y 6 espirales de acero como lo son las mangueras R1,R2,4SH,R15, ideales para las aplicaciones en la miner&iacute;a de material altamente resistente para trabajo de mediana, alta y extrema presi&oacute;n.</p>\r\n', 'Utilizados para mangueras de 1,2,4 y 6 espirales de acero como lo son las mangueras R1,R2,4SH,R15, ideales para las aplicaciones en la minería de material altamente resistente para trabajo de mediana, alta y extrema presión.', 'VISIBLE', 2, 'img/articles/conexioneshidraulicaspng-1583925767.png', '2020-03-11 11:21:42', '2020-03-11 11:22:47'),
(33, 'CONEXIONES PARA TUBOS HIDRÁULICOS', '<p>Se utilizan en los sistemas hidr&aacute;ulicos para unir el tubo en los diferentes componentes del sistema unir tubo con tubo, curva de 90&deg;, diversiones, etc.</p>\r\n', 'Se utilizan en los sistemas hidráulicos para unir el tubo en los diferentes componentes del sistema unir tubo con tubo, curva de 90°, diversiones, etc.', 'VISIBLE', 2, 'img/articles/conexionesparatuboshidraulicospng-1583926061.png', '2020-03-11 11:27:41', '2020-03-11 11:27:41'),
(34, 'SISTEMA DE TESTEO', '<p>Dise&ntilde;ado para monitorias y controlar la presi&oacute;n de los sistemas hidr&aacute;ulicos en todo los niveles de presi&oacute;n del sistema.</p>\r\n', 'Diseñado para monitorias y controlar la presión de los sistemas hidráulicos en todo los niveles de presión del sistema.', 'VISIBLE', 2, 'img/articles/sistemetesteopng-1583926408.png', '2020-03-11 11:33:27', '2020-03-11 11:33:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_articlesetups`
--

CREATE TABLE `pw_articlesetups` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `formato` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'LISTA',
  `orden` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'ASC',
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_articlesetups`
--

INSERT INTO `pw_articlesetups` (`id`, `titulo`, `descripcion`, `formato`, `orden`, `widget_id`, `created_at`, `updated_at`) VALUES
(1, 'Noticias', '', 'LISTA', 'ASC', 5, '2020-02-29 19:22:48', '2020-03-05 10:39:15'),
(2, 'Productos', '', 'BLOG', 'ASC', 21, '2020-03-11 00:21:54', '2020-03-11 10:16:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_categorias`
--

CREATE TABLE `pw_categorias` (
  `id` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `estado` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `creado_por` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `modificado_por` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_categorias`
--

INSERT INTO `pw_categorias` (`id`, `descripcion`, `estado`, `creado_por`, `modificado_por`, `created_at`, `updated_at`) VALUES
(1, 'Quienes somos', 'Activo', 'administrator@appsiel.com.co', 'administrator@appsiel.com.co', '2019-07-14 16:27:51', '2019-07-14 16:27:51'),
(2, 'Noticias', 'Activo', 'administrator@appsiel.com.co', 'administrator@appsiel.com.co', '2019-07-14 16:27:59', '2019-07-14 16:27:59'),
(3, 'General', 'Activo', 'administrator@appsiel.com.co', 'administrator@appsiel.com.co', '2019-07-16 00:21:54', '2019-07-16 00:21:54'),
(4, 'Catálogos', 'Activo', 'administrator@appsiel.com.co', 'administrator@appsiel.com.co', '2019-07-24 22:20:44', '2019-07-24 22:20:44');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_categoria_footer`
--

CREATE TABLE `pw_categoria_footer` (
  `id` int(10) UNSIGNED NOT NULL,
  `texto` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `footer_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_categoria_footer`
--

INSERT INTO `pw_categoria_footer` (`id`, `texto`, `footer_id`, `created_at`, `updated_at`) VALUES
(3, 'ACCESO RÁPIDO', 1, '2020-03-05 14:54:50', '2020-03-05 14:56:53'),
(4, 'EMPRESA', 1, '2020-03-05 14:57:14', '2020-03-11 11:51:01'),
(5, 'INFORMACIÓN', 1, '2020-03-05 14:57:33', '2020-03-11 11:45:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_clientes`
--

CREATE TABLE `pw_clientes` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_clientes`
--

INSERT INTO `pw_clientes` (`id`, `nombre`, `logo`, `widget_id`, `created_at`, `updated_at`) VALUES
(1, 'MINETEC', 'img/1583855325minitec.png', 20, '2020-03-10 15:48:45', '2020-03-10 15:48:45'),
(2, 'CENTINELA', 'img/1583855345centinela.jpg', 20, '2020-03-10 15:49:05', '2020-03-10 15:49:05'),
(3, 'CONYMET', 'http://localhost/Appsiel/img/1583855442conymet.jpg', 20, '2020-03-10 15:49:24', '2020-03-10 15:50:42'),
(4, 'SIERRA GORDA SCM', 'http://localhost/Appsiel/img/1583855660sierragorda.jpg', 20, '2020-03-10 15:51:12', '2020-03-10 15:54:20'),
(5, 'ULTRAPORT', 'img/1583858202ultraport.jpg', 20, '2020-03-10 16:36:42', '2020-03-10 16:36:42'),
(6, 'LOMAS BAYAS', 'img/1583858656lomasbayas.jpg', 20, '2020-03-10 16:44:16', '2020-03-10 16:44:16'),
(7, 'MINERA GABY S.A', 'img/1583858834minergaby.PNG', 20, '2020-03-10 16:47:14', '2020-03-10 16:47:14'),
(8, 'CODELCO', 'http://localhost/Appsiel/img/1583859741codelco.png', 20, '2020-03-10 17:01:46', '2020-03-10 17:02:21');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_comentarios`
--

CREATE TABLE `pw_comentarios` (
  `id` int(10) UNSIGNED NOT NULL,
  `contenido` longtext COLLATE utf8_unicode_ci NOT NULL,
  `user_id` int(10) UNSIGNED NOT NULL,
  `articulo_id` int(10) UNSIGNED NOT NULL,
  `estado` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `creado_por` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `modificado_por` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_componente`
--

CREATE TABLE `pw_componente` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `path_componente` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_configuracion_general`
--

CREATE TABLE `pw_configuracion_general` (
  `id` int(10) UNSIGNED NOT NULL,
  `color_primario` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `color_segundario` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `color_terciario` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_configuracion_general`
--

INSERT INTO `pw_configuracion_general` (`id`, `color_primario`, `color_segundario`, `color_terciario`, `created_at`, `updated_at`) VALUES
(2, '#0f70b4', '#ffffff', '#c0c0c0', '2020-03-09 19:44:09', '2020-03-10 14:35:03');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_contactenos`
--

CREATE TABLE `pw_contactenos` (
  `id` int(10) UNSIGNED NOT NULL,
  `empresa` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `telefono` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `ciudad` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `correo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `direccion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_contactenos`
--

INSERT INTO `pw_contactenos` (`id`, `empresa`, `telefono`, `ciudad`, `correo`, `direccion`, `widget_id`, `created_at`, `updated_at`) VALUES
(1, 'SERVNORT INGENIERIA', '56979648076', '', 'Alexander.romero@servnort.cl', 'av. Pedro Aguirre cerda 6995', 9, '2020-02-29 19:43:42', '2020-03-11 11:40:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_enlace_footer`
--

CREATE TABLE `pw_enlace_footer` (
  `id` int(10) UNSIGNED NOT NULL,
  `enlace` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `texto` varchar(60) COLLATE utf8_unicode_ci NOT NULL,
  `icono` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
  `categoria_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_enlace_footer`
--

INSERT INTO `pw_enlace_footer` (`id`, `enlace`, `texto`, `icono`, `categoria_id`, `created_at`, `updated_at`) VALUES
(19, '', 'Teléfono: +569 79648076', '', 5, '2020-03-11 11:45:59', '2020-03-11 11:45:59'),
(21, '', 'Email:', '', 5, '2020-03-11 11:46:37', '2020-03-11 11:46:37'),
(22, '', 'Alexander.romero@servnort.cl', '', 5, '2020-03-11 11:47:11', '2020-03-11 11:47:11'),
(24, '', 'Razón social: venky Karen Godoy Carvajal', '', 4, '2020-03-11 11:49:24', '2020-03-11 11:49:24'),
(25, '', 'Nombre fantasia: servnort ingenieria', '', 4, '2020-03-11 11:49:59', '2020-03-11 11:49:59'),
(26, '', 'Rut: 16438595-7', '', 4, '2020-03-11 11:50:40', '2020-03-11 11:50:40'),
(27, '', 'Dirección:', '', 5, '2020-03-11 11:51:47', '2020-03-11 11:51:47'),
(28, '', 'Av. Pedro Aguirre cerda 6995', '', 5, '2020-03-11 11:51:56', '2020-03-11 11:51:56'),
(29, 'http://www.servnort.cl/wp-content/uploads/sites/2255/2019/10/307682-v1.pdf', 'POLÍTICAS DE CALIDAD', '', 3, '2020-03-11 11:56:12', '2020-03-11 11:56:12'),
(31, 'http://www.servnort.cl/wp-content/uploads/sites/2255/2017/09/Presentaci%C3%B3n-SERVNORT-2017.pdf', 'SOBRE NOSOTROS', '', 3, '2020-03-11 12:06:40', '2020-03-11 12:06:40'),
(33, 'http://www.servnort.cl/wp-content/uploads/sites/2255/2017/09/Presentaci%C3%B3n-SERVNORT-2017.pdf', 'Antofagasta - Chile', '', 5, '2020-03-11 12:12:17', '2020-03-11 12:12:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_footer`
--

CREATE TABLE `pw_footer` (
  `id` int(10) UNSIGNED NOT NULL,
  `ubicacion` longtext COLLATE utf8_unicode_ci NOT NULL,
  `copyright` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `texto` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `background` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `color` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_footer`
--

INSERT INTO `pw_footer` (`id`, `ubicacion`, `copyright`, `texto`, `background`, `color`, `created_at`, `updated_at`) VALUES
(1, '<iframe src=\"https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3655.940811750934!2d-70.39109248538277!3d-23.60645556914346!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x96ae2ad96603da23%3A0x205bcc52b47a0f85!2sAv.%20Pedro%20Aguirre%20Cerda%206995%2C%20Antofagasta%2C%20Chile!5e0!3m2!1ses-419!2scz!4v1583851000676!5m2!1ses-419!2scz\" width=\"300\" height=\"450\" frameborder=\"0\" style=\"border:0;\" allowfullscreen=\"\"></iframe>', 'APPSIEL S.A.S. 2020', 'Desarrollado por ', '#0f70b4', '#ffffff', '2020-03-03 15:53:25', '2020-03-11 02:11:27');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_formcontactenos`
--

CREATE TABLE `pw_formcontactenos` (
  `id` int(10) UNSIGNED NOT NULL,
  `names` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `subject` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `message` text COLLATE utf8_unicode_ci NOT NULL,
  `state` varchar(50) COLLATE utf8_unicode_ci NOT NULL DEFAULT 'UNREAD',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_formcontactenos`
--

INSERT INTO `pw_formcontactenos` (`id`, `names`, `email`, `subject`, `message`, `state`, `created_at`, `updated_at`) VALUES
(1, '', 'colonca1999@gmail.com', 'Urgente', 'camiloasas', 'READ', '2020-03-05 13:04:43', '2020-03-05 13:05:08'),
(2, '', 'colonca1999@gmail.com', 'Información', 'loas asjal asas', 'READ', '2020-03-05 18:21:18', '2020-03-05 18:21:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_fotos`
--

CREATE TABLE `pw_fotos` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `album_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_galerias`
--

CREATE TABLE `pw_galerias` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_icons`
--

CREATE TABLE `pw_icons` (
  `id` int(10) UNSIGNED NOT NULL,
  `icono` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_icons`
--

INSERT INTO `pw_icons` (`id`, `icono`, `created_at`, `updated_at`) VALUES
(1, 'user', '2020-02-21 10:00:00', '2020-02-21 10:00:00'),
(3, 'address-book', NULL, NULL),
(4, 'address-book-o', NULL, '2020-02-21 22:03:12'),
(5, 'address-card', NULL, '2020-02-21 22:03:12'),
(6, 'address-card-o', NULL, '2020-02-21 22:03:12'),
(7, 'bandcamp', NULL, '2020-02-21 22:03:12'),
(8, 'bath', NULL, '2020-02-21 22:03:12'),
(9, 'bathtub', NULL, '2020-02-21 22:03:12'),
(10, 'drivers-license', NULL, '2020-02-21 22:03:12'),
(11, 'drivers-license-o', NULL, '2020-02-21 22:03:12'),
(12, 'eercast', NULL, '2020-02-21 22:03:12'),
(13, 'envelope-open', NULL, '2020-02-21 22:03:12'),
(14, 'envelope-open-o', NULL, '2020-02-21 22:03:12'),
(15, 'etsy', NULL, '2020-02-21 22:03:12'),
(16, 'free-code-camp', NULL, '2020-02-21 22:03:12'),
(17, 'grav', NULL, '2020-02-21 22:03:12'),
(18, 'handshake-o', NULL, '2020-02-21 22:03:12'),
(19, 'id-badge', NULL, '2020-02-21 22:03:12'),
(20, 'id-card', NULL, '2020-02-21 22:03:12'),
(21, 'id-card-o', NULL, '2020-02-21 22:03:12'),
(22, 'imdb', NULL, '2020-02-21 22:03:12'),
(23, 'linode', NULL, '2020-02-21 22:03:12'),
(24, 'meetup', NULL, '2020-02-21 22:03:12'),
(25, 'microchip', NULL, '2020-02-21 22:03:12'),
(26, 'battery-1', NULL, '2020-02-21 22:03:12'),
(27, 'battery-2', NULL, '2020-02-21 22:03:12'),
(28, 'battery-3', NULL, '2020-02-21 22:03:12'),
(29, 'battery-4', NULL, '2020-02-21 22:03:12'),
(30, 'battery-empty', NULL, '2020-02-21 22:03:12'),
(31, 'battery-full', NULL, '2020-02-21 22:03:12'),
(32, 'battery-half', NULL, '2020-02-21 22:03:12'),
(33, 'battery-quarter', NULL, '2020-02-21 22:03:12'),
(34, 'battery-three-quarters', NULL, '2020-02-21 22:03:12'),
(35, 'bed', NULL, '2020-02-21 22:03:12'),
(36, 'beer', NULL, '2020-02-21 22:03:12'),
(37, 'bell', NULL, '2020-02-21 22:03:12'),
(38, 'bell-o', NULL, '2020-02-21 22:03:12'),
(39, 'bell-slash', NULL, '2020-02-21 22:03:12'),
(40, 'bell-slash-o', NULL, '2020-02-21 22:03:12'),
(41, 'bicycle', NULL, '2020-02-21 22:03:12'),
(42, 'binoculars', NULL, '2020-02-21 22:03:12'),
(43, 'birthday-cake', NULL, '2020-02-21 22:03:12'),
(44, 'blind', NULL, '2020-02-21 22:03:12'),
(45, 'bluetooth', NULL, '2020-02-21 22:03:12'),
(46, 'bluetooth-b', NULL, '2020-02-21 22:03:12'),
(47, 'bolt', NULL, '2020-02-21 22:03:12'),
(48, 'bomb', NULL, '2020-02-21 22:03:12'),
(49, 'book', NULL, '2020-02-21 22:03:12'),
(50, 'bookmark', NULL, '2020-02-21 22:03:12'),
(51, 'bookmark-o', NULL, '2020-02-21 22:03:12'),
(52, 'braille', NULL, '2020-02-21 22:03:12'),
(53, 'briefcase', NULL, '2020-02-21 22:03:12'),
(54, 'bug', NULL, '2020-02-21 22:03:12'),
(55, 'building', NULL, '2020-02-21 22:03:12'),
(56, 'building-o', NULL, '2020-02-21 22:03:12'),
(57, 'bullhorn', NULL, '2020-02-21 22:03:12'),
(58, 'bullseye', NULL, '2020-02-21 22:03:12'),
(59, 'bus', NULL, '2020-02-21 22:03:12'),
(60, 'cab', NULL, '2020-02-21 22:03:12'),
(61, 'calculator', NULL, '2020-02-21 22:03:12'),
(62, 'calendar', NULL, '2020-02-21 22:03:12'),
(63, 'calendar-check-o', NULL, '2020-02-21 22:03:12'),
(64, 'calendar-minus-o', NULL, '2020-02-21 22:03:12'),
(65, 'calendar-o', NULL, '2020-02-21 22:03:12'),
(66, 'calendar-plus-o', NULL, '2020-02-21 22:03:12'),
(67, 'calendar-times-o', NULL, '2020-02-21 22:03:12'),
(68, 'camera', NULL, '2020-02-21 22:03:12'),
(69, 'camera-retro', NULL, '2020-02-21 22:03:12'),
(70, 'car', NULL, '2020-02-21 22:03:12'),
(71, 'caret-square-o-down', NULL, '2020-02-21 22:03:12'),
(72, 'thermometer-3', NULL, '2020-02-21 22:03:12'),
(73, 'thermometer-4', NULL, '2020-02-21 22:03:12'),
(74, 'thermometer-empty', NULL, '2020-02-21 22:03:12'),
(75, 'thermometer-full', NULL, '2020-02-21 22:03:12'),
(76, 'thermometer-half', NULL, '2020-02-21 22:03:12'),
(77, 'thermometer-quarter', NULL, '2020-02-21 22:03:12'),
(78, 'thermometer-three-quarters', NULL, '2020-02-21 22:03:12'),
(79, 'times-rectangle', NULL, '2020-02-21 22:03:12'),
(80, 'times-rectangle-o', NULL, '2020-02-21 22:03:12'),
(81, 'user-circle', NULL, '2020-02-21 22:03:12'),
(82, 'user-circle-o', NULL, '2020-02-21 22:03:12'),
(83, 'user-o', NULL, '2020-02-21 22:03:12'),
(84, 'vcard', NULL, '2020-02-21 22:03:12'),
(85, 'vcard-o', NULL, '2020-02-21 22:03:12'),
(86, 'window-close', NULL, '2020-02-21 22:03:12'),
(87, 'window-close-o', NULL, '2020-02-21 22:03:12'),
(88, 'window-maximize', NULL, '2020-02-21 22:03:12'),
(89, 'window-minimize', NULL, '2020-02-21 22:03:12'),
(90, 'window-restore', NULL, '2020-02-21 22:03:12'),
(91, 'wpexplorer', NULL, '2020-02-21 22:03:12'),
(92, 'address-book', NULL, '2020-02-21 22:03:12'),
(93, 'address-book-o', NULL, '2020-02-21 22:03:12'),
(94, 'address-card', NULL, '2020-02-21 22:03:12'),
(95, 'address-card-o', NULL, '2020-02-21 22:03:12'),
(96, 'adjust', NULL, '2020-02-21 22:03:12'),
(97, 'american-sign-language-interpreting', NULL, '2020-02-21 22:03:12'),
(98, 'anchor', NULL, '2020-02-21 22:03:12'),
(99, 'archive', NULL, '2020-02-21 22:03:12'),
(100, 'area-chart', NULL, '2020-02-21 22:03:12'),
(101, 'arrows', NULL, '2020-02-21 22:03:12'),
(102, 'arrows-h', NULL, '2020-02-21 22:03:12'),
(103, 'arrows-v', NULL, '2020-02-21 22:03:12'),
(104, 'asl-interpreting', NULL, '2020-02-21 22:03:12'),
(105, 'thermometer-3', NULL, '2020-02-21 22:03:12'),
(106, 'thermometer-4', NULL, '2020-02-21 22:03:12'),
(107, 'thermometer-empty', NULL, '2020-02-21 22:03:12'),
(108, 'thermometer-full', NULL, '2020-02-21 22:03:12'),
(109, 'thermometer-half', NULL, '2020-02-21 22:03:12'),
(110, 'thermometer-quarter', NULL, '2020-02-21 22:03:12'),
(111, 'thermometer-three-quarters', NULL, '2020-02-21 22:03:12'),
(112, 'times-rectangle', NULL, '2020-02-21 22:03:12'),
(113, 'times-rectangle-o', NULL, '2020-02-21 22:03:12'),
(114, 'user-circle', NULL, '2020-02-21 22:03:12'),
(115, 'user-circle-o', NULL, '2020-02-21 22:03:12'),
(116, 'user-o', NULL, '2020-02-21 22:03:12'),
(117, 'vcard', NULL, '2020-02-21 22:03:12'),
(118, 'vcard-o', NULL, '2020-02-21 22:03:12'),
(119, 'window-close', NULL, '2020-02-21 22:03:12'),
(120, 'window-close-o', NULL, '2020-02-21 22:03:12'),
(121, 'window-maximize', NULL, '2020-02-21 22:03:12'),
(122, 'window-minimize', NULL, '2020-02-21 22:03:12'),
(123, 'window-restore', NULL, '2020-02-21 22:03:12'),
(124, 'wpexplorer', NULL, '2020-02-21 22:03:12'),
(125, 'address-book', NULL, '2020-02-21 22:03:12'),
(126, 'address-book-o', NULL, '2020-02-21 22:03:12'),
(127, 'address-card', NULL, '2020-02-21 22:03:12'),
(128, 'address-card-o', NULL, '2020-02-21 22:03:12'),
(129, 'adjust', NULL, '2020-02-21 22:03:12'),
(130, 'american-sign-language-interpreting', NULL, '2020-02-21 22:03:12'),
(131, 'anchor', NULL, '2020-02-21 22:03:12'),
(132, 'archive', NULL, '2020-02-21 22:03:12'),
(133, 'area-chart', NULL, '2020-02-21 22:03:12'),
(134, 'arrows', NULL, '2020-02-21 22:03:12'),
(135, 'arrows-h', NULL, '2020-02-21 22:03:12'),
(136, 'arrows-v', NULL, '2020-02-21 22:03:12'),
(137, 'asl-interpreting', NULL, '2020-02-21 22:03:12'),
(138, 'assistive-listening-systems', NULL, '2020-02-21 22:03:12'),
(139, 'asterisk', NULL, '2020-02-21 22:03:12'),
(140, 'at', NULL, '2020-02-21 22:03:12'),
(141, 'audio-description', NULL, '2020-02-21 22:03:12'),
(142, 'automobile', NULL, '2020-02-21 22:03:12'),
(143, 'balance-scale', NULL, '2020-02-21 22:03:12'),
(144, 'ban', NULL, '2020-02-21 22:03:12'),
(145, 'bank', NULL, '2020-02-21 22:03:12'),
(146, 'bar-chart', NULL, '2020-02-21 22:03:12'),
(147, 'bar-chart-o', NULL, '2020-02-21 22:03:12'),
(148, 'barcode', NULL, '2020-02-21 22:03:12'),
(149, 'bars', NULL, '2020-02-21 22:03:12'),
(150, 'bath', NULL, '2020-02-21 22:03:12'),
(151, 'bathtub', NULL, '2020-02-21 22:03:12'),
(152, 'battery', NULL, '2020-02-21 22:03:12'),
(153, 'battery-0', NULL, '2020-02-21 22:03:12'),
(154, 'caret-square-o-left', NULL, '2020-02-21 22:03:12'),
(155, 'caret-square-o-right', NULL, '2020-02-21 22:03:12'),
(156, 'caret-square-o-up', NULL, '2020-02-21 22:03:13'),
(157, 'cart-arrow-down', NULL, '2020-02-21 22:03:13'),
(158, 'cart-plus', NULL, '2020-02-21 22:03:13'),
(159, 'cc', NULL, '2020-02-21 22:03:13'),
(160, 'certificate', NULL, '2020-02-21 22:03:13'),
(161, 'check', NULL, '2020-02-21 22:03:13'),
(162, 'check-circle', NULL, '2020-02-21 22:03:13'),
(163, 'check-circle-o', NULL, '2020-02-21 22:03:13'),
(164, 'check-square', NULL, '2020-02-21 22:03:13'),
(165, 'check-square-o', NULL, '2020-02-21 22:03:13'),
(166, 'child', NULL, '2020-02-21 22:03:13'),
(167, 'circle', NULL, '2020-02-21 22:03:13'),
(168, 'circle-o', NULL, '2020-02-21 22:03:13'),
(169, 'circle-o-notch', NULL, '2020-02-21 22:03:13'),
(170, 'circle-thin', NULL, '2020-02-21 22:03:13'),
(171, 'clock-o', NULL, '2020-02-21 22:03:13'),
(172, 'clone', NULL, '2020-02-21 22:03:13'),
(173, 'close', NULL, '2020-02-21 22:03:13'),
(174, 'cloud', NULL, '2020-02-21 22:03:13'),
(175, 'cloud-download', NULL, '2020-02-21 22:03:13'),
(176, 'cloud-upload', NULL, '2020-02-21 22:03:13'),
(177, 'code', NULL, '2020-02-21 22:03:13'),
(178, 'code-fork', NULL, '2020-02-21 22:03:13'),
(180, 'cog', NULL, '2020-02-21 22:03:13'),
(181, 'cogs', NULL, '2020-02-21 22:03:13'),
(182, 'comment', NULL, '2020-02-21 22:03:13'),
(183, 'comment-o', NULL, '2020-02-21 22:03:13'),
(184, 'commenting', NULL, '2020-02-21 22:03:13'),
(185, 'commenting-o', NULL, '2020-02-21 22:03:13'),
(186, 'comments', NULL, '2020-02-21 22:03:13'),
(187, 'comments-o', NULL, '2020-02-21 22:03:13'),
(188, 'compass', NULL, '2020-02-21 22:03:13'),
(189, 'copyright', NULL, '2020-02-21 22:03:13'),
(190, 'creative-commons', NULL, '2020-02-21 22:03:13'),
(191, 'podcast', NULL, '2020-02-21 22:03:13'),
(192, 'quora', NULL, '2020-02-21 22:03:13'),
(193, 'ravelry', NULL, '2020-02-21 22:03:13'),
(194, 's15', NULL, '2020-02-21 22:03:13'),
(195, 'thermometer-0', NULL, '2020-02-21 22:03:13'),
(196, 'thermometer-1', NULL, '2020-02-21 22:03:13'),
(197, 'thermometer-2', NULL, '2020-02-21 22:03:13'),
(198, 'credit-card', NULL, '2020-02-21 22:03:13'),
(199, 'credit-card-alt', NULL, '2020-02-21 22:03:13'),
(200, 'crop', NULL, '2020-02-21 22:03:13'),
(201, 'crosshairs', NULL, '2020-02-21 22:03:13'),
(202, 'cube', NULL, '2020-02-21 22:03:13'),
(203, 'cubes', NULL, '2020-02-21 22:03:13'),
(204, 'cutlery', NULL, '2020-02-21 22:03:13'),
(205, 'dashboard', NULL, '2020-02-21 22:03:13'),
(206, 'database', NULL, '2020-02-21 22:03:13'),
(207, 'deaf', NULL, '2020-02-21 22:03:13'),
(208, 'deafness', NULL, '2020-02-21 22:03:13'),
(209, 'desktop', NULL, '2020-02-21 22:03:13'),
(210, 'diamond', NULL, '2020-02-21 22:03:13'),
(211, 'dot-circle-o', NULL, '2020-02-21 22:03:13'),
(212, 'download', NULL, '2020-02-21 22:03:13'),
(213, 'drivers-license', NULL, '2020-02-21 22:03:13'),
(214, 'drivers-license-o', NULL, '2020-02-21 22:03:13'),
(215, 'edit', NULL, '2020-02-21 22:03:13'),
(216, 'ellipsis-h', NULL, '2020-02-21 22:03:13'),
(217, 'ellipsis-v', NULL, '2020-02-21 22:03:13'),
(218, 'envelope', NULL, '2020-02-21 22:03:13'),
(219, 'envelope-o', NULL, '2020-02-21 22:03:13'),
(220, 'envelope-open', NULL, '2020-02-21 22:03:13'),
(221, 'envelope-open-o', NULL, '2020-02-21 22:03:13'),
(222, 'envelope-square', NULL, '2020-02-21 22:03:13'),
(223, 'eraser', NULL, '2020-02-21 22:03:13'),
(224, 'exchange', NULL, '2020-02-21 22:03:13'),
(225, 'exclamation', NULL, '2020-02-21 22:03:13'),
(226, 'exclamation-circle', NULL, '2020-02-21 22:03:13'),
(227, 'exclamation-triangle', NULL, '2020-02-21 22:03:13'),
(228, 'external-link', NULL, '2020-02-21 22:03:13'),
(229, 'external-link-square', NULL, '2020-02-21 22:03:13'),
(230, 'eye', NULL, '2020-02-21 22:03:13'),
(231, 'eye-slash', NULL, '2020-02-21 22:03:13'),
(232, 'eyedropper', NULL, '2020-02-21 22:03:13'),
(233, 'fax', NULL, '2020-02-21 22:03:13'),
(234, 'feed', NULL, '2020-02-21 22:03:13'),
(235, 'female', NULL, '2020-02-21 22:03:13'),
(236, 'fighter-jet', NULL, '2020-02-21 22:03:13'),
(237, 'file-archive-o', NULL, '2020-02-21 22:03:13'),
(238, 'file-audio-o', NULL, '2020-02-21 22:03:13'),
(239, 'file-code-o', NULL, '2020-02-21 22:03:13'),
(240, 'file-excel-o', NULL, '2020-02-21 22:03:13'),
(241, 'podcast', NULL, '2020-02-21 22:03:13'),
(242, 'quora', NULL, '2020-02-21 22:03:13'),
(243, 'ravelry', NULL, '2020-02-21 22:03:13'),
(244, 's15', NULL, '2020-02-21 22:03:13'),
(245, 'shower', NULL, '2020-02-21 22:03:13'),
(246, 'snowflake-o', NULL, '2020-02-21 22:03:13'),
(247, 'superpowers', NULL, '2020-02-21 22:03:13'),
(248, 'telegram', NULL, '2020-02-21 22:03:13'),
(249, 'thermometer', NULL, '2020-02-21 22:03:13'),
(250, 'thermometer-0', NULL, '2020-02-21 22:03:13'),
(251, 'thermometer-1', NULL, '2020-02-21 22:03:13'),
(252, 'thermometer-2', NULL, '2020-02-21 22:03:13'),
(253, 'credit-card', NULL, '2020-02-21 22:03:13'),
(254, 'credit-card-alt', NULL, '2020-02-21 22:03:13'),
(255, 'crop', NULL, '2020-02-21 22:03:13'),
(256, 'crosshairs', NULL, '2020-02-21 22:03:13'),
(257, 'cube', NULL, '2020-02-21 22:03:13'),
(258, 'cubes', NULL, '2020-02-21 22:03:13'),
(259, 'cutlery', NULL, '2020-02-21 22:03:13'),
(260, 'dashboard', NULL, '2020-02-21 22:03:13'),
(261, 'database', NULL, '2020-02-21 22:03:13'),
(262, 'deaf', NULL, '2020-02-21 22:03:13'),
(263, 'deafness', NULL, '2020-02-21 22:03:13'),
(264, 'desktop', NULL, '2020-02-21 22:03:13'),
(265, 'diamond', NULL, '2020-02-21 22:03:13'),
(266, 'dot-circle-o', NULL, '2020-02-21 22:03:13'),
(267, 'download', NULL, '2020-02-21 22:03:13'),
(268, 'drivers-license', NULL, '2020-02-21 22:03:13'),
(269, 'drivers-license-o', NULL, '2020-02-21 22:03:13'),
(270, 'edit', NULL, '2020-02-21 22:03:13'),
(271, 'ellipsis-h', NULL, '2020-02-21 22:03:13'),
(272, 'ellipsis-v', NULL, '2020-02-21 22:03:13'),
(273, 'envelope', NULL, '2020-02-21 22:03:13'),
(274, 'envelope-o', NULL, '2020-02-21 22:03:13'),
(275, 'envelope-open', NULL, '2020-02-21 22:03:13'),
(276, 'envelope-open-o', NULL, '2020-02-21 22:03:13'),
(277, 'envelope-square', NULL, '2020-02-21 22:03:13'),
(278, 'eraser', NULL, '2020-02-21 22:03:13'),
(279, 'exchange', NULL, '2020-02-21 22:03:13'),
(280, 'exclamation', NULL, '2020-02-21 22:03:13'),
(281, 'exclamation-circle', NULL, '2020-02-21 22:03:13'),
(282, 'exclamation-triangle', NULL, '2020-02-21 22:03:13'),
(283, 'external-link', NULL, '2020-02-21 22:03:13'),
(284, 'external-link-square', NULL, '2020-02-21 22:03:13'),
(285, 'eye', NULL, '2020-02-21 22:03:13'),
(286, 'eye-slash', NULL, '2020-02-21 22:03:13'),
(287, 'eyedropper', NULL, '2020-02-21 22:03:13'),
(288, 'fax', NULL, '2020-02-21 22:03:13'),
(289, 'feed', NULL, '2020-02-21 22:03:13'),
(290, 'female', NULL, '2020-02-21 22:03:13'),
(291, 'fighter-jet', NULL, '2020-02-21 22:03:13'),
(292, 'file-archive-o', NULL, '2020-02-21 22:03:13'),
(293, 'file-audio-o', NULL, '2020-02-21 22:03:13'),
(294, 'file-code-o', NULL, '2020-02-21 22:03:13'),
(295, 'file-excel-o', NULL, '2020-02-21 22:03:13'),
(296, 'file-image-o', NULL, '2020-02-21 22:03:13'),
(297, 'file-movie-o', NULL, '2020-02-21 22:03:13'),
(298, 'file-pdf-o', NULL, '2020-02-21 22:03:13'),
(299, 'file-photo-o', NULL, '2020-02-21 22:03:13'),
(300, 'file-picture-o', NULL, '2020-02-21 22:03:13'),
(301, 'file-powerpoint-o', NULL, '2020-02-21 22:03:13'),
(302, 'file-sound-o', NULL, '2020-02-21 22:03:13'),
(303, 'file-video-o', NULL, '2020-02-21 22:03:13'),
(304, 'file-word-o', NULL, '2020-02-21 22:03:13'),
(305, 'file-zip-o', NULL, '2020-02-21 22:03:13'),
(306, 'film', NULL, '2020-02-21 22:03:13'),
(307, 'filter', NULL, '2020-02-21 22:03:13'),
(308, 'fire', NULL, '2020-02-21 22:03:13'),
(309, 'fire-extinguisher', NULL, '2020-02-21 22:03:13'),
(310, 'flag', NULL, '2020-02-21 22:03:13'),
(311, 'flag-checkered', NULL, '2020-02-21 22:03:13'),
(312, 'flag-o', NULL, '2020-02-21 22:03:13'),
(313, 'flash', NULL, '2020-02-21 22:03:13'),
(314, 'flask', NULL, '2020-02-21 22:03:13'),
(315, 'folder', NULL, '2020-02-21 22:03:13'),
(316, 'folder-o', NULL, '2020-02-21 22:03:13'),
(317, 'folder-open', NULL, '2020-02-21 22:03:13'),
(318, 'folder-open-o', NULL, '2020-02-21 22:03:13'),
(319, 'frown-o', NULL, '2020-02-21 22:03:13'),
(320, 'futbol-o', NULL, '2020-02-21 22:03:13'),
(321, 'gamepad', NULL, '2020-02-21 22:03:13'),
(322, 'gavel', NULL, '2020-02-21 22:03:13'),
(323, 'gear', NULL, '2020-02-21 22:03:13'),
(324, 'gears', NULL, '2020-02-21 22:03:13'),
(325, 'gift', NULL, '2020-02-21 22:03:13'),
(326, 'glass', NULL, '2020-02-21 22:03:13'),
(327, 'globe', NULL, '2020-02-21 22:03:13'),
(328, 'graduation-cap', NULL, '2020-02-21 22:03:13'),
(329, 'group', NULL, '2020-02-21 22:03:13'),
(330, 'hand-grab-o', NULL, '2020-02-21 22:03:13'),
(331, 'hand-lizard-o', NULL, '2020-02-21 22:03:13'),
(332, 'hand-paper-o', NULL, '2020-02-21 22:03:13'),
(333, 'hand-peace-o', NULL, '2020-02-21 22:03:13'),
(334, 'hand-pointer-o', NULL, '2020-02-21 22:03:13'),
(335, 'hand-rock-o', NULL, '2020-02-21 22:03:13'),
(336, 'hand-scissors-o', NULL, '2020-02-21 22:03:13'),
(337, 'hand-spock-o', NULL, '2020-02-21 22:03:13'),
(338, 'hand-stop-o', NULL, '2020-02-21 22:03:13'),
(339, 'handshake-o', NULL, '2020-02-21 22:03:13'),
(340, 'hard--hearing', NULL, '2020-02-21 22:03:13'),
(341, 'hashtag', NULL, '2020-02-21 22:03:13'),
(342, 'hdd-o', NULL, '2020-02-21 22:03:13'),
(343, 'headphones', NULL, '2020-02-21 22:03:13'),
(344, 'heart', NULL, '2020-02-21 22:03:13'),
(345, 'facebook', NULL, NULL),
(346, 'twitter', NULL, NULL),
(347, 'instagram', NULL, NULL),
(348, 'linkedin', '2020-03-04 10:00:00', '2020-03-04 10:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_itemservicios`
--

CREATE TABLE `pw_itemservicios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8_unicode_ci NOT NULL,
  `icono` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `servicio_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_itemservicios`
--

INSERT INTO `pw_itemservicios` (`id`, `titulo`, `descripcion`, `icono`, `servicio_id`, `created_at`, `updated_at`) VALUES
(1, 'METALMECANICA', '<p>Se brinda servicios de ingenier&iacute;a para el sector comercial e industrial; en las &aacute;reas de dise&ntilde;o, fabricaci&oacute;n y montaje de estructuras met&aacute;licas, como:</p>\r\n\r\n<ul>\r\n	<li>Tanques</li>\r\n	<li>tuber&iacute;as</li>\r\n	<li>Ductos</li>\r\n	<li>Escaleras</li>\r\n	<li>Rejas</li>\r\n	<li>Barandas</li>\r\n	<li>Parrillas</li>\r\n	<li>Cercos perim&eacute;tricos</li>\r\n</ul>\r\n\r\n<p>En general todo tipo de proyectos concernientes a la metal mec&aacute;nica. Adem&aacute;s tambi&eacute;n realizamos y ejecutamos proyectos en acero inoxidable y aluminio.</p>\r\n', 'meetup', 1, '2020-03-04 10:11:37', '2020-03-10 15:40:44'),
(2, 'MECÁNICA DE PRODUCCIÓN', 'Para la especialidad de Mecánica de producción brindamos el servicio de diseño, fabricación e instalación de partes y piezas para máquinas y equipos del sector industrial y minero, según los requerimientos de nuestros clientes, poniendo a su disposición nuestro personal profesional y técnico. Además realizamos trabajos de reconstrucciones de pieza y partes de máquinas para la industria y minería.', 'asl-interpreting', 1, '2020-03-04 10:12:31', '2020-03-10 15:00:58'),
(3, 'MAQUINARIA PESADA', 'Nuestras divisiones de reparación, mantenimiento y de servicio, está conformada por ingenieros y técnicos altamente calificados para cada línea de negocios.  Mecánica: instalaciones, reparaciones, mantenimiento y servicio técnico en equipos de alto tonelaje y maquinaria pesada en general, para la minería e industria.  Sistemas hidráulicos: diseño, fabricación, reparación, instalación, modificaciones, mantenimiento y servicio técnico de sistemas hidráulicos en maquinarias, tales como:  Camiones Aljibes Grúas de alto tonelaje Montacargas, Elevadores Para todo tipo de empresas, oficinas y comercios en general.  Sistemas eléctricos: diseño, fabricación, reparación, implementación, ejecución de proyectos, mantenimiento y servicio técnico de maquinaria pesada y sistemas integrados para la industria en general, tales como: sistemas automatizados, sistemas de regadío en maquinaria pesada, oficinas, truck shop, etc.', 'car', 1, '2020-03-04 10:15:09', '2020-03-10 15:02:17'),
(4, 'MECÁNICA INDUSTRIAL', 'Se ofrece un plan integral de beneficios y asesoría especializada para sus requerimientos de estudios de ingeniería, instalación e implementación de sistemas integrales dentro del campo industrial, así como brindar un servicio de mantenimiento con el fin de lograr un desempeño óptimo en los distintos procesos de producción. Tales como: fabricación de lozas de lavado para maquinarias y componentes industriales, mantenimiento de plantas industriales, Iluminación, sistemas eléctricos de control, etc.', 'cogs', 1, '2020-03-10 15:03:16', '2020-03-10 15:03:16'),
(5, 'MAQUINARIAS Y EQUIPOS PARA ARRIENDO', 'Grúa Horquilla Lonking de 10 ton - Camión Pluma Hino de 4 ton - Camión Pluma Volkwagen de 8 ton - Grúa RT 50 ton - Camión Pluma recta de 30 ton - Grúa camión de 160 ton - Grúa camión de 300 ton - Compresor autónomo Sullair - Soldadora autónoma Lincoln', 'podcast', 1, '2020-03-10 15:04:48', '2020-03-10 15:07:06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_itemslider`
--

CREATE TABLE `pw_itemslider` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `imagen` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `titulo` varchar(50) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `button` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `enlace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `slider_id` bigint(20) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_itemslider`
--

INSERT INTO `pw_itemslider` (`id`, `imagen`, `titulo`, `descripcion`, `button`, `enlace`, `slider_id`, `created_at`, `updated_at`) VALUES
(4, 'IMG/1583856682WALLPAPER1.PNG', '', '', '', 'http://localhost/Appsiel/sitio-landing-page', 1, '2020-03-10 16:11:22', '2020-03-10 17:24:36'),
(5, 'IMG/1583856694WALLPAPER2.JPG', 'NUESTROS PRODUCTOS', 'CON UNA LÍNEA DE MANGUERA HIDRÁULICAS DE CALIDAD QUE CUMPLE Y EXCEDE LAS NORMAS INTERNACIONALES TALES COMO SAE, ISO, DIN,RMA Y FDA.', '', 'http://localhost/Appsiel/sitio-landing-page', 1, '2020-03-10 16:11:34', '2020-03-10 17:25:05'),
(6, 'img/1583857134wallpaper3.jpg', '', '', '', 'http://localhost/Appsiel/sitio-landing-page', 1, '2020-03-10 16:18:54', '2020-03-10 16:18:54'),
(7, 'img/1583857148wallpaper4.jpg', '', '', '', 'http://localhost/Appsiel/sitio-landing-page', 1, '2020-03-10 16:19:08', '2020-03-10 16:19:08');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_menunavegacion`
--

CREATE TABLE `pw_menunavegacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `orden` int(11) NOT NULL,
  `icono` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `enlace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `navegacion_id` int(10) UNSIGNED NOT NULL,
  `parent_id` int(10) UNSIGNED NOT NULL DEFAULT '0',
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_menunavegacion`
--

INSERT INTO `pw_menunavegacion` (`id`, `titulo`, `descripcion`, `orden`, `icono`, `enlace`, `navegacion_id`, `parent_id`, `estado`, `created_at`, `updated_at`) VALUES
(3, 'Inicio', 'Inicio', 1, '', 'http://localhost/Appsiel/sitio-inicio', 1, 0, 'ACTIVO', '2020-02-29 20:06:49', '2020-03-11 02:33:28');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_navegacion`
--

CREATE TABLE `pw_navegacion` (
  `id` int(10) UNSIGNED NOT NULL,
  `logo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `color` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `fixed` int(11) NOT NULL DEFAULT '0',
  `background` varchar(20) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_navegacion`
--

INSERT INTO `pw_navegacion` (`id`, `logo`, `color`, `widget_id`, `created_at`, `updated_at`, `fixed`, `background`) VALUES
(1, 'SERVNORT', '#ffffff', 0, '2020-02-11 10:00:00', '2020-03-11 00:25:50', 0, '#0f70b4');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_paginas`
--

CREATE TABLE `pw_paginas` (
  `id` int(10) UNSIGNED NOT NULL,
  `descripcion` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `meta_keywords` text COLLATE utf8_unicode_ci NOT NULL,
  `codigo_google_analitics` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `favicon` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `titulo` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
  `pagina_inicio` tinyint(1) NOT NULL,
  `estado` varchar(10) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `slug` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_paginas`
--

INSERT INTO `pw_paginas` (`id`, `descripcion`, `meta_keywords`, `codigo_google_analitics`, `favicon`, `titulo`, `pagina_inicio`, `estado`, `created_at`, `updated_at`, `slug`) VALUES
(1, 'SERVNORT INGENIERIA', 'Sistema de testeo,Conexiones para tubos hidraulicos,Conexiones hidraulicas,linea neumatica,valvulas hidraulicas e industriales,Adapdatadores hidraulicos,Acoples,Mangueras Industriales', 'UA-149024927-1', 'img/1583892561favi.png', 'Inicio', 1, 'Activa', '2019-07-14 15:29:57', '2020-03-11 11:36:52', 'sitio-inicio'),
(2, 'AVIPOULET - Blog', '', 'UA-149024927-1', '5e28355ed76e7.ico', 'Blog', 0, 'Activa', '2020-01-24 02:47:42', '2020-02-29 17:13:12', 'sitioblog'),
(3, 'Espacio virtual', 'espacio virtual de documentos, repositorio ', '', '', 'Espacio virtual de documentos', 0, 'Activa', '2020-03-03 14:22:11', '2020-03-03 15:44:55', 'sitio-espacio-virtual-de-documentos');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_redessociales`
--

CREATE TABLE `pw_redessociales` (
  `id` int(10) UNSIGNED NOT NULL,
  `icono` varchar(20) COLLATE utf8_unicode_ci NOT NULL,
  `nombre` varchar(30) COLLATE utf8_unicode_ci NOT NULL,
  `enlace` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_redessociales`
--

INSERT INTO `pw_redessociales` (`id`, `icono`, `nombre`, `enlace`, `created_at`, `updated_at`) VALUES
(1, 'facebook', 'Facebook', 'https://web.facebook.com/appsiel', '2020-02-29 20:06:10', '2020-03-04 09:34:16'),
(2, 'instagram', 'Instagram', 'https://www.instagram.com/appsiel_/', '2020-03-03 19:30:26', '2020-03-04 09:34:52'),
(3, 'linkedin', 'LinkedIn', 'https://www.linkedin.com/in/appsiel-sas-77850816a/', '2020-03-04 09:37:22', '2020-03-04 09:39:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_seccion`
--

CREATE TABLE `pw_seccion` (
  `id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  `preview` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `tipo` enum('ESTANDAR','GENERICO') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'GENERICO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_seccion`
--

INSERT INTO `pw_seccion` (`id`, `nombre`, `descripcion`, `created_at`, `updated_at`, `preview`, `tipo`) VALUES
(2, 'Slider', 'sección fotos que se pasan en un tiempo determinado', '2020-02-08 10:00:00', '2020-02-08 10:00:00', 'assets/web/componentes/slider.png', 'GENERICO'),
(3, 'Navegación', 'menu que enlace a las diferentes partes de la pagina web', '2020-02-08 10:00:00', '2020-02-08 10:00:00', 'assets/web/componentes/navegacion.png', 'ESTANDAR'),
(4, 'Quienes somos', 'descripcíon de la empresa', '2019-12-16 10:00:00', '2019-11-13 10:00:00', 'assets/web/componentes/about-us.png', 'GENERICO'),
(5, 'Galería', 'Galería de Imágenes', NULL, NULL, 'assets/web/componentes/galeria.png', 'GENERICO'),
(6, 'Servicios', 'Servicios exclusivos', NULL, NULL, 'assets/web/componentes/servicios.png', 'GENERICO'),
(7, 'Artículos', 'Gestión de artículos', NULL, NULL, 'assets/web/componentes/articulos.png', 'GENERICO'),
(8, 'Pie de página', 'Pie de pagina', NULL, NULL, 'assets/web/componentes/footer.png', 'ESTANDAR'),
(9, 'Contáctenos', 'Contáctenos', NULL, NULL, 'assets/web/componentes/footer.png', 'GENERICO'),
(10, 'Clientes', 'Clientes', NULL, NULL, 'assets/web/componentes/footer.png', 'GENERICO'),
(11, 'Archivos', 'Archivos', NULL, NULL, 'assets/web/componentes/footer.png', 'GENERICO');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_servicios`
--

CREATE TABLE `pw_servicios` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `titulo` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `descripcion` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_servicios`
--

INSERT INTO `pw_servicios` (`id`, `titulo`, `descripcion`, `widget_id`, `created_at`, `updated_at`) VALUES
(1, 'NUESTROS SERVICIOS', '', 19, '2020-03-04 10:09:36', '2020-03-10 15:20:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_slider`
--

CREATE TABLE `pw_slider` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `widget_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_slider`
--

INSERT INTO `pw_slider` (`id`, `widget_id`, `created_at`, `updated_at`) VALUES
(1, 1, '2020-02-26 15:01:29', '2020-02-26 15:01:29');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pw_widget`
--

CREATE TABLE `pw_widget` (
  `id` int(10) UNSIGNED NOT NULL,
  `orden` int(11) NOT NULL,
  `estado` enum('ACTIVO','INACTIVO') COLLATE utf8_unicode_ci NOT NULL,
  `pagina_id` int(10) UNSIGNED NOT NULL,
  `seccion_id` int(10) UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `pw_widget`
--

INSERT INTO `pw_widget` (`id`, `orden`, `estado`, `pagina_id`, `seccion_id`, `created_at`, `updated_at`) VALUES
(1, 2, 'ACTIVO', 1, 2, '2020-02-25 16:59:38', '2020-03-11 00:25:19'),
(3, 1, 'ACTIVO', 1, 3, '2020-02-25 19:46:45', '2020-02-25 19:46:45'),
(4, 3, 'ACTIVO', 1, 4, '2020-02-25 19:46:54', '2020-03-10 14:43:02'),
(5, 4, 'ACTIVO', 2, 7, '2020-02-29 17:15:46', '2020-02-29 17:15:46'),
(6, 9, 'ACTIVO', 1, 8, '2020-02-29 18:42:28', '2020-03-11 00:25:19'),
(7, 12, 'ACTIVO', 2, 8, '2020-02-29 19:09:53', '2020-02-29 19:09:53'),
(8, 1, 'ACTIVO', 2, 3, '2020-02-29 19:35:07', '2020-02-29 19:35:07'),
(9, 8, 'ACTIVO', 1, 9, '2020-02-29 19:43:23', '2020-03-10 15:55:54'),
(10, 1, 'ACTIVO', 3, 3, '2020-03-03 14:22:24', '2020-03-03 14:22:24'),
(11, 2, 'ACTIVO', 3, 11, '2020-03-03 14:23:00', '2020-03-03 14:23:00'),
(12, 3, 'ACTIVO', 3, 8, '2020-03-03 15:35:27', '2020-03-03 15:35:27'),
(19, 4, 'ACTIVO', 1, 6, '2020-03-04 10:07:43', '2020-03-10 14:43:02'),
(20, 7, 'ACTIVO', 1, 10, '2020-03-10 15:48:04', '2020-03-10 15:55:02'),
(21, 6, 'ACTIVO', 1, 7, '2020-03-10 15:55:30', '2020-03-10 15:55:45');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `pw_aboutuses`
--
ALTER TABLE `pw_aboutuses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_aboutuses_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_albums`
--
ALTER TABLE `pw_albums`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_albums_galeria_id_foreign` (`galeria_id`);

--
-- Indices de la tabla `pw_archivoitems`
--
ALTER TABLE `pw_archivoitems`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_archivoitems_archivo_id_foreign` (`archivo_id`);

--
-- Indices de la tabla `pw_archivos`
--
ALTER TABLE `pw_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_archivos_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_articles`
--
ALTER TABLE `pw_articles`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_articles_articlesetup_id_foreign` (`articlesetup_id`);

--
-- Indices de la tabla `pw_articlesetups`
--
ALTER TABLE `pw_articlesetups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_articlesetups_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_categorias`
--
ALTER TABLE `pw_categorias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `descripcion` (`descripcion`);

--
-- Indices de la tabla `pw_categoria_footer`
--
ALTER TABLE `pw_categoria_footer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_categoria_footer_footer_id_foreign` (`footer_id`);

--
-- Indices de la tabla `pw_clientes`
--
ALTER TABLE `pw_clientes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_clientes_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_comentarios`
--
ALTER TABLE `pw_comentarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `articulo_id` (`articulo_id`);

--
-- Indices de la tabla `pw_componente`
--
ALTER TABLE `pw_componente`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_configuracion_general`
--
ALTER TABLE `pw_configuracion_general`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_contactenos`
--
ALTER TABLE `pw_contactenos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_contactenos_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_enlace_footer`
--
ALTER TABLE `pw_enlace_footer`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_enlace_footer_categoria_id_foreign` (`categoria_id`);

--
-- Indices de la tabla `pw_footer`
--
ALTER TABLE `pw_footer`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_formcontactenos`
--
ALTER TABLE `pw_formcontactenos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_fotos`
--
ALTER TABLE `pw_fotos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_fotos_album_id_foreign` (`album_id`);

--
-- Indices de la tabla `pw_galerias`
--
ALTER TABLE `pw_galerias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_galerias_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_icons`
--
ALTER TABLE `pw_icons`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_itemservicios`
--
ALTER TABLE `pw_itemservicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_itemservicios_servicio_id_foreign` (`servicio_id`);

--
-- Indices de la tabla `pw_itemslider`
--
ALTER TABLE `pw_itemslider`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_itemslider_slider_id_foreign` (`slider_id`);

--
-- Indices de la tabla `pw_menunavegacion`
--
ALTER TABLE `pw_menunavegacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_menunavegacion_navegacion_id_foreign` (`navegacion_id`);

--
-- Indices de la tabla `pw_navegacion`
--
ALTER TABLE `pw_navegacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_navegacion_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_paginas`
--
ALTER TABLE `pw_paginas`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_redessociales`
--
ALTER TABLE `pw_redessociales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_seccion`
--
ALTER TABLE `pw_seccion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pw_servicios`
--
ALTER TABLE `pw_servicios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_servicios_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_slider`
--
ALTER TABLE `pw_slider`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_slider_widget_id_foreign` (`widget_id`);

--
-- Indices de la tabla `pw_widget`
--
ALTER TABLE `pw_widget`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pw_widget_pagina_id_foreign` (`pagina_id`),
  ADD KEY `pw_widget_seccion_id_foreign` (`seccion_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `pw_aboutuses`
--
ALTER TABLE `pw_aboutuses`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pw_albums`
--
ALTER TABLE `pw_albums`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pw_archivoitems`
--
ALTER TABLE `pw_archivoitems`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `pw_archivos`
--
ALTER TABLE `pw_archivos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pw_articles`
--
ALTER TABLE `pw_articles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT de la tabla `pw_articlesetups`
--
ALTER TABLE `pw_articlesetups`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pw_categorias`
--
ALTER TABLE `pw_categorias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pw_categoria_footer`
--
ALTER TABLE `pw_categoria_footer`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pw_clientes`
--
ALTER TABLE `pw_clientes`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `pw_comentarios`
--
ALTER TABLE `pw_comentarios`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pw_componente`
--
ALTER TABLE `pw_componente`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pw_configuracion_general`
--
ALTER TABLE `pw_configuracion_general`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pw_contactenos`
--
ALTER TABLE `pw_contactenos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pw_enlace_footer`
--
ALTER TABLE `pw_enlace_footer`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `pw_footer`
--
ALTER TABLE `pw_footer`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pw_formcontactenos`
--
ALTER TABLE `pw_formcontactenos`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pw_fotos`
--
ALTER TABLE `pw_fotos`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT de la tabla `pw_galerias`
--
ALTER TABLE `pw_galerias`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pw_icons`
--
ALTER TABLE `pw_icons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=349;

--
-- AUTO_INCREMENT de la tabla `pw_itemservicios`
--
ALTER TABLE `pw_itemservicios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pw_itemslider`
--
ALTER TABLE `pw_itemslider`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `pw_menunavegacion`
--
ALTER TABLE `pw_menunavegacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `pw_navegacion`
--
ALTER TABLE `pw_navegacion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pw_paginas`
--
ALTER TABLE `pw_paginas`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pw_redessociales`
--
ALTER TABLE `pw_redessociales`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pw_seccion`
--
ALTER TABLE `pw_seccion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `pw_servicios`
--
ALTER TABLE `pw_servicios`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pw_slider`
--
ALTER TABLE `pw_slider`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `pw_widget`
--
ALTER TABLE `pw_widget`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pw_aboutuses`
--
ALTER TABLE `pw_aboutuses`
  ADD CONSTRAINT `pw_aboutuses_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_albums`
--
ALTER TABLE `pw_albums`
  ADD CONSTRAINT `pw_albums_galeria_id_foreign` FOREIGN KEY (`galeria_id`) REFERENCES `pw_galerias` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_archivoitems`
--
ALTER TABLE `pw_archivoitems`
  ADD CONSTRAINT `pw_archivoitems_archivo_id_foreign` FOREIGN KEY (`archivo_id`) REFERENCES `pw_archivos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_archivos`
--
ALTER TABLE `pw_archivos`
  ADD CONSTRAINT `pw_archivos_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_articles`
--
ALTER TABLE `pw_articles`
  ADD CONSTRAINT `pw_articles_articlesetup_id_foreign` FOREIGN KEY (`articlesetup_id`) REFERENCES `pw_articlesetups` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_articlesetups`
--
ALTER TABLE `pw_articlesetups`
  ADD CONSTRAINT `pw_articlesetups_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_categoria_footer`
--
ALTER TABLE `pw_categoria_footer`
  ADD CONSTRAINT `pw_categoria_footer_footer_id_foreign` FOREIGN KEY (`footer_id`) REFERENCES `pw_footer` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_clientes`
--
ALTER TABLE `pw_clientes`
  ADD CONSTRAINT `pw_clientes_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_contactenos`
--
ALTER TABLE `pw_contactenos`
  ADD CONSTRAINT `pw_contactenos_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_enlace_footer`
--
ALTER TABLE `pw_enlace_footer`
  ADD CONSTRAINT `pw_enlace_footer_categoria_id_foreign` FOREIGN KEY (`categoria_id`) REFERENCES `pw_categoria_footer` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_fotos`
--
ALTER TABLE `pw_fotos`
  ADD CONSTRAINT `pw_fotos_album_id_foreign` FOREIGN KEY (`album_id`) REFERENCES `pw_albums` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_galerias`
--
ALTER TABLE `pw_galerias`
  ADD CONSTRAINT `pw_galerias_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_itemservicios`
--
ALTER TABLE `pw_itemservicios`
  ADD CONSTRAINT `pw_itemservicios_servicio_id_foreign` FOREIGN KEY (`servicio_id`) REFERENCES `pw_servicios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_itemslider`
--
ALTER TABLE `pw_itemslider`
  ADD CONSTRAINT `pw_itemslider_slider_id_foreign` FOREIGN KEY (`slider_id`) REFERENCES `pw_slider` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_menunavegacion`
--
ALTER TABLE `pw_menunavegacion`
  ADD CONSTRAINT `pw_menunavegacion_navegacion_id_foreign` FOREIGN KEY (`navegacion_id`) REFERENCES `pw_navegacion` (`id`);

--
-- Filtros para la tabla `pw_navegacion`
--
ALTER TABLE `pw_navegacion`
  ADD CONSTRAINT `pw_navegacion_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`);

--
-- Filtros para la tabla `pw_servicios`
--
ALTER TABLE `pw_servicios`
  ADD CONSTRAINT `pw_servicios_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_slider`
--
ALTER TABLE `pw_slider`
  ADD CONSTRAINT `pw_slider_widget_id_foreign` FOREIGN KEY (`widget_id`) REFERENCES `pw_widget` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pw_widget`
--
ALTER TABLE `pw_widget`
  ADD CONSTRAINT `pw_widget_pagina_id_foreign` FOREIGN KEY (`pagina_id`) REFERENCES `pw_paginas` (`id`),
  ADD CONSTRAINT `pw_widget_seccion_id_foreign` FOREIGN KEY (`seccion_id`) REFERENCES `pw_seccion` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

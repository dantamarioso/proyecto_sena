-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 01-12-2025 a las 17:45:32
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
-- Base de datos: `inventario_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_materiales`
--

CREATE TABLE `auditoria_materiales` (
  `id` int(11) NOT NULL,
  `material_id` int(11) DEFAULT NULL,
  `accion` enum('crear','actualizar','eliminar') DEFAULT 'actualizar',
  `detalles` longtext NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_materiales`
--

INSERT INTO `auditoria_materiales` (`id`, `material_id`, `accion`, `detalles`, `admin_id`, `ip_address`, `fecha_cambio`) VALUES
(1, 1, 'actualizar', '{\"nodo_id\":{\"antes\":11,\"despues\":9}}', 1, '179.1.217.248', '2025-12-01 16:43:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria_usuarios`
--

CREATE TABLE `auditoria_usuarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` enum('crear','actualizar','eliminar') DEFAULT 'actualizar',
  `detalles` longtext NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria_usuarios`
--

INSERT INTO `auditoria_usuarios` (`id`, `usuario_id`, `accion`, `detalles`, `admin_id`, `fecha_cambio`, `ip_address`) VALUES
(1, 2, 'actualizar', '{\"celular\":{\"anterior\":\"(vac\\u00edo)\",\"nuevo\":\"(vac\\u00edo)\"},\"cargo\":{\"anterior\":\"(vac\\u00edo)\",\"nuevo\":\"(vac\\u00edo)\"},\"rol\":{\"anterior\":\"usuario\",\"nuevo\":\"admin\"}}', 1, '2025-11-25 20:20:06', '181.234.43.55'),
(2, 1, 'actualizar', '{\"foto\":{\"anterior\":\"uploads\\/fotos\\/foto_6925d8f7a4d3f.jpg\",\"nuevo\":\"uploads\\/fotos\\/foto_692610ac6a97a.jpg\"}}', 1, '2025-11-25 20:25:16', '181.234.43.55'),
(3, 2, 'actualizar', '{\"cargo\":{\"anterior\":\"\",\"nuevo\":\"Gerente en Assasin Creed\"},\"foto\":{\"anterior\":\"(ninguna)\",\"nuevo\":\"uploads\\/fotos\\/foto_692610ee299a0.jpg\"}}', 2, '2025-11-25 20:26:22', '181.234.43.55'),
(4, 2, 'actualizar', '{\"rol\":{\"anterior\":\"admin\",\"nuevo\":\"dinamizador\"},\"nodo_id\":{\"anterior\":\"Sin asignar\",\"nuevo\":1}}', 1, '2025-11-25 20:26:45', '181.234.43.55'),
(5, 2, 'actualizar', '{\"nodo_id\":{\"anterior\":1,\"nuevo\":2}}', 1, '2025-11-25 20:27:20', '181.234.43.55'),
(6, 2, 'actualizar', '{\"rol\":{\"anterior\":\"dinamizador\",\"nuevo\":\"usuario\"},\"nodo_id\":{\"anterior\":2,\"nuevo\":1},\"linea_id\":{\"anterior\":\"Sin asignar\",\"nuevo\":1}}', 1, '2025-11-25 20:27:39', '181.234.43.55'),
(7, 2, 'actualizar', '{\"rol\":{\"anterior\":\"usuario\",\"nuevo\":\"dinamizador\"}}', 1, '2025-11-25 20:27:47', '181.234.43.55'),
(8, 3, 'crear', '{\"nombre\":{\"anterior\":\"N\\/A\",\"nuevo\":\"meme\"},\"correo\":{\"anterior\":\"N\\/A\",\"nuevo\":\"danamarios.o@gmail.com\"},\"nombre_usuario\":{\"anterior\":\"N\\/A\",\"nuevo\":\"danamarios.o@gmail.com\"},\"rol\":{\"anterior\":\"N\\/A\",\"nuevo\":\"usuario\"},\"estado\":{\"anterior\":\"N\\/A\",\"nuevo\":\"Activo\"},\"email_verificado\":{\"anterior\":\"N\\/A\",\"nuevo\":\"Pendiente\"}}', NULL, '2025-11-25 20:28:36', '181.234.43.55'),
(9, 1, 'actualizar', '{\"foto\":{\"anterior\":\"uploads\\/fotos\\/foto_692610ac6a97a.jpg\",\"nuevo\":\"uploads\\/fotos\\/foto_6926398c1512f.jpg\"}}', 1, '2025-11-25 23:19:40', '181.234.43.55'),
(10, 3, 'actualizar', '{\"celular\":{\"anterior\":\"(vac\\u00edo)\",\"nuevo\":\"(vac\\u00edo)\"},\"cargo\":{\"anterior\":\"(vac\\u00edo)\",\"nuevo\":\"(vac\\u00edo)\"},\"rol\":{\"anterior\":\"usuario\",\"nuevo\":\"dinamizador\"},\"nodo_id\":{\"anterior\":\"Sin asignar\",\"nuevo\":1}}', 1, '2025-11-25 23:21:54', '181.234.43.55'),
(11, 1, 'actualizar', '{\"nombre\":{\"anterior\":\"Mario Alejandro\",\"nuevo\":\"Mario\"}}', 1, '2025-11-26 13:23:32', '179.1.217.248'),
(12, 2, 'actualizar', '{\"rol\":{\"anterior\":\"dinamizador\",\"nuevo\":\"usuario\"},\"nodo_id\":{\"anterior\":1,\"nuevo\":2}}', 1, '2025-11-28 19:13:58', '179.1.217.248'),
(13, 4, 'crear', '{\"nombre\":{\"anterior\":\"N\\/A\",\"nuevo\":\"derly natalia\"},\"correo\":{\"anterior\":\"N\\/A\",\"nuevo\":\"lehoyej959@badfist.com\"},\"nombre_usuario\":{\"anterior\":\"N\\/A\",\"nuevo\":\"lehoyej959@badfist.com\"},\"rol\":{\"anterior\":\"N\\/A\",\"nuevo\":\"usuario\"},\"estado\":{\"anterior\":\"N\\/A\",\"nuevo\":\"Activo\"},\"email_verificado\":{\"anterior\":\"N\\/A\",\"nuevo\":\"Pendiente\"}}', NULL, '2025-12-01 15:35:30', '179.1.217.251'),
(14, 4, 'actualizar', '{\"nombre\":{\"anterior\":\"derly natalia\",\"nuevo\":\"derly\"},\"celular\":{\"anterior\":\"(vac\\u00edo)\",\"nuevo\":\"3173037445\"},\"cargo\":{\"anterior\":\"(vac\\u00edo)\",\"nuevo\":\"doctora\"},\"foto\":{\"anterior\":\"(ninguna)\",\"nuevo\":\"uploads\\/fotos\\/foto_692db61f234df.png\"}}', 4, '2025-12-01 15:37:03', '179.1.217.251'),
(15, 4, 'actualizar', '{\"rol\":{\"anterior\":\"usuario\",\"nuevo\":\"dinamizador\"},\"nodo_id\":{\"anterior\":\"Sin asignar\",\"nuevo\":1}}', 1, '2025-12-01 15:37:48', '179.1.217.248');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `lineas`
--

CREATE TABLE `lineas` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `lineas`
--

INSERT INTO `lineas` (`id`, `nombre`, `descripcion`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'BIOTECNOLOGÍA Y NANOTECNOLOGÍA', 'Línea especializada en biotecnología y nanotecnología', 1, '2025-11-25 14:52:41', '2025-11-25 14:52:41'),
(2, 'INGENIERÍA Y DISEÑO', 'Línea de ingeniería y diseño', 1, '2025-11-25 14:52:41', '2025-11-25 14:52:41'),
(3, 'ELECTRÓNICA Y TELECOMUNICACIONES', 'Línea de electrónica y telecomunicaciones', 1, '2025-11-25 14:52:41', '2025-11-25 14:52:41'),
(4, 'TECNOLOGÍAS VIRTUALES', 'Línea de tecnologías virtuales y desarrollo digital', 1, '2025-11-25 14:52:41', '2025-11-25 14:52:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `linea_nodo`
--

CREATE TABLE `linea_nodo` (
  `id` int(11) NOT NULL,
  `linea_id` int(11) NOT NULL,
  `nodo_id` int(11) NOT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `linea_nodo`
--

INSERT INTO `linea_nodo` (`id`, `linea_id`, `nodo_id`, `estado`, `fecha_creacion`) VALUES
(1, 1, 1, 1, '2025-12-01 14:52:41'),
(2, 1, 2, 1, '2025-12-01 14:52:41'),
(3, 1, 3, 1, '2025-12-01 14:52:41'),
(4, 1, 4, 1, '2025-12-01 14:52:41'),
(5, 1, 5, 1, '2025-12-01 14:52:41'),
(6, 1, 6, 1, '2025-12-01 14:52:41'),
(7, 1, 7, 1, '2025-12-01 14:52:41'),
(8, 1, 8, 1, '2025-12-01 14:52:41'),
(9, 1, 9, 1, '2025-12-01 14:52:41'),
(10, 1, 10, 1, '2025-12-01 14:52:41'),
(11, 1, 11, 1, '2025-12-01 14:52:41'),
(12, 1, 12, 1, '2025-12-01 14:52:41'),
(13, 1, 13, 1, '2025-12-01 14:52:41'),
(14, 1, 14, 1, '2025-12-01 14:52:41'),
(15, 1, 15, 1, '2025-12-01 14:52:41'),
(16, 1, 16, 1, '2025-12-01 14:52:41'),
(17, 1, 17, 1, '2025-12-01 14:52:41'),
(18, 1, 18, 1, '2025-12-01 14:52:41'),
(19, 1, 19, 1, '2025-12-01 14:52:41'),
(20, 1, 20, 1, '2025-12-01 14:52:41'),
(21, 1, 21, 1, '2025-12-01 14:52:41'),
(22, 1, 22, 1, '2025-12-01 14:52:41'),
(23, 2, 1, 1, '2025-12-01 14:52:41'),
(24, 2, 2, 1, '2025-12-01 14:52:41'),
(25, 2, 3, 1, '2025-12-01 14:52:41'),
(26, 2, 4, 1, '2025-12-01 14:52:41'),
(27, 2, 5, 1, '2025-12-01 14:52:41'),
(28, 2, 6, 1, '2025-12-01 14:52:41'),
(29, 2, 7, 1, '2025-12-01 14:52:41'),
(30, 2, 8, 1, '2025-12-01 14:52:41'),
(31, 2, 9, 1, '2025-12-01 14:52:41'),
(32, 2, 10, 1, '2025-12-01 14:52:41'),
(33, 2, 11, 1, '2025-12-01 14:52:41'),
(34, 2, 12, 1, '2025-12-01 14:52:41'),
(35, 2, 13, 1, '2025-12-01 14:52:41'),
(36, 2, 14, 1, '2025-12-01 14:52:41'),
(37, 2, 15, 1, '2025-12-01 14:52:41'),
(38, 2, 16, 1, '2025-12-01 14:52:41'),
(39, 2, 17, 1, '2025-12-01 14:52:41'),
(40, 2, 18, 1, '2025-12-01 14:52:41'),
(41, 2, 19, 1, '2025-12-01 14:52:41'),
(42, 2, 20, 1, '2025-12-01 14:52:41'),
(43, 2, 21, 1, '2025-12-01 14:52:41'),
(44, 2, 22, 1, '2025-12-01 14:52:41'),
(45, 3, 1, 1, '2025-12-01 14:52:41'),
(46, 3, 2, 1, '2025-12-01 14:52:41'),
(47, 3, 3, 1, '2025-12-01 14:52:41'),
(48, 3, 4, 1, '2025-12-01 14:52:41'),
(49, 3, 5, 1, '2025-12-01 14:52:41'),
(50, 3, 6, 1, '2025-12-01 14:52:41'),
(51, 3, 7, 1, '2025-12-01 14:52:41'),
(52, 3, 8, 1, '2025-12-01 14:52:41'),
(53, 3, 9, 1, '2025-12-01 14:52:41'),
(54, 3, 10, 1, '2025-12-01 14:52:41'),
(55, 3, 11, 1, '2025-12-01 14:52:41'),
(56, 3, 12, 1, '2025-12-01 14:52:41'),
(57, 3, 13, 1, '2025-12-01 14:52:41'),
(58, 3, 14, 1, '2025-12-01 14:52:41'),
(59, 3, 15, 1, '2025-12-01 14:52:41'),
(60, 3, 16, 1, '2025-12-01 14:52:41'),
(61, 3, 17, 1, '2025-12-01 14:52:41'),
(62, 3, 18, 1, '2025-12-01 14:52:41'),
(63, 3, 19, 1, '2025-12-01 14:52:41'),
(64, 3, 20, 1, '2025-12-01 14:52:41'),
(65, 3, 21, 1, '2025-12-01 14:52:41'),
(66, 3, 22, 1, '2025-12-01 14:52:41'),
(67, 4, 1, 1, '2025-12-01 14:52:41'),
(68, 4, 2, 1, '2025-12-01 14:52:41'),
(69, 4, 3, 1, '2025-12-01 14:52:41'),
(70, 4, 4, 1, '2025-12-01 14:52:41'),
(71, 4, 5, 1, '2025-12-01 14:52:41'),
(72, 4, 6, 1, '2025-12-01 14:52:41'),
(73, 4, 7, 1, '2025-12-01 14:52:41'),
(74, 4, 8, 1, '2025-12-01 14:52:41'),
(75, 4, 9, 1, '2025-12-01 14:52:41'),
(76, 4, 10, 1, '2025-12-01 14:52:41'),
(77, 4, 11, 1, '2025-12-01 14:52:41'),
(78, 4, 12, 1, '2025-12-01 14:52:41'),
(79, 4, 13, 1, '2025-12-01 14:52:41'),
(80, 4, 14, 1, '2025-12-01 14:52:41'),
(81, 4, 15, 1, '2025-12-01 14:52:41'),
(82, 4, 16, 1, '2025-12-01 14:52:41'),
(83, 4, 17, 1, '2025-12-01 14:52:41'),
(84, 4, 18, 1, '2025-12-01 14:52:41'),
(85, 4, 19, 1, '2025-12-01 14:52:41'),
(86, 4, 20, 1, '2025-12-01 14:52:41'),
(87, 4, 21, 1, '2025-12-01 14:52:41'),
(88, 4, 22, 1, '2025-12-01 14:52:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `materiales`
--

CREATE TABLE `materiales` (
  `id` int(11) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `nodo_id` int(11) DEFAULT NULL,
  `linea_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `fecha_adquisicion` date DEFAULT NULL,
  `categoria` varchar(100) DEFAULT NULL,
  `presentacion` varchar(100) DEFAULT NULL,
  `medida` varchar(50) DEFAULT NULL,
  `cantidad` int(11) DEFAULT 0,
  `valor_compra` decimal(15,2) DEFAULT NULL,
  `proveedor` varchar(200) DEFAULT NULL,
  `marca` varchar(100) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `creado_por` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `materiales`
--

INSERT INTO `materiales` (`id`, `codigo`, `nodo_id`, `linea_id`, `nombre`, `fecha_adquisicion`, `categoria`, `presentacion`, `medida`, `cantidad`, `valor_compra`, `proveedor`, `marca`, `descripcion`, `estado`, `creado_por`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, '1', 9, 1, 'prueba', NULL, NULL, NULL, NULL, 10, NULL, NULL, NULL, 'prueba', 1, NULL, '2025-11-25 23:20:27', '2025-12-01 16:43:38'),
(2, 'wqq', 2, 1, 'wqw', NULL, NULL, NULL, NULL, 232, NULL, NULL, NULL, 'qwqw', 1, NULL, '2025-11-26 20:16:17', '2025-11-28 14:17:31'),
(3, '2454g', 3, 1, 'jgbhj', NULL, NULL, NULL, NULL, 5, NULL, NULL, NULL, 'gdghfr', 1, NULL, '2025-12-01 15:42:46', '2025-12-01 16:20:54');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material_archivos`
--

CREATE TABLE `material_archivos` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(50) DEFAULT NULL,
  `tamano` bigint(20) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `material_archivos`
--

INSERT INTO `material_archivos` (`id`, `material_id`, `nombre_original`, `nombre_archivo`, `tipo_archivo`, `tamano`, `usuario_id`, `fecha_creacion`) VALUES
(1, 1, 'Hoja_de_vida.pdf', 'uploads/materiales/20251126002038_Hoja_de_vida.pdf', 'application/pdf', 282947, 1, '2025-11-25 23:20:38'),
(2, 1, 'Presentacion.pptx', 'uploads/materiales/20251126140110_Presentacion.pptx', 'application/vnd.openxmlformats-officedocument.pres', 1641240, 1, '2025-11-26 13:01:10'),
(3, 2, '20251126140110_Presentacion.pptx', 'uploads/materiales/20251127165000_20251126140110_Presentacion.pptx', 'application/vnd.openxmlformats-officedocument.pres', 1641240, 1, '2025-11-27 15:50:00'),
(4, 2, 'Hoja_de_vida.pdf', 'uploads/materiales/20251128151729_Hoja_de_vida.pdf', 'application/pdf', 282947, 1, '2025-11-28 14:17:29'),
(5, 1, 'Proyecto fem fit.pdf', 'uploads/materiales/20251201164000_Proyecto_fem_fit.pdf', 'application/pdf', 4037198, 4, '2025-12-01 15:40:00'),
(8, 3, 'Proyecto fem fit.pdf', 'uploads/materiales/20251201164258_Proyecto_fem_fit.pdf', 'application/pdf', 4037198, 4, '2025-12-01 15:42:58');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `material_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `tipo_movimiento` enum('entrada','salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `documento_referencia` varchar(100) DEFAULT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nodos`
--

CREATE TABLE `nodos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `nodos`
--

INSERT INTO `nodos` (`id`, `nombre`, `ciudad`, `descripcion`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Nodo Bucaramanga', 'Bucaramanga', 'Centro de formación en Bucaramanga - Santander', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(2, 'Nodo Cúcuta', 'Cúcuta', 'Centro de formación en Cúcuta - Norte de Santander', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(3, 'Nodo Cali', 'Cali', 'Centro de formación en Cali - Valle del Cauca', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(4, 'Nodo Barranquilla', 'Barranquilla', 'Centro de formación en Barranquilla - Atlántico', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(5, 'Nodo Angostura', 'Angostura', 'Centro de formación en Angostura - Huila', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(6, 'Nodo Cazucá', 'Cazucá', 'Centro de formación en Cazucá - Cundinamarca', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(7, 'Nodo La Granja', 'La Granja', 'Centro de formación en La Granja - Cundinamarca', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(8, 'Nodo Manizales', 'Manizales', 'Centro de formación en Manizales - Caldas', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(9, 'Nodo Medellín', 'Medellín', 'Centro de formación en Medellín - Antioquia', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(10, 'Nodo Neiva', 'Neiva', 'Centro de formación en Neiva - Huila', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(11, 'Nodo Ocaña', 'Ocaña', 'Centro de formación en Ocaña - Norte de Santander', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(12, 'Nodo Pereira', 'Pereira', 'Centro de formación en Pereira - Risaralda', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(13, 'Nodo Pitalito', 'Pitalito', 'Centro de formación en Pitalito - Huila', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(14, 'Nodo Popayán', 'Popayán', 'Centro de formación en Popayán - Cauca', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(15, 'Nodo Rionegro', 'Rionegro', 'Centro de formación en Rionegro - Antioquia', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(16, 'Nodo Socorro', 'Socorro', 'Centro de formación en Socorro - Santander', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(17, 'Nodo Valledupar', 'Valledupar', 'Centro de formación en Valledupar - Cesar', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(18, 'Nodo Bogotá D.C.', 'Bogotá', 'Centro de formación en Bogotá D.C. - Cundinamarca', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(19, 'Nodo Arauca', 'Arauca', 'Centro de formación en Arauca - Arauca', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(20, 'Nodo Bolívar', 'Bolívar', 'Centro de formación en Bolívar - Cauca', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(21, 'Nodo Rural itinerante', 'Rural itinerante', 'Centro de formación Rural itinerante - Cobertura nacional', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41'),
(22, 'Nodo Extensión Rural', 'Extensión Rural', 'Centro de formación Extensión Rural - Cobertura nacional', 1, '2025-12-01 14:52:41', '2025-12-01 14:52:41');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos_usuario`
--

CREATE TABLE `permisos_usuario` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `linea_id` int(11) DEFAULT NULL,
  `nodo_id` int(11) NOT NULL,
  `puede_crear_material` tinyint(4) DEFAULT 0,
  `puede_editar_material` tinyint(4) DEFAULT 0,
  `puede_eliminar_material` tinyint(4) DEFAULT 0,
  `puede_entrada_material` tinyint(4) DEFAULT 0,
  `puede_salida_material` tinyint(4) DEFAULT 0,
  `puede_ver_auditoria` tinyint(4) DEFAULT 0,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `celular` varchar(20) DEFAULT NULL,
  `cargo` varchar(100) DEFAULT NULL,
  `rol` enum('admin','usuario','dinamizador') DEFAULT 'usuario',
  `foto` varchar(255) DEFAULT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `email_verified` tinyint(4) DEFAULT 0,
  `recovery_code` varchar(6) DEFAULT NULL,
  `recovery_expire` datetime DEFAULT NULL,
  `recovery_last_sent` datetime DEFAULT NULL,
  `verification_code` varchar(6) DEFAULT NULL,
  `verification_expire` datetime DEFAULT NULL,
  `verification_last_sent` datetime DEFAULT NULL,
  `nodo_id` int(11) DEFAULT NULL,
  `linea_id` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `correo`, `nombre_usuario`, `password`, `celular`, `cargo`, `rol`, `foto`, `estado`, `email_verified`, `recovery_code`, `recovery_expire`, `recovery_last_sent`, `verification_code`, `verification_expire`, `verification_last_sent`, `nodo_id`, `linea_id`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Mario', 'dantamarioso@gmail.com', 'dantarioso', '$2y$10$uFOsjcapKal9Pt0hK3JHduoloXmYPq0W3jXDnCawuwBUZOLXhAlHC', '3173037442', 'doctor', 'admin', 'uploads/fotos/foto_6926398c1512f.jpg', 1, 1, '967409', '2025-11-26 08:05:12', NULL, '392853', '2025-11-25 14:12:31', NULL, NULL, NULL, '2025-11-25 15:58:29', '2025-11-26 13:23:32'),
(2, 'Juan Bedoya', 'Lifejuca@gmail.com', 'Lifejuca@gmail.com', '$2y$10$1F8GnU5mmjTogR.5htCi7OHFtZFPXN/dclJ54wxUAR/p116Z0qU42', '', 'Gerente en Assasin Creed', 'usuario', 'uploads/fotos/foto_692610ee299a0.jpg', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 2, 1, '2025-11-25 20:17:40', '2025-11-28 19:13:58'),
(3, 'meme', 'danamarios.o@gmail.com', 'danamarios.o@gmail.com', '$2y$10$beysTFRd6vKjtUoe.3eczeyfooWrSk8UXlM1R4wc0m.hVCxCy1dE6', '', '', 'dinamizador', NULL, 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-11-25 20:28:36', '2025-11-25 23:23:46'),
(4, 'derly', 'lehoyej959@badfist.com', 'lehoyej959@badfist.com', '$2y$10$DEl9b6R0zHfBpkcffonGAuWrOFGf1V0jPljmxOt0pRH202gIevtO6', '3173037445', 'doctora', 'dinamizador', 'uploads/fotos/foto_692db61f234df.png', 1, 1, NULL, NULL, NULL, NULL, NULL, NULL, 1, NULL, '2025-12-01 15:35:30', '2025-12-01 15:37:48');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_estadisticas_materiales`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_estadisticas_materiales` (
`linea_id` int(11)
,`linea_nombre` varchar(100)
,`nodo_id` int(11)
,`nodo_nombre` varchar(100)
,`total_materiales` bigint(21)
,`cantidad_total` decimal(32,0)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_historial_usuarios_reciente`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_historial_usuarios_reciente` (
`id` int(11)
,`usuario_id` int(11)
,`accion` enum('crear','actualizar','eliminar')
,`fecha_cambio` timestamp
,`detalles` longtext
,`usuario_nombre` varchar(100)
,`modificado_por` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_materiales_con_detalles`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_materiales_con_detalles` (
`id` int(11)
,`codigo` varchar(50)
,`nombre` varchar(100)
,`cantidad` int(11)
,`estado` tinyint(4)
,`linea_nombre` varchar(100)
,`nodo_nombre` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `v_usuarios_con_nodo_linea`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `v_usuarios_con_nodo_linea` (
`id` int(11)
,`nombre` varchar(100)
,`correo` varchar(100)
,`rol` enum('admin','usuario','dinamizador')
,`estado` tinyint(4)
,`email_verified` tinyint(4)
,`nodo_nombre` varchar(100)
,`linea_nombre` varchar(100)
);

-- --------------------------------------------------------

--
-- Estructura para la vista `v_estadisticas_materiales`
--
DROP TABLE IF EXISTS `v_estadisticas_materiales`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_estadisticas_materiales`  AS SELECT `l`.`id` AS `linea_id`, `l`.`nombre` AS `linea_nombre`, `n`.`id` AS `nodo_id`, `n`.`nombre` AS `nodo_nombre`, count(`m`.`id`) AS `total_materiales`, sum(`m`.`cantidad`) AS `cantidad_total` FROM (((`lineas` `l` left join `linea_nodo` `ln` on(`l`.`id` = `ln`.`linea_id`)) left join `nodos` `n` on(`ln`.`nodo_id` = `n`.`id`)) left join `materiales` `m` on(`m`.`linea_id` = `l`.`id` and `m`.`nodo_id` = `n`.`id`)) GROUP BY `l`.`id`, `n`.`id` ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_historial_usuarios_reciente`
--
DROP TABLE IF EXISTS `v_historial_usuarios_reciente`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_historial_usuarios_reciente`  AS SELECT `a`.`id` AS `id`, `a`.`usuario_id` AS `usuario_id`, `a`.`accion` AS `accion`, `a`.`fecha_cambio` AS `fecha_cambio`, `a`.`detalles` AS `detalles`, `u`.`nombre` AS `usuario_nombre`, `admin`.`nombre` AS `modificado_por` FROM ((`auditoria_usuarios` `a` left join `usuarios` `u` on(`a`.`usuario_id` = `u`.`id`)) left join `usuarios` `admin` on(`a`.`admin_id` = `admin`.`id`)) ORDER BY `a`.`fecha_cambio` DESC LIMIT 0, 100 ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_materiales_con_detalles`
--
DROP TABLE IF EXISTS `v_materiales_con_detalles`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_materiales_con_detalles`  AS SELECT `m`.`id` AS `id`, `m`.`codigo` AS `codigo`, `m`.`nombre` AS `nombre`, `m`.`cantidad` AS `cantidad`, `m`.`estado` AS `estado`, `l`.`nombre` AS `linea_nombre`, `n`.`nombre` AS `nodo_nombre` FROM ((`materiales` `m` left join `lineas` `l` on(`m`.`linea_id` = `l`.`id`)) left join `nodos` `n` on(`m`.`nodo_id` = `n`.`id`)) ;

-- --------------------------------------------------------

--
-- Estructura para la vista `v_usuarios_con_nodo_linea`
--
DROP TABLE IF EXISTS `v_usuarios_con_nodo_linea`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_usuarios_con_nodo_linea`  AS SELECT `u`.`id` AS `id`, `u`.`nombre` AS `nombre`, `u`.`correo` AS `correo`, `u`.`rol` AS `rol`, `u`.`estado` AS `estado`, `u`.`email_verified` AS `email_verified`, `n`.`nombre` AS `nodo_nombre`, `l`.`nombre` AS `linea_nombre` FROM ((`usuarios` `u` left join `nodos` `n` on(`u`.`nodo_id` = `n`.`id`)) left join `lineas` `l` on(`u`.`linea_id` = `l`.`id`)) ;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria_materiales`
--
ALTER TABLE `auditoria_materiales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_id` (`material_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_fecha` (`fecha_cambio`),
  ADD KEY `idx_material_fecha` (`material_id`,`fecha_cambio`);

--
-- Indices de la tabla `auditoria_usuarios`
--
ALTER TABLE `auditoria_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_fecha` (`fecha_cambio`),
  ADD KEY `idx_usuario_fecha` (`usuario_id`,`fecha_cambio`);

--
-- Indices de la tabla `lineas`
--
ALTER TABLE `lineas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_estado` (`estado`);

--
-- Indices de la tabla `linea_nodo`
--
ALTER TABLE `linea_nodo`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_linea_nodo` (`linea_id`,`nodo_id`),
  ADD KEY `idx_linea_id` (`linea_id`),
  ADD KEY `idx_nodo_id` (`nodo_id`),
  ADD KEY `idx_estado_fecha` (`estado`,`fecha_creacion`);

--
-- Indices de la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_codigo` (`codigo`),
  ADD KEY `idx_codigo` (`codigo`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_linea_id` (`linea_id`),
  ADD KEY `idx_nodo_id` (`nodo_id`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_creado_por` (`creado_por`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`),
  ADD KEY `idx_cantidad` (`cantidad`),
  ADD KEY `idx_linea_nodo` (`linea_id`,`nodo_id`);

--
-- Indices de la tabla `material_archivos`
--
ALTER TABLE `material_archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_id` (`material_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_fecha` (`fecha_creacion`);

--
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_material_id` (`material_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_tipo` (`tipo_movimiento`),
  ADD KEY `idx_fecha` (`fecha_movimiento`),
  ADD KEY `idx_material_fecha` (`material_id`,`fecha_movimiento`),
  ADD KEY `idx_usuario_fecha` (`usuario_id`,`fecha_movimiento`),
  ADD KEY `idx_tipo_fecha` (`tipo_movimiento`,`fecha_movimiento`);

--
-- Indices de la tabla `nodos`
--
ALTER TABLE `nodos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_nombre` (`nombre`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_ciudad` (`ciudad`);

--
-- Indices de la tabla `permisos_usuario`
--
ALTER TABLE `permisos_usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_usuario_nodo` (`usuario_id`,`nodo_id`),
  ADD KEY `idx_usuario_id` (`usuario_id`),
  ADD KEY `idx_linea_id` (`linea_id`),
  ADD KEY `idx_nodo_id` (`nodo_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_correo` (`correo`),
  ADD UNIQUE KEY `uk_nombre_usuario` (`nombre_usuario`),
  ADD KEY `idx_correo` (`correo`),
  ADD KEY `idx_nombre_usuario` (`nombre_usuario`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_email_verified` (`email_verified`),
  ADD KEY `idx_nombre` (`nombre`),
  ADD KEY `idx_creation_date` (`fecha_creacion`),
  ADD KEY `idx_nodo_linea` (`nodo_id`,`linea_id`),
  ADD KEY `fk_usuarios_nodos` (`nodo_id`),
  ADD KEY `fk_usuarios_lineas` (`linea_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria_materiales`
--
ALTER TABLE `auditoria_materiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `auditoria_usuarios`
--
ALTER TABLE `auditoria_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `lineas`
--
ALTER TABLE `lineas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `linea_nodo`
--
ALTER TABLE `linea_nodo`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=89;

--
-- AUTO_INCREMENT de la tabla `materiales`
--
ALTER TABLE `materiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `material_archivos`
--
ALTER TABLE `material_archivos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nodos`
--
ALTER TABLE `nodos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `permisos_usuario`
--
ALTER TABLE `permisos_usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `auditoria_materiales`
--
ALTER TABLE `auditoria_materiales`
  ADD CONSTRAINT `auditoria_materiales_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `auditoria_materiales_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `auditoria_usuarios`
--
ALTER TABLE `auditoria_usuarios`
  ADD CONSTRAINT `auditoria_usuarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `auditoria_usuarios_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `linea_nodo`
--
ALTER TABLE `linea_nodo`
  ADD CONSTRAINT `linea_nodo_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `linea_nodo_ibfk_2` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `materiales`
--
ALTER TABLE `materiales`
  ADD CONSTRAINT `materiales_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materiales_ibfk_2` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `materiales_ibfk_3` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `material_archivos`
--
ALTER TABLE `material_archivos`
  ADD CONSTRAINT `material_archivos_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `material_archivos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `permisos_usuario`
--
ALTER TABLE `permisos_usuario`
  ADD CONSTRAINT `permisos_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `permisos_usuario_ibfk_2` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `permisos_usuario_ibfk_3` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_lineas` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_usuarios_nodos` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL;

DELIMITER $$
--
-- Eventos
--
CREATE DEFINER=`root`@`localhost` EVENT `ev_limpiar_auditoria_antigua` ON SCHEDULE EVERY 1 DAY STARTS '2025-11-25 14:52:42' ON COMPLETION PRESERVE ENABLE DO BEGIN
    DELETE FROM auditoria_materiales WHERE fecha_cambio < DATE_SUB(NOW(), INTERVAL 180 DAY);
    DELETE FROM auditoria_usuarios WHERE fecha_cambio < DATE_SUB(NOW(), INTERVAL 180 DAY);
END$$

CREATE DEFINER=`root`@`localhost` EVENT `ev_limpiar_codigos_expirados` ON SCHEDULE EVERY 1 HOUR STARTS '2025-11-25 14:52:42' ON COMPLETION PRESERVE ENABLE DO BEGIN
    UPDATE usuarios SET recovery_code = NULL, recovery_expire = NULL WHERE recovery_expire IS NOT NULL AND recovery_expire < NOW();
    UPDATE usuarios SET verification_code = NULL, verification_expire = NULL WHERE verification_expire IS NOT NULL AND verification_expire < NOW();
END$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 25-11-2025
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

-- ========================================
-- TABLAS
-- ========================================

-- --------------------------------------------------------
-- Tabla: nodos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `nodos`;
CREATE TABLE `nodos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_nombre` (`nombre`),
  KEY `idx_estado` (`estado`),
  KEY `idx_ciudad` (`ciudad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `nodos` (`nombre`, `ciudad`, `descripcion`, `estado`) VALUES
('NODO_CALI', 'Cali', 'Centro de formación en Cali - Valle del Cauca', 1),
('NODO_BOGOTA', 'Bogotá', 'Centro de formación en Bogotá - Cundinamarca', 1),
('NODO_MEDELLIN', 'Medellín', 'Centro de formación en Medellín - Antioquia', 1),
('NODO_BARRANQUILLA', 'Barranquilla', 'Centro de formación en Barranquilla - Atlántico', 1),
('NODO_CARTAGENA', 'Cartagena', 'Centro de formación en Cartagena - Bolívar', 1);

-- --------------------------------------------------------
-- Tabla: lineas
-- --------------------------------------------------------

DROP TABLE IF EXISTS `lineas`;
CREATE TABLE `lineas` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_nombre` (`nombre`),
  KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `lineas` (`nombre`, `descripcion`, `estado`) VALUES
('BIOTECNOLOGÍA Y NANOTECNOLOGÍA', 'Línea especializada en biotecnología y nanotecnología', 1),
('INGENIERÍA Y DISEÑO', 'Línea de ingeniería y diseño', 1),
('ELECTRÓNICA Y TELECOMUNICACIONES', 'Línea de electrónica y telecomunicaciones', 1),
('TECNOLOGÍAS VIRTUALES', 'Línea de tecnologías virtuales y desarrollo digital', 1),
('ENFERMERÍA', 'Línea de enfermería y ciencias de la salud', 1);

-- --------------------------------------------------------
-- Tabla: linea_nodo (Relación muchos a muchos)
-- --------------------------------------------------------

DROP TABLE IF EXISTS `linea_nodo`;
CREATE TABLE `linea_nodo` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `linea_id` int(11) NOT NULL,
  `nodo_id` int(11) NOT NULL,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_linea_nodo` (`linea_id`, `nodo_id`),
  KEY `idx_linea_id` (`linea_id`),
  KEY `idx_nodo_id` (`nodo_id`),
  KEY `idx_estado_fecha` (`estado`, `fecha_creacion`),
  CONSTRAINT `linea_nodo_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `linea_nodo_ibfk_2` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `linea_nodo` (`linea_id`, `nodo_id`) VALUES
(1, 1), (1, 2), (1, 3), (1, 4), (1, 5),
(2, 1), (2, 2), (2, 3), (2, 4), (2, 5),
(3, 1), (3, 2), (3, 3), (3, 4), (3, 5),
(4, 1), (4, 2), (4, 3), (4, 4), (4, 5),
(5, 1), (5, 2), (5, 3), (5, 4), (5, 5);

-- --------------------------------------------------------
-- Tabla: usuarios
-- --------------------------------------------------------

DROP TABLE IF EXISTS `usuarios`;
CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `nombre_usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `celular` varchar(20),
  `cargo` varchar(100),
  `rol` enum('admin', 'usuario', 'dinamizador') DEFAULT 'usuario',
  `foto` varchar(255),
  `estado` tinyint(4) DEFAULT 1,
  `email_verified` tinyint(4) DEFAULT 0,
  `recovery_code` varchar(6),
  `recovery_expire` datetime,
  `recovery_last_sent` datetime,
  `verification_code` varchar(6),
  `verification_expire` datetime,
  `verification_last_sent` datetime,
  `nodo_id` int(11),
  `linea_id` int(11),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_correo` (`correo`),
  UNIQUE KEY `uk_nombre_usuario` (`nombre_usuario`),
  KEY `idx_correo` (`correo`),
  KEY `idx_nombre_usuario` (`nombre_usuario`),
  KEY `idx_rol` (`rol`),
  KEY `idx_estado` (`estado`),
  KEY `idx_email_verified` (`email_verified`),
  KEY `idx_nombre` (`nombre`),
  KEY `idx_creation_date` (`fecha_creacion`),
  KEY `idx_nodo_linea` (`nodo_id`, `linea_id`),
  KEY `fk_usuarios_nodos` (`nodo_id`),
  KEY `fk_usuarios_lineas` (`linea_id`),
  CONSTRAINT `fk_usuarios_nodos` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_usuarios_lineas` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: auditoria_usuarios
-- --------------------------------------------------------

DROP TABLE IF EXISTS `auditoria_usuarios`;
CREATE TABLE `auditoria_usuarios` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11),
  `accion` enum('CREATE', 'UPDATE', 'DELETE') NOT NULL,
  `detalles` longtext NOT NULL,
  `admin_id` int(11),
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  `ip_address` varchar(45),
  PRIMARY KEY (`id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_fecha` (`fecha_cambio`),
  KEY `idx_usuario_fecha` (`usuario_id`, `fecha_cambio`),
  CONSTRAINT `auditoria_usuarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL,
  CONSTRAINT `auditoria_usuarios_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: materiales
-- --------------------------------------------------------

DROP TABLE IF EXISTS `materiales`;
CREATE TABLE `materiales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `codigo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text,
  `linea_id` int(11),
  `nodo_id` int(11),
  `cantidad` int(11) DEFAULT 0,
  `estado` tinyint(4) DEFAULT 1,
  `creado_por` int(11),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_codigo` (`codigo`),
  KEY `idx_codigo` (`codigo`),
  KEY `idx_nombre` (`nombre`),
  KEY `idx_linea_id` (`linea_id`),
  KEY `idx_nodo_id` (`nodo_id`),
  KEY `idx_estado` (`estado`),
  KEY `idx_creado_por` (`creado_por`),
  KEY `idx_fecha_creacion` (`fecha_creacion`),
  KEY `idx_cantidad` (`cantidad`),
  KEY `idx_linea_nodo` (`linea_id`, `nodo_id`),
  CONSTRAINT `materiales_ibfk_1` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `materiales_ibfk_2` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE SET NULL,
  CONSTRAINT `materiales_ibfk_3` FOREIGN KEY (`creado_por`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: auditoria_materiales
-- --------------------------------------------------------

DROP TABLE IF EXISTS `auditoria_materiales`;
CREATE TABLE `auditoria_materiales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11),
  `accion` enum('CREATE', 'UPDATE', 'DELETE') NOT NULL,
  `detalles` longtext NOT NULL,
  `admin_id` int(11),
  `ip_address` varchar(45),
  `fecha_cambio` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_material_id` (`material_id`),
  KEY `idx_admin_id` (`admin_id`),
  KEY `idx_accion` (`accion`),
  KEY `idx_fecha` (`fecha_cambio`),
  KEY `idx_material_fecha` (`material_id`, `fecha_cambio`),
  CONSTRAINT `auditoria_materiales_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `auditoria_materiales_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: material_archivos
-- --------------------------------------------------------

DROP TABLE IF EXISTS `material_archivos`;
CREATE TABLE `material_archivos` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) NOT NULL,
  `nombre_original` varchar(255) NOT NULL,
  `nombre_archivo` varchar(255) NOT NULL,
  `tipo_archivo` varchar(50),
  `tamano` bigint(20) NOT NULL,
  `usuario_id` int(11),
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_material_id` (`material_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_fecha` (`fecha_creacion`),
  CONSTRAINT `material_archivos_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `material_archivos_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: movimientos_inventario
-- --------------------------------------------------------

DROP TABLE IF EXISTS `movimientos_inventario`;
CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `material_id` int(11) NOT NULL,
  `usuario_id` int(11),
  `tipo_movimiento` enum('entrada', 'salida') NOT NULL,
  `cantidad` int(11) NOT NULL,
  `descripcion` text,
  `documento_referencia` varchar(100),
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_material_id` (`material_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_tipo` (`tipo_movimiento`),
  KEY `idx_fecha` (`fecha_movimiento`),
  KEY `idx_material_fecha` (`material_id`, `fecha_movimiento`),
  KEY `idx_usuario_fecha` (`usuario_id`, `fecha_movimiento`),
  KEY `idx_tipo_fecha` (`tipo_movimiento`, `fecha_movimiento`),
  CONSTRAINT `movimientos_inventario_ibfk_1` FOREIGN KEY (`material_id`) REFERENCES `materiales` (`id`) ON DELETE CASCADE,
  CONSTRAINT `movimientos_inventario_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Tabla: permisos_usuario
-- --------------------------------------------------------

DROP TABLE IF EXISTS `permisos_usuario`;
CREATE TABLE `permisos_usuario` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `usuario_id` int(11) NOT NULL,
  `linea_id` int(11),
  `nodo_id` int(11) NOT NULL,
  `puede_crear_material` tinyint(4) DEFAULT 0,
  `puede_editar_material` tinyint(4) DEFAULT 0,
  `puede_eliminar_material` tinyint(4) DEFAULT 0,
  `puede_entrada_material` tinyint(4) DEFAULT 0,
  `puede_salida_material` tinyint(4) DEFAULT 0,
  `puede_ver_auditoria` tinyint(4) DEFAULT 0,
  `estado` tinyint(4) DEFAULT 1,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_usuario_nodo` (`usuario_id`, `nodo_id`),
  KEY `idx_usuario_id` (`usuario_id`),
  KEY `idx_linea_id` (`linea_id`),
  KEY `idx_nodo_id` (`nodo_id`),
  CONSTRAINT `permisos_usuario_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  CONSTRAINT `permisos_usuario_ibfk_2` FOREIGN KEY (`linea_id`) REFERENCES `lineas` (`id`) ON DELETE SET NULL,
  CONSTRAINT `permisos_usuario_ibfk_3` FOREIGN KEY (`nodo_id`) REFERENCES `nodos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ========================================
-- TRIGGERS
-- ========================================

DELIMITER $$

DROP TRIGGER IF EXISTS `audit_usuario_create` $$
CREATE TRIGGER `audit_usuario_create` AFTER INSERT ON `usuarios` FOR EACH ROW
BEGIN
    INSERT INTO auditoria_usuarios (usuario_id, accion, detalles, admin_id, ip_address)
    VALUES (
        NEW.id,
        'CREATE',
        CONCAT('{"nombre":"', NEW.nombre, '","correo":"', NEW.correo, '","rol":"', NEW.rol, '"}'),
        IF(IFNULL(@usuario_id, 0) > 0, @usuario_id, NULL),
        IF(IFNULL(@ip_address, '') != '', @ip_address, NULL)
    );
END$$

DROP TRIGGER IF EXISTS `audit_usuario_update` $$
CREATE TRIGGER `audit_usuario_update` AFTER UPDATE ON `usuarios` FOR EACH ROW
BEGIN
    IF (NEW.nombre <> OLD.nombre OR NEW.correo <> OLD.correo OR NEW.nombre_usuario <> OLD.nombre_usuario 
        OR NEW.celular <> OLD.celular OR NEW.cargo <> OLD.cargo OR NEW.rol <> OLD.rol 
        OR NEW.estado <> OLD.estado OR NEW.foto <> OLD.foto OR NEW.nodo_id <> OLD.nodo_id 
        OR NEW.linea_id <> OLD.linea_id) THEN
        INSERT INTO auditoria_usuarios (usuario_id, accion, detalles, admin_id, ip_address)
        VALUES (
            NEW.id,
            'UPDATE',
            CONCAT('{"cambios":"usuario actualizado"}'),
            IF(IFNULL(@usuario_id, 0) > 0, @usuario_id, NULL),
            IF(IFNULL(@ip_address, '') != '', @ip_address, NULL)
        );
    END IF;
END$$

DROP TRIGGER IF EXISTS `audit_usuario_delete` $$
CREATE TRIGGER `audit_usuario_delete` BEFORE DELETE ON `usuarios` FOR EACH ROW
BEGIN
    INSERT INTO auditoria_usuarios (usuario_id, accion, detalles, admin_id, ip_address)
    VALUES (
        OLD.id,
        'DELETE',
        CONCAT('{"nombre":"', OLD.nombre, '","correo":"', OLD.correo, '"}'),
        IF(IFNULL(@usuario_id, 0) > 0, @usuario_id, NULL),
        IF(IFNULL(@ip_address, '') != '', @ip_address, NULL)
    );
END$$

DROP TRIGGER IF EXISTS `audit_material_create` $$
CREATE TRIGGER `audit_material_create` AFTER INSERT ON `materiales` FOR EACH ROW
BEGIN
    INSERT INTO auditoria_materiales (material_id, accion, detalles, admin_id, ip_address)
    VALUES (
        NEW.id,
        'CREATE',
        CONCAT('{"codigo":"', NEW.codigo, '","nombre":"', NEW.nombre, '"}'),
        NEW.creado_por,
        IF(IFNULL(@ip_address, '') != '', @ip_address, NULL)
    );
END$$

DROP TRIGGER IF EXISTS `audit_material_update` $$
CREATE TRIGGER `audit_material_update` AFTER UPDATE ON `materiales` FOR EACH ROW
BEGIN
    IF (NEW.nombre <> OLD.nombre OR NEW.codigo <> OLD.codigo OR NEW.descripcion <> OLD.descripcion 
        OR NEW.cantidad <> OLD.cantidad OR NEW.estado <> OLD.estado 
        OR NEW.linea_id <> OLD.linea_id OR NEW.nodo_id <> OLD.nodo_id) THEN
        INSERT INTO auditoria_materiales (material_id, accion, detalles, admin_id, ip_address)
        VALUES (
            NEW.id,
            'UPDATE',
            CONCAT('{"cambios":"material actualizado"}'),
            IF(IFNULL(@usuario_id, 0) > 0, @usuario_id, NULL),
            IF(IFNULL(@ip_address, '') != '', @ip_address, NULL)
        );
    END IF;
END$$

DROP TRIGGER IF EXISTS `audit_material_delete` $$
CREATE TRIGGER `audit_material_delete` BEFORE DELETE ON `materiales` FOR EACH ROW
BEGIN
    INSERT INTO auditoria_materiales (material_id, accion, detalles, admin_id, ip_address)
    VALUES (
        OLD.id,
        'DELETE',
        CONCAT('{"codigo":"', OLD.codigo, '","nombre":"', OLD.nombre, '"}'),
        IF(IFNULL(@usuario_id, 0) > 0, @usuario_id, NULL),
        IF(IFNULL(@ip_address, '') != '', @ip_address, NULL)
    );
END$$

DELIMITER ;

-- ========================================
-- VISTAS
-- ========================================

DROP VIEW IF EXISTS `v_estadisticas_materiales`;
CREATE VIEW `v_estadisticas_materiales` AS
SELECT 
    l.id as linea_id,
    l.nombre as linea_nombre,
    n.id as nodo_id,
    n.nombre as nodo_nombre,
    COUNT(m.id) as total_materiales,
    SUM(m.cantidad) as cantidad_total
FROM lineas l
LEFT JOIN linea_nodo ln ON l.id = ln.linea_id
LEFT JOIN nodos n ON ln.nodo_id = n.id
LEFT JOIN materiales m ON m.linea_id = l.id AND m.nodo_id = n.id
GROUP BY l.id, n.id;

DROP VIEW IF EXISTS `v_historial_usuarios_reciente`;
CREATE VIEW `v_historial_usuarios_reciente` AS
SELECT 
    a.id,
    a.usuario_id,
    a.accion,
    a.fecha_cambio,
    a.detalles,
    u.nombre as usuario_nombre,
    admin.nombre as modificado_por
FROM auditoria_usuarios a
LEFT JOIN usuarios u ON a.usuario_id = u.id
LEFT JOIN usuarios admin ON a.admin_id = admin.id
ORDER BY a.fecha_cambio DESC
LIMIT 100;

DROP VIEW IF EXISTS `v_usuarios_con_nodo_linea`;
CREATE VIEW `v_usuarios_con_nodo_linea` AS
SELECT 
    u.id,
    u.nombre,
    u.correo,
    u.rol,
    u.estado,
    u.email_verified,
    n.nombre as nodo_nombre,
    l.nombre as linea_nombre
FROM usuarios u
LEFT JOIN nodos n ON u.nodo_id = n.id
LEFT JOIN lineas l ON u.linea_id = l.id;

DROP VIEW IF EXISTS `v_materiales_con_detalles`;
CREATE VIEW `v_materiales_con_detalles` AS
SELECT 
    m.id,
    m.codigo,
    m.nombre,
    m.cantidad,
    m.estado,
    l.nombre as linea_nombre,
    n.nombre as nodo_nombre
FROM materiales m
LEFT JOIN lineas l ON m.linea_id = l.id
LEFT JOIN nodos n ON m.nodo_id = n.id;

-- ========================================
-- EVENTOS AUTOMÁTICOS
-- ========================================

DELIMITER $$

DROP EVENT IF EXISTS `ev_limpiar_auditoria_antigua` $$
CREATE EVENT `ev_limpiar_auditoria_antigua`
ON SCHEDULE EVERY 1 DAY
STARTS CURRENT_TIMESTAMP
ON COMPLETION PRESERVE
ENABLE
DO
BEGIN
    DELETE FROM auditoria_materiales WHERE fecha_cambio < DATE_SUB(NOW(), INTERVAL 180 DAY);
    DELETE FROM auditoria_usuarios WHERE fecha_cambio < DATE_SUB(NOW(), INTERVAL 180 DAY);
END$$

DROP EVENT IF EXISTS `ev_limpiar_codigos_expirados` $$
CREATE EVENT `ev_limpiar_codigos_expirados`
ON SCHEDULE EVERY 1 HOUR
STARTS CURRENT_TIMESTAMP
ON COMPLETION PRESERVE
ENABLE
DO
BEGIN
    UPDATE usuarios SET recovery_code = NULL, recovery_expire = NULL WHERE recovery_expire IS NOT NULL AND recovery_expire < NOW();
    UPDATE usuarios SET verification_code = NULL, verification_expire = NULL WHERE verification_expire IS NOT NULL AND verification_expire < NOW();
END$$

DELIMITER ;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

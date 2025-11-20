-- ============================================================
-- TABLAS PARA SISTEMA DE GESTIÓN DE MATERIALES
-- ============================================================

-- Tabla de Líneas de Trabajo
CREATE TABLE IF NOT EXISTS `lineas` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nombre` VARCHAR(100) NOT NULL UNIQUE,
    `descripcion` TEXT,
    `estado` TINYINT DEFAULT 1,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    KEY `idx_nombre` (`nombre`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Materiales
CREATE TABLE IF NOT EXISTS `materiales` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `codigo` VARCHAR(50) NOT NULL UNIQUE,
    `nombre` VARCHAR(100) NOT NULL,
    `descripcion` TEXT,
    `linea_id` INT,
    `cantidad` INT DEFAULT 0,
    `estado` TINYINT DEFAULT 1,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `fecha_actualizacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`linea_id`) REFERENCES `lineas`(`id`) ON DELETE SET NULL,
    KEY `idx_codigo` (`codigo`),
    KEY `idx_nombre` (`nombre`),
    KEY `idx_linea_id` (`linea_id`),
    KEY `idx_estado` (`estado`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Movimientos de Inventario
CREATE TABLE IF NOT EXISTS `movimientos_inventario` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `material_id` INT NOT NULL,
    `usuario_id` INT NOT NULL,
    `tipo_movimiento` ENUM('entrada', 'salida') NOT NULL,
    `cantidad` INT NOT NULL,
    `descripcion` TEXT,
    `fecha_movimiento` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`material_id`) REFERENCES `materiales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT,
    KEY `idx_material_id` (`material_id`),
    KEY `idx_usuario_id` (`usuario_id`),
    KEY `idx_tipo` (`tipo_movimiento`),
    KEY `idx_fecha` (`fecha_movimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- INSERCIÓN DE LÍNEAS PREDEFINIDAS
-- ============================================================

INSERT INTO `lineas` (`nombre`, `descripcion`, `estado`) VALUES
('BIOTECNOLOGÍA Y NANOTECNOLOGÍA', 'Línea especializada en biotecnología y nanotecnología', 1),
('INGENIERÍA Y DISEÑO', 'Línea de ingeniería y diseño', 1),
('ELECTRÓNICA Y TELECOMUNICACIONES', 'Línea de electrónica y telecomunicaciones', 1),
('TECNOLOGÍAS VIRTUALES', 'Línea de tecnologías virtuales y desarrollo digital', 1),
('ENFERMERÍA', 'Línea de enfermería y ciencias de la salud', 1)
ON DUPLICATE KEY UPDATE estado = 1;

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

-- ============================================================
-- TRIGGERS PARA AUDITORÍA DE MATERIALES
-- ============================================================

DROP TRIGGER IF EXISTS `audit_material_create`;
DELIMITER $$
CREATE TRIGGER `audit_material_create`
AFTER INSERT ON `materiales`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria` (`usuario_id`, `tabla`, `accion`, `detalles`, `fecha_cambio`)
    VALUES (
        1,
        'materiales',
        'CREATE',
        JSON_OBJECT('id', NEW.`id`, 'codigo', NEW.`codigo`, 'nombre', NEW.`nombre`, 'cantidad', NEW.`cantidad`),
        NOW()
    );
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `audit_material_update`;
DELIMITER $$
CREATE TRIGGER `audit_material_update`
AFTER UPDATE ON `materiales`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria` (`usuario_id`, `tabla`, `accion`, `detalles`, `fecha_cambio`)
    VALUES (
        1,
        'materiales',
        'UPDATE',
        JSON_OBJECT(
            'id', NEW.`id`,
            'codigo', NEW.`codigo`,
            'nombre', NEW.`nombre`,
            'cantidad_anterior', OLD.`cantidad`,
            'cantidad_nueva', NEW.`cantidad`,
            'estado_anterior', OLD.`estado`,
            'estado_nuevo', NEW.`estado`
        ),
        NOW()
    );
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `audit_material_delete`;
DELIMITER $$
CREATE TRIGGER `audit_material_delete`
BEFORE DELETE ON `materiales`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria` (`usuario_id`, `tabla`, `accion`, `detalles`, `fecha_cambio`)
    VALUES (
        1,
        'materiales',
        'DELETE',
        JSON_OBJECT('id', OLD.`id`, 'codigo', OLD.`codigo`, 'nombre', OLD.`nombre`, 'cantidad', OLD.`cantidad`),
        NOW()
    );
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `audit_movimiento_create`;
DELIMITER $$
CREATE TRIGGER `audit_movimiento_create`
AFTER INSERT ON `movimientos_inventario`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria` (`usuario_id`, `tabla`, `accion`, `detalles`, `fecha_cambio`)
    VALUES (
        NEW.`usuario_id`,
        'movimientos_inventario',
        'CREATE',
        JSON_OBJECT('id', NEW.`id`, 'material_id', NEW.`material_id`, 'tipo', NEW.`tipo_movimiento`, 'cantidad', NEW.`cantidad`),
        NOW()
    );
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `audit_movimiento_delete`;
DELIMITER $$
CREATE TRIGGER `audit_movimiento_delete`
BEFORE DELETE ON `movimientos_inventario`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria` (`usuario_id`, `tabla`, `accion`, `detalles`, `fecha_cambio`)
    VALUES (
        OLD.`usuario_id`,
        'movimientos_inventario',
        'DELETE',
        JSON_OBJECT('id', OLD.`id`, 'material_id', OLD.`material_id`, 'tipo', OLD.`tipo_movimiento`, 'cantidad', OLD.`cantidad`),
        NOW()
    );
END$$
DELIMITER ;

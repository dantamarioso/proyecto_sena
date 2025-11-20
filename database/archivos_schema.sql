-- ============================================================
-- TABLA PARA GESTIÓN DE ARCHIVOS DE MATERIALES
-- ============================================================

-- Tabla de Archivos de Materiales
CREATE TABLE IF NOT EXISTS `material_archivos` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `material_id` INT NOT NULL,
    `nombre_original` VARCHAR(255) NOT NULL,
    `nombre_archivo` VARCHAR(255) NOT NULL,
    `tipo_archivo` VARCHAR(50),
    `tamaño` BIGINT NOT NULL,
    `usuario_id` INT NOT NULL,
    `fecha_creacion` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`material_id`) REFERENCES `materiales`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`usuario_id`) REFERENCES `usuarios`(`id`) ON DELETE RESTRICT,
    KEY `idx_material_id` (`material_id`),
    KEY `idx_usuario_id` (`usuario_id`),
    KEY `idx_fecha` (`fecha_creacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================
-- TRIGGERS PARA AUDITORÍA DE ARCHIVOS
-- ============================================================

DROP TRIGGER IF EXISTS `audit_archivo_create`;
DELIMITER $$
CREATE TRIGGER `audit_archivo_create`
AFTER INSERT ON `material_archivos`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria` (`usuario_id`, `tabla`, `accion`, `detalles`, `fecha_cambio`)
    VALUES (
        NEW.`usuario_id`,
        'material_archivos',
        'CREATE',
        JSON_OBJECT('id', NEW.`id`, 'material_id', NEW.`material_id`, 'nombre_archivo', NEW.`nombre_archivo`, 'tamaño', NEW.`tamaño`),
        NOW()
    );
END$$
DELIMITER ;

DROP TRIGGER IF EXISTS `audit_archivo_delete`;
DELIMITER $$
CREATE TRIGGER `audit_archivo_delete`
BEFORE DELETE ON `material_archivos`
FOR EACH ROW
BEGIN
    INSERT INTO `auditoria` (`usuario_id`, `tabla`, `accion`, `detalles`, `fecha_cambio`)
    VALUES (
        OLD.`usuario_id`,
        'material_archivos',
        'DELETE',
        JSON_OBJECT('id', OLD.`id`, 'material_id', OLD.`material_id`, 'nombre_archivo', OLD.`nombre_archivo`, 'tamaño', OLD.`tamaño`),
        NOW()
    );
END$$
DELIMITER ;

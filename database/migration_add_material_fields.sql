-- =============================================
-- Migración: Agregar nuevos campos a tabla materiales
-- Fecha: 4 de diciembre de 2025
-- Descripción: Añade campos fecha_adquisicion, categoria, presentacion, 
--              medida, valor_compra, proveedor, marca
-- =============================================

USE inventario_db;

-- Agregar nuevos campos a la tabla materiales
ALTER TABLE `materiales`
ADD COLUMN `fecha_adquisicion` DATE NULL AFTER `nombre`,
ADD COLUMN `categoria` VARCHAR(100) NULL AFTER `fecha_adquisicion`,
ADD COLUMN `presentacion` VARCHAR(100) NULL AFTER `categoria`,
ADD COLUMN `medida` VARCHAR(50) NULL AFTER `presentacion`,
ADD COLUMN `valor_compra` DECIMAL(15,2) NULL AFTER `cantidad`,
ADD COLUMN `proveedor` VARCHAR(200) NULL AFTER `valor_compra`,
ADD COLUMN `marca` VARCHAR(100) NULL AFTER `proveedor`;

-- Verificar que los campos se agregaron correctamente
DESCRIBE materiales;

-- Mostrar algunos registros de ejemplo
SELECT id, codigo, nombre, fecha_adquisicion, categoria, presentacion, 
       medida, cantidad, valor_compra, proveedor, marca 
FROM materiales 
LIMIT 5;

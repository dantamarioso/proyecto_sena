-- Migracion: Campos para "Lista maestra de materiales" (CSV/XLSX)
-- Fecha: 2026-02-09

-- 1) Nuevos campos solicitados por el formato de Excel
ALTER TABLE materiales
  ADD COLUMN cantidad_requerida INT(11) NOT NULL DEFAULT 0 AFTER cantidad,
  ADD COLUMN fecha_fabricacion DATE NULL AFTER valor_compra,
  ADD COLUMN fecha_vencimiento DATE NULL AFTER fecha_fabricacion,
  ADD COLUMN fabricante VARCHAR(200) NULL AFTER fecha_vencimiento,
  ADD COLUMN ubicacion VARCHAR(200) NULL AFTER fabricante,
  ADD COLUMN observacion TEXT NULL AFTER descripcion;

-- 2) Migrar datos existentes (opcional): usar marca/proveedor como fabricante si viene vacio
UPDATE materiales
SET fabricante = COALESCE(NULLIF(marca, ''), NULLIF(proveedor, ''), NULL)
WHERE fabricante IS NULL OR fabricante = '';

-- 3) Permitir codigos duplicados (el Excel puede traer "Pendiente")
-- Si no existe el indice, ignore el error.
ALTER TABLE materiales DROP INDEX uk_codigo;

-- 4) Indice auxiliar para busquedas
CREATE INDEX idx_materiales_codigo_nodo_linea ON materiales (codigo, nodo_id, linea_id);

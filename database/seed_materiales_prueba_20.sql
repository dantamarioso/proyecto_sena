-- Seed: 20 materiales de prueba (solo 3 vencidos)
-- Fecha: 2026-02-13
-- Nota: ejecutar DESPUES de la migracion de decimales si aplica.

-- Opcional (si existen triggers de auditoria que usen @usuario_id)
SET @usuario_id = 1;

INSERT INTO materiales (
  codigo, nodo_id, linea_id, nombre,
  descripcion, categoria, presentacion, medida,
  cantidad, cantidad_requerida,
  valor_compra, fecha_adquisicion, fecha_fabricacion, fecha_vencimiento,
  fabricante, ubicacion, proveedor, marca,
  observacion, estado
) VALUES
  ('TEST-MAT-0001', 1, 1, 'Material Prueba 01 - Resina Epoxi', 'Resina para pruebas de prototipado', 'Insumos', 'Kit', 'Unidad', 1.140, 2.500, 45000.00, '2026-01-20', '2026-01-10', '2030-12-31', 'GenLab', 'Bodega A - Estante 1', 'Proveedor Demo', 'Marca Demo', 'No vencido', 1),
  ('TEST-MAT-0002', 2, 2, 'Material Prueba 02 - Filamento PLA', 'Filamento para impresion 3D', 'Fabricacion', 'Rollo', 'Kg', 12.300, 20.000, 78000.00, '2026-02-01', '2026-01-15', NULL, 'PrintCo', 'Bodega B - Estante 2', 'Proveedor Demo', 'PLA', 'Sin fecha de vencimiento', 1),
  ('TEST-MAT-0003', 3, 3, 'Material Prueba 03 - Alcohol Isopropilico', 'Solvente de limpieza', 'Quimicos', 'Botella', 'L', 5.125, 10.000, 32000.00, '2026-01-05', '2025-12-20', '2026-01-10', 'ChemLab', 'Lab - Gabinete 1', 'Proveedor Demo', 'ISO', 'VENCIDO (prueba)', 1),
  ('TEST-MAT-0004', 4, 4, 'Material Prueba 04 - Bateria LiPo', 'Bateria para electronica', 'Electronica', 'Unidad', 'Unidad', 3.500, 5.000, 65000.00, '2026-02-03', '2026-01-25', '2030-05-01', 'PowerTech', 'Taller - Caja 3', 'Proveedor Demo', 'LiPo', 'No vencido', 1),
  ('TEST-MAT-0005', 5, 1, 'Material Prueba 05 - Sensor Ultrasonico', 'Sensor HC-SR04', 'Electronica', 'Unidad', 'Unidad', 8.750, 10.000, 18000.00, '2026-02-04', NULL, NULL, 'MakerParts', 'Taller - Gaveta 7', 'Proveedor Demo', 'HC', 'Sin vencimiento', 1),
  ('TEST-MAT-0006', 6, 2, 'Material Prueba 06 - Pintura Acrilica', 'Pintura para acabados', 'Insumos', 'Frasco', 'L', 0.750, 2.000, 12000.00, '2026-01-28', '2026-01-10', '2030-11-30', 'ColorMix', 'Bodega A - Estante 4', 'Proveedor Demo', 'Acril', 'No vencido', 1),
  ('TEST-MAT-0007', 7, 3, 'Material Prueba 07 - Cinta Kapton', 'Cinta termica para electronica', 'Electronica', 'Rollo', 'Unidad', 2.001, 3.000, 24000.00, '2026-02-02', NULL, '2030-03-15', 'TapePro', 'Taller - Gaveta 2', 'Proveedor Demo', 'Kapton', 'No vencido', 1),
  ('TEST-MAT-0008', 8, 4, 'Material Prueba 08 - Guantes Nitrilo', 'Guantes de proteccion', 'Seguridad', 'Caja', 'Unidad', 50.000, 100.000, 55000.00, '2026-02-06', '2026-01-30', '2030-09-01', 'SafeWear', 'Bodega B - Estante 1', 'Proveedor Demo', 'Nitrilo', 'No vencido', 1),
  ('TEST-MAT-0009', 9, 1, 'Material Prueba 09 - Placa Arduino Uno', 'Tarjeta de desarrollo', 'Electronica', 'Unidad', 'Unidad', 7.000, 10.000, 95000.00, '2026-01-18', NULL, NULL, 'Arduino', 'Taller - Gaveta 1', 'Proveedor Demo', 'UNO', 'Sin vencimiento', 1),
  ('TEST-MAT-0010', 10, 2, 'Material Prueba 10 - Tornilleria M3', 'Tornillos para ensamble', 'Ferreteria', 'Bolsa', 'Unidad', 250.500, 300.000, 15000.00, '2026-02-07', NULL, NULL, 'FixIt', 'Taller - Caja 1', 'Proveedor Demo', 'M3', 'Sin vencimiento', 1),
  ('TEST-MAT-0011', 11, 3, 'Material Prueba 11 - Flux Soldadura', 'Flux para soldadura', 'Electronica', 'Frasco', 'Unidad', 1.234, 2.000, 28000.00, '2026-01-22', '2026-01-05', '2030-01-01', 'SolderPro', 'Lab - Gabinete 2', 'Proveedor Demo', 'Flux', 'No vencido', 1),
  ('TEST-MAT-0012', 12, 4, 'Material Prueba 12 - Acido Citrico', 'Reactivo para pruebas', 'Quimicos', 'Bolsa', 'Kg', 0.500, 1.000, 22000.00, '2026-01-02', '2025-11-15', '2025-12-31', 'ChemLab', 'Lab - Gabinete 3', 'Proveedor Demo', 'Citric', 'VENCIDO (prueba)', 1),
  ('TEST-MAT-0013', 13, 1, 'Material Prueba 13 - Espuma EVA', 'Material para maquetas', 'Insumos', 'Pliego', 'Unidad', 15.250, 20.000, 8000.00, '2026-02-05', NULL, '2030-06-30', 'FoamCo', 'Bodega A - Estante 3', 'Proveedor Demo', 'EVA', 'No vencido', 1),
  ('TEST-MAT-0014', 14, 2, 'Material Prueba 14 - Cable UTP Cat6', 'Cableado de red', 'Telecom', 'Rollo', 'm', 30.000, 50.000, 110000.00, '2026-01-25', NULL, NULL, 'NetCo', 'Bodega B - Estante 4', 'Proveedor Demo', 'Cat6', 'Sin vencimiento', 1),
  ('TEST-MAT-0015', 15, 3, 'Material Prueba 15 - Bomba Peristaltica', 'Bomba para dosificacion', 'Mecanica', 'Unidad', 'Unidad', 1.210, 2.000, 185000.00, '2026-02-08', NULL, '2030-08-20', 'PumpTech', 'Taller - Caja 4', 'Proveedor Demo', 'Peri', 'No vencido', 1),
  ('TEST-MAT-0016', 16, 4, 'Material Prueba 16 - Tinta UV', 'Tinta para curado UV', 'Insumos', 'Frasco', 'L', 0.125, 0.500, 42000.00, '2026-02-09', '2026-01-20', '2030-02-10', 'InkLab', 'Lab - Estante 1', 'Proveedor Demo', 'UV', 'No vencido', 1),
  ('TEST-MAT-0017', 17, 1, 'Material Prueba 17 - Pegante Cianoacrilato', 'Pegante de secado rapido', 'Insumos', 'Unidad', 'Unidad', 4.000, 6.000, 9000.00, '2026-01-30', '2026-01-10', '2026-02-01', 'GlueCo', 'Bodega A - Estante 2', 'Proveedor Demo', 'CA', 'VENCIDO (prueba)', 1),
  ('TEST-MAT-0018', 18, 2, 'Material Prueba 18 - Mascarillas', 'Mascarillas de seguridad', 'Seguridad', 'Caja', 'Unidad', 120.000, 200.000, 48000.00, '2026-02-10', NULL, '2030-12-31', 'SafeWear', 'Bodega B - Estante 2', 'Proveedor Demo', 'Mask', 'No vencido', 1),
  ('TEST-MAT-0019', 19, 3, 'Material Prueba 19 - Pasta Termica', 'Pasta para disipacion', 'Electronica', 'Jeringa', 'Unidad', 0.333, 1.000, 15000.00, '2026-02-11', NULL, '2030-04-04', 'CoolTech', 'Taller - Gaveta 5', 'Proveedor Demo', 'Thermal', 'No vencido', 1),
  ('TEST-MAT-0020', 20, 4, 'Material Prueba 20 - Lija 400', 'Lija para acabados', 'Ferreteria', 'Paquete', 'Unidad', 25.000, 30.000, 6000.00, '2026-02-12', NULL, NULL, 'FixIt', 'Taller - Caja 2', 'Proveedor Demo', 'Lija', 'Sin vencimiento', 1);

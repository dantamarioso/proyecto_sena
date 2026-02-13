<?php

/**
 * Servicio para manejar exportaciones de materiales.
 * Separa la lógica de exportación del controlador principal.
 */
class MaterialExportService
{
    public function __construct() {}

    private function buildNodoDisplayName($nodoNombre)
    {
        $nodoNombre = trim((string)$nodoNombre);
        if ($nodoNombre === '') {
            return 'Todos';
        }

        // Evitar "Nodo Nodo X" en el titulo
        return preg_replace('/^nodo\s+/i', '', $nodoNombre);
    }

    /**
      * Exporta materiales a Excel (Lista Maestra) con el formato solicitado.
      * Columnas: No consecutivo, Codigo, Nombre, Descripcion, Fecha compra, Valor compra,
      * Fecha fabricacion, Fecha vencimiento, Fabricante, Unidad medida, Presentacion,
      * Categoria, Cantidad requerida, Cantidad en stock, Cantidad faltante, Ubicacion, Observacion
      */
    public function exportToExcel($materiales, $filename = 'materiales', $context = [])
    {
        require_once __DIR__ . '/../helpers/ExcelHelper.php';

        $excel = new ExcelHelper();

        $nodoNombre = $context['nodo_nombre'] ?? '';
        $nodoDisplay = $this->buildNodoDisplayName($nodoNombre);
        $titulo = 'Lista maestra de materiales - Tecnoparque Nodo ' . $nodoDisplay;

        $excel->createSheet('Lista Maestra');
        $excel->setTitle($titulo);

        $headers = [
            'No de consecutivo',
            'Código',
            'Nodo',
            'Línea',
            'Nombre del material',
            'Descripción material',
            'Fecha de compra',
            'Valor de compra',
            'Fecha de fabricación',
            'Fecha de vencimiento',
            'Fabricante',
            'Unidad de medida',
            'Presentación',
            'Categoría',
            'Cantidad requerida',
            'Cantidad en Stock',
            'Cantidad faltante',
            'Ubicación',
            'Observación'
        ];
        $excel->setHeaders($headers);

        $excel->setColumnFormats([
            7 => 'date',
            8 => 'currency',
            9 => 'date',
            10 => 'date',
        ]);

        $consecutivo = 1;
        foreach ($materiales as $material) {
            $req = (float)($material['cantidad_requerida'] ?? 0);
            $stock = (float)($material['cantidad'] ?? 0);
            $faltante = $req - $stock;

            $fabricante = $material['fabricante'] ?? '';
            if ($fabricante === '' || $fabricante === null) {
                $fabricante = $material['marca'] ?? ($material['proveedor'] ?? '');
            }

            $excel->addRow([
                $consecutivo,
                $material['codigo'] ?? '',
                $material['nodo_nombre'] ?? '',
                $material['linea_nombre'] ?? '',
                $material['nombre'] ?? '',
                $material['descripcion'] ?? '',
                $material['fecha_adquisicion'] ?? '',
                $material['valor_compra'] ?? '',
                $material['fecha_fabricacion'] ?? '',
                $material['fecha_vencimiento'] ?? '',
                $fabricante,
                $material['medida'] ?? '',
                $material['presentacion'] ?? '',
                $material['categoria'] ?? '',
                $req,
                $stock,
                $faltante > 0 ? $faltante : 0,
                $material['ubicacion'] ?? '',
                $material['observacion'] ?? ''
            ]);

            $consecutivo++;
        }

        $excel->download($filename . '.xlsx');
    }

    /**
      * Exporta materiales a CSV con el formato de la Lista Maestra.
      */
    public function exportToCSV($materiales, $filename = 'materiales', $context = [])
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8 (Excel lo reconoce correctamente)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        $nodoNombre = $context['nodo_nombre'] ?? '';
        $nodoDisplay = $this->buildNodoDisplayName($nodoNombre);
        $titulo = 'Lista maestra de materiales - Tecnoparque Nodo ' . $nodoDisplay;

        $headers = [
            'No de consecutivo',
            'Código',
            'Nodo',
            'Línea',
            'Nombre del material',
            'Descripción material',
            'Fecha de compra',
            'Valor de compra',
            'Fecha de fabricación',
            'Fecha de vencimiento',
            'Fabricante',
            'Unidad de medida',
            'Presentación',
            'Categoría',
            'Cantidad requerida',
            'Cantidad en Stock',
            'Cantidad faltante',
            'Ubicación',
            'Observación'
        ];

        // Titulo (primera fila) para que el CSV mantenga el mismo encabezado visual en Excel
        fputcsv($output, array_merge([$titulo], array_fill(0, max(count($headers) - 1, 0), '')), ';');

        // Encabezados
        fputcsv($output, $headers, ';');

        // Datos
        $consecutivo = 1;
        foreach ($materiales as $material) {
            $req = (float)($material['cantidad_requerida'] ?? 0);
            $stock = (float)($material['cantidad'] ?? 0);
            $faltante = $req - $stock;

            $fabricante = $material['fabricante'] ?? '';
            if ($fabricante === '' || $fabricante === null) {
                $fabricante = $material['marca'] ?? ($material['proveedor'] ?? '');
            }

            $valorCompra = '';
            if (isset($material['valor_compra']) && $material['valor_compra'] !== null && $material['valor_compra'] !== '') {
                $valorCompra = '$ ' . number_format((float)$material['valor_compra'], 0, ',', '.');
            }

            fputcsv($output, [
                $consecutivo,
                $material['codigo'] ?? '',
                $material['nodo_nombre'] ?? '',
                $material['linea_nombre'] ?? '',
                $material['nombre'] ?? '',
                $material['descripcion'] ?? '',
                isset($material['fecha_adquisicion']) && $material['fecha_adquisicion'] ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : '',
                $valorCompra,
                isset($material['fecha_fabricacion']) && $material['fecha_fabricacion'] ? date('d/m/Y', strtotime($material['fecha_fabricacion'])) : '',
                isset($material['fecha_vencimiento']) && $material['fecha_vencimiento'] ? date('d/m/Y', strtotime($material['fecha_vencimiento'])) : '',
                $fabricante,
                $material['medida'] ?? '',
                $material['presentacion'] ?? '',
                $material['categoria'] ?? '',
                $req,
                $stock,
                $faltante > 0 ? $faltante : 0,
                $material['ubicacion'] ?? '',
                $material['observacion'] ?? ''
            ], ';');

            $consecutivo++;
        }

        fclose($output);
        exit;
    }

    /**
     * Exporta materiales a PDF profesional (sin ID ni fecha creación).
     * Orden exacto: Código de material, Nodo, Línea, Nombre, Fecha de adquisición, Categoría, Presentación, Medida, Cantidad, Valor de compra, Proveedor, Marca, Estado
     */
    public function exportToPDF($materiales, $filename = 'materiales')
    {
        require_once __DIR__ . '/../helpers/PdfHelper.php';

        $pdf = new PdfHelper();

        $pdf->setTitle('Listado de Materiales - Inventario');

        // Tabla principal con información exacta según la imagen
        $headers = ['Código de material', 'Nodo', 'Línea', 'Nombre', 'Fecha de adquisición', 'Categoría', 'Presentación', 'Medida', 'Cantidad', 'Descripción', 'Valor de compra', 'Proveedor', 'Marca', 'Estado'];
        $data = [];

        foreach ($materiales as $material) {
            $data[] = [
                $material['codigo'] ?? '',
                substr($material['nodo_nombre'] ?? 'Sin asignar', 0, 15),
                substr($material['linea_nombre'] ?? 'Sin asignar', 0, 15),
                substr($material['nombre'] ?? '', 0, 20),
                isset($material['fecha_adquisicion']) ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : '',
                substr($material['categoria'] ?? '', 0, 15),
                substr($material['presentacion'] ?? '', 0, 15),
                $material['medida'] ?? '',
                $material['cantidad'] ?? 0,
                substr($material['descripcion'] ?? '', 0, 30),
                isset($material['valor_compra']) ? number_format($material['valor_compra'], 2, ',', '.') : '',
                substr($material['proveedor'] ?? '', 0, 15),
                substr($material['marca'] ?? '', 0, 15),
                ($material['estado'] == 1) ? 'Activo' : 'Inactivo',
            ];
        }

        $pdf->addTable($headers, $data);

        $pdf->download($filename . '_' . date('Y-m-d') . '.pdf');
    }

    /**
     * Exporta materiales a TXT con formato legible (sin ID ni fecha creación).
     * Orden exacto como XLSX.
     */
    public function exportToTXT($materiales, $filename = 'materiales')
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.txt"');

        // Título
        echo "╔════════════════════════════════════════════════════════════════════════════════╗\n";
        echo "║                    LISTADO DE MATERIALES - INVENTARIO                         ║\n";
        echo "╚════════════════════════════════════════════════════════════════════════════════╝\n\n";

        echo "Generado: " . date('d/m/Y H:i:s') . "\n";
        echo "Total de materiales: " . count($materiales) . "\n";
        echo str_repeat("═", 88) . "\n\n";

        foreach ($materiales as $index => $material) {
            echo "┌─ MATERIAL #" . ($index + 1) . " ─" . str_repeat("─", 60) . "┐\n";
            echo "│ Código:              " . str_pad($material['codigo'] ?? '', 60) . "│\n";
            echo "│ Nodo:                " . str_pad($material['nodo_nombre'] ?? 'Sin asignar', 60) . "│\n";
            echo "│ Línea:               " . str_pad($material['linea_nombre'] ?? 'Sin asignar', 60) . "│\n";
            echo "│ Nombre:              " . str_pad($material['nombre'] ?? '', 60) . "│\n";

            $fecha = isset($material['fecha_adquisicion']) ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : 'N/A';
            echo "│ Fecha Adquisición:   " . str_pad($fecha, 60) . "│\n";

            echo "│ Categoría:           " . str_pad($material['categoria'] ?? '', 60) . "│\n";
            echo "│ Presentación:        " . str_pad($material['presentacion'] ?? '', 60) . "│\n";
            echo "│ Medida:              " . str_pad($material['medida'] ?? '', 60) . "│\n";
            echo "│ Cantidad:            " . str_pad((string)($material['cantidad'] ?? 0), 60) . "│\n";
            echo "│ Descripción:         " . str_pad(substr($material['descripcion'] ?? '', 0, 55), 60) . "│\n";

            $valor = isset($material['valor_compra']) ? '$ ' . number_format($material['valor_compra'], 2, ',', '.') : 'N/A';
            echo "│ Valor Compra:        " . str_pad($valor, 60) . "│\n";

            echo "│ Proveedor:           " . str_pad($material['proveedor'] ?? '', 60) . "│\n";
            echo "│ Marca:               " . str_pad($material['marca'] ?? '', 60) . "│\n";

            $estado = ($material['estado'] == 1) ? 'Activo' : 'Inactivo';
            echo "│ Estado:              " . str_pad($estado, 60) . "│\n";
            echo "└" . str_repeat("─", 88) . "┘\n\n";
        }

        echo str_repeat("═", 88) . "\n";
        echo "FIN DEL REPORTE\n";

        exit;
    }

    /**
      * Exporta materiales a ZIP con 3 CSVs (Lista Maestra, Líneas, Nodos).
      */
    public function exportToZip($materiales, $filename = 'materiales', $lineas = [], $nodos = [], $context = [])
    {
        // Crear archivos temporales
        $tempDir = sys_get_temp_dir() . '/' . uniqid('materiales_');
        @mkdir($tempDir);

        // ===== CSV MATERIALES =====
        $materialesFile = $tempDir . '/materiales.csv';
        $handle = fopen($materialesFile, 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        // Titulo + encabezados (Lista Maestra)
        $headers = [
            'No de consecutivo',
            'Código',
            'Nodo',
            'Línea',
            'Nombre del material',
            'Descripción material',
            'Fecha de compra',
            'Valor de compra',
            'Fecha de fabricación',
            'Fecha de vencimiento',
            'Fabricante',
            'Unidad de medida',
            'Presentación',
            'Categoría',
            'Cantidad requerida',
            'Cantidad en Stock',
            'Cantidad faltante',
            'Ubicación',
            'Observación'
        ];

        $nodoNombre = $context['nodo_nombre'] ?? '';
        $nodoDisplay = $this->buildNodoDisplayName($nodoNombre);
        fputcsv($handle, array_merge(['Lista maestra de materiales - Tecnoparque Nodo ' . $nodoDisplay], array_fill(0, max(count($headers) - 1, 0), '')), ';');
        fputcsv($handle, $headers, ';');

        $consecutivo = 1;
        foreach ($materiales as $material) {
            $req = (float)($material['cantidad_requerida'] ?? 0);
            $stock = (float)($material['cantidad'] ?? 0);
            $faltante = $req - $stock;

            $fabricante = $material['fabricante'] ?? '';
            if ($fabricante === '' || $fabricante === null) {
                $fabricante = $material['marca'] ?? ($material['proveedor'] ?? '');
            }

            $valorCompra = '';
            if (isset($material['valor_compra']) && $material['valor_compra'] !== null && $material['valor_compra'] !== '') {
                $valorCompra = '$ ' . number_format((float)$material['valor_compra'], 0, ',', '.');
            }

            fputcsv($handle, [
                $consecutivo,
                $material['codigo'] ?? '',
                $material['nodo_nombre'] ?? '',
                $material['linea_nombre'] ?? '',
                $material['nombre'] ?? '',
                $material['descripcion'] ?? '',
                isset($material['fecha_adquisicion']) && $material['fecha_adquisicion'] ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : '',
                $valorCompra,
                isset($material['fecha_fabricacion']) && $material['fecha_fabricacion'] ? date('d/m/Y', strtotime($material['fecha_fabricacion'])) : '',
                isset($material['fecha_vencimiento']) && $material['fecha_vencimiento'] ? date('d/m/Y', strtotime($material['fecha_vencimiento'])) : '',
                $fabricante,
                $material['medida'] ?? '',
                $material['presentacion'] ?? '',
                $material['categoria'] ?? '',
                $req,
                $stock,
                $faltante > 0 ? $faltante : 0,
                $material['ubicacion'] ?? '',
                $material['observacion'] ?? ''
            ], ';');

            $consecutivo++;
        }
        fclose($handle);

        // ===== CSV LÍNEAS =====
        $lineasFile = $tempDir . '/lineas.csv';
        $handle = fopen($lineasFile, 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        fputcsv($handle, ['ID', 'Nombre'], ';');
        foreach ($lineas as $linea) {
            fputcsv($handle, [$linea['id'] ?? '', $linea['nombre'] ?? ''], ';');
        }
        fclose($handle);

        // ===== CSV NODOS =====
        $nodosFile = $tempDir . '/nodos.csv';
        $handle = fopen($nodosFile, 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        fputcsv($handle, ['ID', 'Nombre'], ';');
        foreach ($nodos as $nodo) {
            fputcsv($handle, [$nodo['id'] ?? '', $nodo['nombre'] ?? ''], ';');
        }
        fclose($handle);

        // ===== CREAR ZIP =====
        $zipFile = sys_get_temp_dir() . '/' . $filename . '_' . date('Y-m-d') . '.zip';
        $zip = new ZipArchive();

        if ($zip->open($zipFile, ZipArchive::CREATE) === true) {
            // Agregar archivos al ZIP dentro de carpeta "materiales"
            $zip->addFile($materialesFile, 'materiales/materiales.csv');
            $zip->addFile($lineasFile, 'materiales/lineas.csv');
            $zip->addFile($nodosFile, 'materiales/nodos.csv');
            $zip->close();

            // Descargar ZIP
            header('Content-Type: application/zip');
            header('Content-Disposition: attachment; filename="' . basename($zipFile) . '"');
            header('Content-Length: ' . filesize($zipFile));
            readfile($zipFile);

            // Limpiar archivos temporales
            @unlink($zipFile);
            @unlink($materialesFile);
            @unlink($lineasFile);
            @unlink($nodosFile);
            @rmdir($tempDir);
        }

        exit;
    }
}

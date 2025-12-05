<?php

/**
 * Servicio para manejar exportaciones de materiales.
 * Separa la lógica de exportación del controlador principal.
 */
class MaterialExportService
{
    public function __construct() {}

    /**
     * Exporta materiales a Excel con 3 sheets: Materiales, Líneas y Nodos.
     * Orden exacto: Código de material, Nodo, Línea, Nombre, Fecha de adquisición, Categoría, Presentación, Medida, Cantidad, Valor de compra, Proveedor, Marca, Estado
     */
    public function exportToExcel($materiales, $filename = 'materiales', $lineas = [], $nodos = [])
    {
        require_once __DIR__ . '/../helpers/ExcelHelper.php';

        $excel = new ExcelHelper();

        // ===== SHEET 1: MATERIALES (sin ID ni fecha creación) =====
        $excel->createSheet('Materiales');
        $excel->setHeaders([
            'Código de material',
            'Nodo',
            'Línea',
            'Nombre',
            'Fecha de adquisición',
            'Categoría',
            'Presentación',
            'Medida',
            'Cantidad',
            'Descripción',
            'Valor de compra',
            'Proveedor',
            'Marca',
            'Estado'
        ]);

        foreach ($materiales as $material) {
            $excel->addRow([
                $material['codigo'] ?? '',
                $material['nodo_nombre'] ?? 'Sin asignar',
                $material['linea_nombre'] ?? 'Sin asignar',
                $material['nombre'] ?? '',
                isset($material['fecha_adquisicion']) ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : '',
                $material['categoria'] ?? '',
                $material['presentacion'] ?? '',
                $material['medida'] ?? '',
                $material['cantidad'] ?? 0,
                $material['descripcion'] ?? '',
                isset($material['valor_compra']) ? number_format($material['valor_compra'], 2, ',', '.') : '',
                $material['proveedor'] ?? '',
                $material['marca'] ?? '',
                ($material['estado'] == 1) ? 'Activo' : 'Inactivo'
            ]);
        }

        // ===== SHEET 2: LÍNEAS =====
        $excel->createSheet('Líneas');
        $excel->setHeaders([
            'ID',
            'Nombre'
        ]);

        foreach ($lineas as $linea) {
            $excel->addRow([
                $linea['id'] ?? '',
                $linea['nombre'] ?? ''
            ]);
        }

        // ===== SHEET 3: NODOS =====
        $excel->createSheet('Nodos');
        $excel->setHeaders([
            'ID',
            'Nombre'
        ]);

        foreach ($nodos as $nodo) {
            $excel->addRow([
                $nodo['id'] ?? '',
                $nodo['nombre'] ?? ''
            ]);
        }

        $excel->download($filename . '.xlsx');
    }

    /**
     * Exporta materiales a CSV con formato correcto y UTF-8 (sin ID ni fecha creación).
     * Orden exacto: Código de material, Nodo, Línea, Nombre, Fecha de adquisición, Categoría, Presentación, Medida, Cantidad, Valor de compra, Proveedor, Marca, Estado
     */
    public function exportToCSV($materiales, $filename = 'materiales')
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '_' . date('Y-m-d') . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8 (Excel lo reconoce correctamente)
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Encabezados en orden exacto
        fputcsv($output, [
            'Código de material',
            'Nodo',
            'Línea',
            'Nombre',
            'Fecha de adquisición',
            'Categoría',
            'Presentación',
            'Medida',
            'Cantidad',
            'Descripción',
            'Valor de compra',
            'Proveedor',
            'Marca',
            'Estado'
        ], ';');

        // Datos
        foreach ($materiales as $material) {
            fputcsv($output, [
                $material['codigo'] ?? '',
                $material['nodo_nombre'] ?? 'Sin asignar',
                $material['linea_nombre'] ?? 'Sin asignar',
                $material['nombre'] ?? '',
                isset($material['fecha_adquisicion']) ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : '',
                $material['categoria'] ?? '',
                $material['presentacion'] ?? '',
                $material['medida'] ?? '',
                $material['cantidad'] ?? 0,
                $material['descripcion'] ?? '',
                isset($material['valor_compra']) ? number_format($material['valor_compra'], 2, ',', '.') : '',
                $material['proveedor'] ?? '',
                $material['marca'] ?? '',
                ($material['estado'] == 1) ? 'Activo' : 'Inactivo'
            ], ';');
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
     * Exporta materiales a ZIP con 3 CSVs (Materiales, Líneas, Nodos).
     */
    public function exportToZip($materiales, $filename = 'materiales', $lineas = [], $nodos = [])
    {
        // Crear archivos temporales
        $tempDir = sys_get_temp_dir() . '/' . uniqid('materiales_');
        @mkdir($tempDir);

        // ===== CSV MATERIALES =====
        $materialesFile = $tempDir . '/materiales.csv';
        $handle = fopen($materialesFile, 'w');
        fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM

        fputcsv($handle, [
            'Código de material',
            'Nodo',
            'Línea',
            'Nombre',
            'Fecha de adquisición',
            'Categoría',
            'Presentación',
            'Medida',
            'Cantidad',
            'Descripción',
            'Valor de compra',
            'Proveedor',
            'Marca',
            'Estado'
        ], ';');

        foreach ($materiales as $material) {
            fputcsv($handle, [
                $material['codigo'] ?? '',
                $material['nodo_nombre'] ?? 'Sin asignar',
                $material['linea_nombre'] ?? 'Sin asignar',
                $material['nombre'] ?? '',
                isset($material['fecha_adquisicion']) ? date('d/m/Y', strtotime($material['fecha_adquisicion'])) : '',
                $material['categoria'] ?? '',
                $material['presentacion'] ?? '',
                $material['medida'] ?? '',
                $material['cantidad'] ?? 0,
                $material['descripcion'] ?? '',
                isset($material['valor_compra']) ? number_format($material['valor_compra'], 2, ',', '.') : '',
                $material['proveedor'] ?? '',
                $material['marca'] ?? '',
                ($material['estado'] == 1) ? 'Activo' : 'Inactivo'
            ], ';');
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

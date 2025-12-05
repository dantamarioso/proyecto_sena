<?php

/**
 * Servicio para manejar exportaciones de materiales.
 * Separa la lógica de exportación del controlador principal.
 */
class MaterialExportService
{
    public function __construct()
    {
    }

    /**
     * Exporta materiales a Excel.
     */
    public function exportToExcel($materiales, $filename = 'materiales')
    {
        $excel = new ExcelHelper();

        // Preparar datos
        $headers = [
            'ID',
            'Código SAP',
            'Descripción',
            'Cantidad',
            'Unidad',
            'Ubicación',
            'Estado',
            'Fecha Creación',
        ];

        $data = [];
        foreach ($materiales as $material) {
            $data[] = [
                $material['id'],
                $material['codigo_sap'],
                $material['descripcion'],
                $material['cantidad'],
                $material['unidad_medida'] ?? 'UND',
                $material['ubicacion'] ?? '',
                $material['estado'] ?? 'disponible',
                $material['created_at'] ?? '',
            ];
        }

        $excel->addSheet('Materiales', $headers, $data);

        return $excel->download($filename . '.xlsx');
    }

    /**
     * Exporta materiales a CSV.
     */
    public function exportToCSV($materiales, $filename = 'materiales')
    {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        $output = fopen('php://output', 'w');

        // BOM para UTF-8
        fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

        // Encabezados
        fputcsv($output, [
            'ID',
            'Código SAP',
            'Descripción',
            'Cantidad',
            'Unidad',
            'Ubicación',
            'Estado',
            'Fecha Creación',
        ]);

        // Datos
        foreach ($materiales as $material) {
            fputcsv($output, [
                $material['id'],
                $material['codigo_sap'],
                $material['descripcion'],
                $material['cantidad'],
                $material['unidad_medida'] ?? 'UND',
                $material['ubicacion'] ?? '',
                $material['estado'] ?? 'disponible',
                $material['created_at'] ?? '',
            ]);
        }

        fclose($output);
        exit;
    }

    /**
     * Exporta materiales a PDF.
     */
    public function exportToPDF($materiales, $filename = 'materiales')
    {
        $pdf = new PdfHelper();

        $pdf->setTitle('Listado de Materiales');
        $currentPage = $pdf->addPage();

        // Tabla
        $headers = ['ID', 'Código SAP', 'Descripción', 'Cantidad', 'Unidad', 'Estado'];
        $data = [];

        foreach ($materiales as $material) {
            $data[] = [
                $material['id'],
                $material['codigo_sap'],
                substr($material['descripcion'], 0, 40),
                $material['cantidad'],
                $material['unidad_medida'] ?? 'UND',
                $material['estado'] ?? 'disponible',
            ];
        }

        $pdf->addTable($headers, $data);

        return $pdf->download($filename . '.pdf');
    }

    /**
     * Exporta materiales a TXT.
     */
    public function exportToTXT($materiales, $filename = 'materiales')
    {
        header('Content-Type: text/plain; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.txt"');

        echo "LISTADO DE MATERIALES\n";
        echo str_repeat('=', 80) . "\n\n";

        foreach ($materiales as $material) {
            echo "ID: {$material['id']}\n";
            echo "Código SAP: {$material['codigo_sap']}\n";
            echo "Descripción: {$material['descripcion']}\n";
            echo "Cantidad: {$material['cantidad']}\n";
            echo 'Unidad: ' . ($material['unidad_medida'] ?? 'UND') . "\n";
            echo 'Estado: ' . ($material['estado'] ?? 'disponible') . "\n";
            echo str_repeat('-', 80) . "\n\n";
        }

        exit;
    }
}

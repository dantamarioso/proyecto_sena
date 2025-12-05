<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../services/MaterialExportService.php';

/**
 * Controlador para exportación de materiales.
 */
class MaterialesExportController extends Controller
{
    private $exportService;
    private $materialModel;

    public function __construct()
    {
        $this->exportService = new MaterialExportService();
        $this->materialModel = new Material();
    }

    private function requireAuth()
    {
        if (!isset($_SESSION['user'])) {
            header('HTTP/1.1 401 Unauthorized');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
    }

    /**
     * Exportar materiales (selector de formato).
     */
    public function exportar()
    {
        $this->requireAuth();

        $formato = $_GET['formato'] ?? 'csv';
        $filtros = $this->buildFilters();

        $materiales = $this->getMaterialesFiltrados($filtros);

        switch ($formato) {
            case 'pdf':
                $this->exportService->exportToPDF($materiales);
                break;
            case 'excel':
                $this->exportService->exportToExcel($materiales);
                break;
            case 'txt':
                $this->exportService->exportToTXT($materiales);
                break;
            case 'csv':
            default:
                $this->exportService->exportToCSV($materiales);
                break;
        }
    }

    /**
     * Exportar materiales a CSV.
     */
    public function csv()
    {
        $this->requireAuth();

        $filtros = $this->buildFilters();
        $materiales = $this->getMaterialesFiltrados($filtros);

        $this->exportService->exportToCSV($materiales);
    }

    /**
     * Exportar materiales a CSV comprimido (ZIP).
     */
    public function csvZip()
    {
        $this->requireAuth();

        $filtros = $this->buildFilters();
        $materiales = $this->getMaterialesFiltrados($filtros);

        // Crear archivo CSV temporal
        $filename = 'materiales_' . date('Y-m-d_His') . '.csv';
        $csvPath = sys_get_temp_dir() . '/' . $filename;

        $file = fopen($csvPath, 'w');
        fputcsv($file, ['Código', 'Nombre', 'Descripción', 'Cantidad', 'Estado', 'Línea']);

        foreach ($materiales as $mat) {
            fputcsv($file, [
                $mat['codigo'],
                $mat['nombre'],
                $mat['descripcion'] ?? '',
                $mat['cantidad'],
                $mat['estado'] == 1 ? 'Activo' : 'Inactivo',
                $mat['linea_nombre'] ?? ''
            ]);
        }
        fclose($file);

        // Crear ZIP
        $zipFilename = 'materiales_' . date('Y-m-d_His') . '.zip';
        $zipPath = sys_get_temp_dir() . '/' . $zipFilename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) === true) {
            $zip->addFile($csvPath, $filename);
            $zip->close();
        }

        // Descargar ZIP
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $zipFilename . '"');
        header('Content-Length: ' . filesize($zipPath));
        readfile($zipPath);

        // Limpiar archivos temporales
        unlink($csvPath);
        unlink($zipPath);
        exit;
    }

    /**
     * Exportar materiales a TXT.
     */
    public function txt()
    {
        $this->requireAuth();

        $filtros = $this->buildFilters();
        $materiales = $this->getMaterialesFiltrados($filtros);

        $this->exportService->exportToTXT($materiales);
    }

    /**
     * Exportar materiales a PDF.
     */
    public function pdf()
    {
        $this->requireAuth();

        $filtros = $this->buildFilters();
        $materiales = $this->getMaterialesFiltrados($filtros);

        $this->exportService->exportToPDF($materiales);
    }

    /**
     * Obtener materiales filtrados.
     */
    private function getMaterialesFiltrados($filtros)
    {
        if (!empty($filtros['buscar']) || !empty($filtros['linea']) || isset($filtros['estado'])) {
            return $this->materialModel->search(
                $filtros['buscar'] ?? '',
                $filtros['linea'] ?? null,
                $filtros['estado'] ?? null
            );
        }

        return $this->materialModel->all();
    }

    /**
     * Construye filtros desde $_GET.
     */
    private function buildFilters()
    {
        $filtros = [];

        if (!empty($_GET['buscar'])) {
            $filtros['buscar'] = trim($_GET['buscar']);
        }

        if (!empty($_GET['nodo'])) {
            $filtros['nodo'] = (int)$_GET['nodo'];
        }

        if (!empty($_GET['linea'])) {
            $filtros['linea'] = (int)$_GET['linea'];
        }

        if (!empty($_GET['estado'])) {
            $filtros['estado'] = $_GET['estado'];
        }

        if (isset($_GET['cantidad_baja']) && $_GET['cantidad_baja'] === '1') {
            $filtros['cantidad_baja'] = true;
        }

        return $filtros;
    }
}

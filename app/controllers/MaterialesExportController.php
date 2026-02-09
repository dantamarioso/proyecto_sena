<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/Linea.php';
require_once __DIR__ . '/../models/Nodo.php';
require_once __DIR__ . '/../services/MaterialExportService.php';

/**
 * Controlador para exportación de materiales.
 */
class MaterialesExportController extends Controller
{
    private $exportService;
    private $materialModel;
    private $lineaModel;
    private $nodoModel;

    public function __construct()
    {
        $this->exportService = new MaterialExportService();
        $this->materialModel = new Material();
        $this->lineaModel = new Linea();
        $this->nodoModel = new Nodo();
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

        // Aplicar permisos al export (igual que el listado)
        $rol = $_SESSION['user']['rol'] ?? 'usuario';
        if ($rol === 'dinamizador') {
            $filtros['nodo_id'] = $_SESSION['user']['nodo_id'] ?? null;
        } elseif ($rol === 'usuario') {
            $filtros['nodo_id'] = $_SESSION['user']['nodo_id'] ?? null;
            $filtros['linea_id'] = $_SESSION['user']['linea_id'] ?? null;
        }

        $materiales = $this->getMaterialesFiltrados($filtros);

        $nodoNombre = '';
        if (!empty($filtros['nodo_id'])) {
            $nodo = $this->nodoModel->getById((int)$filtros['nodo_id']);
            $nodoNombre = $nodo['nombre'] ?? '';
        }

        switch ($formato) {
            case 'pdf':
                $this->exportService->exportToPDF($materiales);
                break;
            case 'excel':
                $this->exportService->exportToExcel($materiales, 'lista_maestra_materiales', ['nodo_nombre' => $nodoNombre]);
                break;
            case 'txt':
                $this->exportService->exportToTXT($materiales);
                break;
            case 'zip':
                $lineas = $this->lineaModel->all() ?? [];
                $nodos = $this->nodoModel->all() ?? [];
                $this->exportService->exportToZip($materiales, 'lista_maestra_materiales', $lineas, $nodos, ['nodo_nombre' => $nodoNombre]);
                break;
            case 'csv':
            default:
                $this->exportService->exportToCSV($materiales, 'lista_maestra_materiales', ['nodo_nombre' => $nodoNombre]);
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

        $nodoNombre = '';
        if (!empty($filtros['nodo_id'])) {
            $nodo = $this->nodoModel->getById((int)$filtros['nodo_id']);
            $nodoNombre = $nodo['nombre'] ?? '';
        }

        $this->exportService->exportToCSV($materiales, 'lista_maestra_materiales', ['nodo_nombre' => $nodoNombre]);
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
        if (!empty($filtros)) {
            return $this->materialModel->searchAvanzado($filtros);
        }

        return $this->materialModel->all();
    }

    /**
     * Construye filtros desde $_GET.
     */
    private function buildFilters()
    {
        $filtros = [];

        $busqueda = $_GET['busqueda'] ?? ($_GET['buscar'] ?? '');
        if (trim($busqueda) !== '') {
            $filtros['busqueda'] = trim($busqueda);
        }

        $nodoId = $_GET['nodo_id'] ?? ($_GET['nodo'] ?? null);
        if (!empty($nodoId)) {
            $filtros['nodo_id'] = (int)$nodoId;
        }

        $lineaId = $_GET['linea_id'] ?? ($_GET['linea'] ?? null);
        if (!empty($lineaId)) {
            $filtros['linea_id'] = (int)$lineaId;
        }

        if (isset($_GET['estado']) && $_GET['estado'] !== '') {
            $filtros['estado'] = (int)$_GET['estado'];
        }

        if (!empty($_GET['categoria'])) {
            $filtros['categoria'] = trim($_GET['categoria']);
        }

        if (!empty($_GET['proveedor'])) {
            $filtros['proveedor'] = trim($_GET['proveedor']);
        }

        if (!empty($_GET['cantidad'])) {
            $filtros['cantidad'] = $_GET['cantidad'];
        }

        return $filtros;
    }
}

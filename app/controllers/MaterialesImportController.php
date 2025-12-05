<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/MaterialImportService.php';

/**
 * Controlador especializado para importación de materiales.
 */
class MaterialesImportController extends Controller
{
    private $importService;

    public function __construct()
    {
        $this->importService = new MaterialImportService();
    }

    /**
     * Importar materiales desde archivo CSV/TXT/XLSX.
     */
    public function importar()
    {
        header('Content-Type: application/json; charset=utf-8');

        // Validar autenticación
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        // Validar permisos (solo admin y dinamizador)
        $rol = $_SESSION['user']['rol'] ?? 'usuario';
        if (!in_array($rol, ['admin', 'dinamizador'])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No tiene permiso para importar materiales']);
            exit;
        }

        // Validar archivo
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $error = $_FILES['archivo']['error'] ?? 'desconocido';
            echo json_encode(['success' => false, 'message' => "Error al subir archivo (código: $error)"]);
            exit;
        }

        try {
            // Usar servicio de importación
            $result = $this->importService->importFromFile($_FILES['archivo'], $_POST);

            $statusCode = $result['success'] ? 200 : 400;
            http_response_code($statusCode);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Error al procesar importación: ' . $e->getMessage(),
            ]);
        }

        exit;
    }
}

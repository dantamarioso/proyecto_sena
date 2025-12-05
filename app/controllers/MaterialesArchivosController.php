<?php

require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/MaterialArchivo.php';
require_once __DIR__ . '/../services/MaterialFileService.php';

/**
 * Controlador para gestión de archivos adjuntos a materiales.
 */
class MaterialesArchivosController extends Controller
{
    private $fileService;
    private $archivoModel;

    public function __construct()
    {
        $this->fileService = new MaterialFileService();
        $this->archivoModel = new MaterialArchivo();
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
     * Subir archivo a un material.
     */
    public function subir()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        try {
            if (!isset($_FILES['archivo']) || !isset($_POST['material_id'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            $materialId = (int)$_POST['material_id'];
            $file = $_FILES['archivo'];
            $userId = $_SESSION['user']['id'];

            $result = $this->fileService->uploadFile($materialId, $file, $userId);

            $statusCode = $result['success'] ? 200 : 400;
            http_response_code($statusCode);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Eliminar archivo adjunto.
     */
    public function eliminar()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        try {
            $archivoId = $_POST['archivo_id'] ?? null;

            if (!$archivoId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de archivo no proporcionado']);
                return;
            }

            $userId = $_SESSION['user']['id'];
            $result = $this->fileService->deleteFile((int)$archivoId, $userId);

            $statusCode = $result['success'] ? 200 : 400;
            http_response_code($statusCode);
            echo json_encode($result);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Obtener archivos de un material.
     */
    public function listar()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        try {
            $materialId = $_GET['material_id'] ?? null;

            if (!$materialId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de material no proporcionado']);
                return;
            }

            $archivos = $this->archivoModel->getByMaterial((int)$materialId);

            echo json_encode([
                'success' => true,
                'archivos' => $archivos
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    /**
     * Contar documentos de un material.
     */
    public function contar()
    {
        header('Content-Type: application/json');
        $this->requireAuth();

        try {
            $materialId = $_GET['material_id'] ?? null;

            if (!$materialId) {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'ID de material no proporcionado']);
                return;
            }

            $cantidad = count($this->archivoModel->getByMaterial((int)$materialId));

            echo json_encode([
                'success' => true,
                'cantidad' => $cantidad
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

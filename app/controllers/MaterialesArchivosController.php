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
            $materialId = null;
            $file = null;
            $userId = $_SESSION['user']['id'];

            // Intentar obtener datos de JSON (base64)
            $jsonInput = json_decode(file_get_contents('php://input'), true);

            if ($jsonInput && isset($jsonInput['material_id']) && isset($jsonInput['archivo_data'])) {
                // Procesar JSON con base64
                $materialId = (int)$jsonInput['material_id'];
                $base64Data = $jsonInput['archivo_data'];
                $fileName = $jsonInput['archivo_nombre'] ?? 'documento';
                $fileSize = (int)($jsonInput['archivo_tamaño'] ?? 0);

                // Decodificar base64
                $fileContent = base64_decode($base64Data);
                if ($fileContent === false) {
                    http_response_code(400);
                    echo json_encode(['success' => false, 'message' => 'Datos base64 inválidos']);
                    return;
                }

                // Crear archivo temporal
                $tempFile = tempnam(sys_get_temp_dir(), 'upload_');
                if (file_put_contents($tempFile, $fileContent) === false) {
                    http_response_code(500);
                    echo json_encode(['success' => false, 'message' => 'Error al crear archivo temporal']);
                    return;
                }

                // Estructura compatible con $_FILES
                $file = [
                    'name' => $fileName,
                    'type' => $jsonInput['archivo_tipo'] ?? 'application/octet-stream',
                    'tmp_name' => $tempFile,
                    'error' => 0,
                    'size' => $fileSize
                ];
            } elseif (isset($_FILES['archivo']) && isset($_POST['material_id'])) {
                // Procesar multipart form
                $materialId = (int)$_POST['material_id'];
                $file = $_FILES['archivo'];
            } else {
                http_response_code(400);
                echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
                return;
            }

            $result = $this->fileService->uploadFile($materialId, $file, $userId);

            // Limpiar archivo temporal si se creó
            if (isset($tempFile) && file_exists($tempFile)) {
                @unlink($tempFile);
            }

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
            $archivoId = $_POST['archivo_id'] ?? ($_POST['id'] ?? null);

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
                'count' => $cantidad
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}

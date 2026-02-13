<?php

/**
 * Servicio para manejar archivos adjuntos de materiales.
 */
class MaterialFileService
{
    private $archivoModel;

    private $uploadDir;

    public function __construct()
    {
        $this->archivoModel = new MaterialArchivo();
        $this->uploadDir = __DIR__ . '/../../public/uploads/materiales/';

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    /**
     * Sube un archivo y lo asocia a un material.
     */
    public function uploadFile($materialId, $file, $userId)
    {
        // Validar archivo
        $validation = $this->validateFile($file);
        if (!$validation['valid']) {
            return ['success' => false, 'message' => $validation['message']];
        }

        // Generar nombre único
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $filename = uniqid() . '_' . time() . '.' . $extension;
        $filepath = $this->uploadDir . $filename;

        // Mover archivo (soporta uploads directos y archivos temporales base64)
        if (!move_uploaded_file($file['tmp_name'], $filepath)) {
            // Fallback para archivos generados vía base64 (no son is_uploaded_file)
            if (!@rename($file['tmp_name'], $filepath)) {
                return ['success' => false, 'message' => 'Error al subir el archivo.'];
            }
        }

        // Guardar en base de datos
        $archivoId = $this->archivoModel->create([
            'material_id' => $materialId,
            'nombre_original' => $file['name'],
            'nombre_archivo' => $filename,
            'tipo_archivo' => $file['type'] ?? 'application/octet-stream',
            'tamano' => $file['size'] ?? 0,
            'usuario_id' => $userId,
        ]);

        if (!$archivoId) {
            unlink($filepath); // Eliminar archivo físico si falla DB

            return ['success' => false, 'message' => 'Error al guardar el registro del archivo.'];
        }

        return [
            'success' => true,
            'message' => 'Archivo subido exitosamente.',
            'archivo_id' => $archivoId,
        ];
    }

    /**
     * Elimina un archivo.
     */
    public function deleteFile($archivoId, $userId)
    {
        $archivo = $this->archivoModel->findById($archivoId);

        if (!$archivo) {
            return ['success' => false, 'message' => 'Archivo no encontrado.'];
        }

        // Eliminar archivo físico
        $filepath = __DIR__ . '/../../public/' . $archivo['ruta'];
        if (file_exists($filepath)) {
            unlink($filepath);
        }

        // Eliminar de base de datos
        $deleted = $this->archivoModel->delete($archivoId);

        if (!$deleted) {
            return ['success' => false, 'message' => 'Error al eliminar el archivo.'];
        }

        return ['success' => true, 'message' => 'Archivo eliminado exitosamente.'];
    }

    /**
     * Obtiene los archivos de un material.
     */
    public function getFilesByMaterial($materialId)
    {
        return $this->archivoModel->findByMaterialId($materialId);
    }

    /**
     * Cuenta documentos por material.
     */
    public function countDocuments($materialId)
    {
        return $this->archivoModel->countByMaterialId($materialId);
    }

    /**
     * Valida un archivo subido.
     */
    private function validateFile($file)
    {
        // Verificar errores de upload
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'Error al subir el archivo.'];
        }

        // Validar tamaño (máximo 10MB)
        $maxSize = 10 * 1024 * 1024;
        if ($file['size'] > $maxSize) {
            return ['valid' => false, 'message' => 'El archivo es demasiado grande (máximo 10MB).'];
        }

        // Validar extensión
        $allowedExtensions = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv', 'jpg', 'jpeg', 'png'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return ['valid' => false, 'message' => 'Tipo de archivo no permitido.'];
        }

        return ['valid' => true];
    }
}

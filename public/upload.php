<?php
/**
 * Endpoint especial para subir archivos (bypassa el router/rewrite rules)
 * Esto evita problemas con Apache rewrite que pierde el body en GET requests
 */

error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');
ini_set('error_log', __DIR__ . '/../error_log.txt');

// Iniciar sesión
session_start();

// Forzar HTTPS en ngrok
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['REQUEST_SCHEME'] = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    $_SERVER['HTTPS'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'on' : 'off';
}

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/helpers/MailHelper.php';
require_once __DIR__ . '/../app/helpers/ViewHelpers.php';
require_once __DIR__ . '/../app/helpers/PermissionHelper.php';

// Autocarga
spl_autoload_register(function ($class) {
    if (file_exists(__DIR__ . '/../app/controllers/' . $class . '.php')) {
        require_once __DIR__ . '/../app/controllers/' . $class . '.php';
    } elseif (file_exists(__DIR__ . '/../app/models/' . $class . '.php')) {
        require_once __DIR__ . '/../app/models/' . $class . '.php';
    }
});

ob_start();
header('Content-Type: application/json; charset=utf-8');
error_log('=== UPLOAD.PHP INICIO ===');

try {
    // Validar sesión
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['id'])) {
        error_log('Sesión no válida');
        throw new Exception('No autorizado');
    }
    
    // Validar rol
    $rol = $_SESSION['user']['rol'] ?? 'usuario';
    if (!in_array($rol, ['admin', 'dinamizador'])) {
        error_log('Rol no permitido: ' . $rol);
        throw new Exception('Solo administradores y dinamizadores pueden subir archivos');
    }

    // Leer input JSON
    $input = file_get_contents('php://input');
    error_log('Input length: ' . strlen($input) . ' bytes');
    
    if (strlen($input) > 0) {
        error_log('Input first 150 chars: ' . substr($input, 0, 150));
    }
    
    if (empty($input)) {
        throw new Exception('No se recibieron datos');
    }
    
    $data = json_decode($input, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log('JSON error: ' . json_last_error_msg());
        throw new Exception('JSON inválido: ' . json_last_error_msg());
    }
    
    if (!$data) {
        throw new Exception('Datos vacíos');
    }
    
    error_log('JSON decodificado OK');
    
    $materialId = intval($data['material_id'] ?? 0);
    error_log('Material ID: ' . $materialId);
    
    if ($materialId <= 0) {
        throw new Exception('Material inválido');
    }

    if (empty($data['archivo_data'])) {
        throw new Exception('No se envió archivo');
    }

    // Cargar modelos
    $materialModel = new Material();
    $material = $materialModel->getById($materialId);
    
    if (!$material) {
        throw new Exception('Material no encontrado');
    }

    // Verificar permisos
    $permissions = new PermissionHelper();
    if (!$permissions->canEditMaterial($materialId)) {
        throw new Exception('No tiene permisos para subir archivos a este material');
    }

    error_log('Permisos OK');

    // Procesar archivo
    $nombreOriginal = $data['archivo_nombre'] ?? 'archivo_sin_nombre';
    $tipoArchivo = $data['archivo_tipo'] ?? 'application/octet-stream';
    $tamanioArchivo = $data['archivo_tamaño'] ?? 0;
    $archivoBase64 = $data['archivo_data'] ?? '';
    
    error_log('Archivo: ' . $nombreOriginal . ', tamaño: ' . $tamanioArchivo);
    
    // Validar extensión
    $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
    $extensionesPermitidas = ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'];
    if (!in_array($ext, $extensionesPermitidas)) {
        throw new Exception('Tipo de archivo no permitido');
    }

    // Validar tamaño
    if ($tamanioArchivo > 10 * 1024 * 1024) {
        throw new Exception('Archivo supera 10MB');
    }

    // Decodificar base64
    $archivoContenido = base64_decode($archivoBase64, true);
    if ($archivoContenido === false) {
        throw new Exception('Error al decodificar archivo');
    }

    error_log('Archivo decodificado: ' . strlen($archivoContenido) . ' bytes');

    // Guardar archivo
    $nombreArchivo = "uploads/materiales/" . date('YmdHis_') . preg_replace('/[^a-zA-Z0-9._-]/', '_', $nombreOriginal);
    $rutaSistema = __DIR__ . "/" . $nombreArchivo;
    $uploadDir = __DIR__ . "/uploads/materiales/";

    if (!is_dir($uploadDir)) {
        if (!@mkdir($uploadDir, 0777, true)) {
            throw new Exception('Error al crear directorio');
        }
    }

    if (file_put_contents($rutaSistema, $archivoContenido) === false) {
        throw new Exception('Error al guardar archivo');
    }

    error_log('Archivo guardado en: ' . $rutaSistema);

    // Guardar en BD
    $userModel = new User();
    $userId = $_SESSION['user']['id'];
    $usuario = $userModel->findById($userId);
    
    if (!$usuario) {
        $userId = 1;
    }
    
    $archivoModel = new MaterialArchivo();
    $result = $archivoModel->create([
        'material_id' => $materialId,
        'nombre_original' => $nombreOriginal,
        'nombre_archivo' => $nombreArchivo,
        'tipo_archivo' => $tipoArchivo,
        'tamano' => strlen($archivoContenido),
        'usuario_id' => $userId
    ]);
    
    if (!$result) {
        @unlink($rutaSistema);
        throw new Exception('Error al guardar en base de datos');
    }

    error_log('Archivo registrado en BD');

    // Auditoría
    try {
        require_once __DIR__ . '/../app/models/Audit.php';
        $audit = new Audit();
        $audit->registrarCambio(
            $_SESSION['user']['id'],
            'material_archivos',
            $materialId,
            'subir_archivo',
            [
                'material_id' => $materialId,
                'nombre_original' => $nombreOriginal,
                'nombre_archivo' => $nombreArchivo,
                'tamaño' => strlen($archivoContenido)
            ],
            $_SESSION['user']['id']
        );
        error_log('Auditoría registrada');
    } catch (Exception $e) {
        error_log('Error en auditoría: ' . $e->getMessage());
    }

    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'Archivo subido exitosamente']);
    error_log('=== UPLOAD.PHP SUCCESS ===');

} catch (Exception $e) {
    ob_end_clean();
    error_log('EXCEPCIÓN: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
    error_log('=== UPLOAD.PHP ERROR ===');
}

exit;

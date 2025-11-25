<?php
/**
 * Diagnóstico completo de carga de archivos
 */

session_start();

header('Content-Type: application/json; charset=utf-8');

$diagnostico = [
    'timestamp' => date('Y-m-d H:i:s'),
    'sesion' => [
        'sesion_activa' => isset($_SESSION['user']),
        'usuario_id' => $_SESSION['user']['id'] ?? null,
        'usuario_rol' => $_SESSION['user']['rol'] ?? null,
        'session_id' => session_id(),
    ],
    'request' => [
        'metodo' => $_SERVER['REQUEST_METHOD'],
        'url' => $_SERVER['REQUEST_URI'],
        'host' => $_SERVER['HTTP_HOST'],
        'https' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'x_forwarded_proto' => $_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'no definido',
    ],
    'archivos' => [
        'cantidad_archivos' => count($_FILES),
        'archivos_keys' => array_keys($_FILES),
        'archivo_error' => $_FILES['archivo']['error'] ?? 'sin archivo',
        'archivo_size' => $_FILES['archivo']['size'] ?? 0,
        'archivo_name' => $_FILES['archivo']['name'] ?? 'sin nombre',
    ],
    'post' => [
        'cantidad_post' => count($_POST),
        'post_keys' => array_keys($_POST),
        'material_id' => $_POST['material_id'] ?? null,
    ],
    'permisos' => [
        'puede_subir' => isset($_SESSION['user']) && in_array($_SESSION['user']['rol'] ?? '', ['admin', 'dinamizador']),
        'archivos_permitidos' => ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv'],
    ],
];

// Validar qué falla
$problemas = [];

if (!isset($_SESSION['user'])) {
    $problemas[] = 'No hay sesión de usuario activa';
}

if (!isset($_SESSION['user']['id'])) {
    $problemas[] = 'Usuario no tiene ID en sesión';
}

if (!in_array($_SESSION['user']['rol'] ?? '', ['admin', 'dinamizador'])) {
    $problemas[] = 'Usuario no tiene rol admin o dinamizador. Rol: ' . ($_SESSION['user']['rol'] ?? 'no definido');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $problemas[] = 'No es una solicitud POST';
}

if (empty($_POST['material_id'])) {
    $problemas[] = 'Material ID no enviado o vacío';
}

if (empty($_FILES['archivo'])) {
    $problemas[] = 'Archivo no enviado';
} else if ($_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
    $errores_upload = [
        UPLOAD_ERR_INI_SIZE => 'El archivo supera php.ini upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'El archivo supera el MAX_FILE_SIZE del formulario',
        UPLOAD_ERR_PARTIAL => 'El archivo se subió parcialmente',
        UPLOAD_ERR_NO_FILE => 'No se subió archivo',
        UPLOAD_ERR_NO_TMP_DIR => 'No hay directorio temporal',
        UPLOAD_ERR_CANT_WRITE => 'No se puede escribir en el directorio',
        UPLOAD_ERR_EXTENSION => 'La carga fue detenida por extensión',
    ];
    $problemas[] = 'Error en carga: ' . ($errores_upload[$_FILES['archivo']['error']] ?? 'Error desconocido');
}

$diagnostico['problemas'] = $problemas;
$diagnostico['es_valido'] = empty($problemas);

echo json_encode($diagnostico, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

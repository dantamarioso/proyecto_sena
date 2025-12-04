<?php
// ========== ERROR LOGGING ==========
error_reporting(E_ALL);
ini_set('display_errors', '0'); // No mostrar en página
ini_set('log_errors', '1'); // Activar logging
ini_set('error_log', __DIR__ . '/../error_log.txt'); // Guardar en la raíz del proyecto (fuera del webroot)

// ========== FORCE HTTPS FOR NGROK ==========
// Procesar headers de proxy (ngrok, cloudflare, etc)
if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
    $_SERVER['REQUEST_SCHEME'] = $_SERVER['HTTP_X_FORWARDED_PROTO'];
    $_SERVER['HTTPS'] = $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https' ? 'on' : 'off';
}

if (!empty($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $_SERVER['HTTP_HOST'] = $_SERVER['HTTP_X_FORWARDED_HOST'];
}

// Forzar HTTPS en ngrok (muy importante para evitar mixed content)
if (!empty($_SERVER['HTTP_HOST']) && strpos($_SERVER['HTTP_HOST'], 'ngrok') !== false) {
    // Asegurar que SIEMPRE sea HTTPS para ngrok
    $_SERVER['HTTPS'] = 'on';
    $_SERVER['REQUEST_SCHEME'] = 'https';
    
    // Si la solicitud vino por HTTP, redirigir a HTTPS
    if (empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off') {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $redirectUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
            header('Location: ' . $redirectUrl, true, 301);
            exit;
        }
    }
}

// ========== FORZAR MÉTODO POST CORRECTO ==========
// Si llega como GET pero con URL params POST-like, puede ser ngrok redirigiendo
// En ese caso, recrear la petición como POST si es un endpoint de upload
if ($_SERVER['REQUEST_METHOD'] === 'GET' && !empty($_GET['url']) && strpos($_GET['url'], 'subirArchivo') !== false) {
    // Este es un endpoint POST que llegó como GET
    // Verificar si tiene input en php://input (el JSON enviado)
    $input = file_get_contents('php://input');
    if (!empty($input)) {
        error_log('REDIRECCIONAMIENTO POST DETECTADO: ' . $_GET['url']);
        // Procesar como si fuera POST
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $_SERVER['CONTENT_TYPE'] = 'application/json; charset=utf-8';
    }
}

session_start();

// ========== DISABLE CACHE FOR DEVELOPMENT ==========
header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: Thu, 01 Jan 1970 00:00:00 GMT');

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/helpers/DebugHelper.php';
require_once __DIR__ . '/../app/helpers/ViewHelpers.php';

// Autocarga simple de controllers, models y helpers
spl_autoload_register(function ($class) {
    if (file_exists(__DIR__ . '/../app/controllers/' . $class . '.php')) {
        require_once __DIR__ . '/../app/controllers/' . $class . '.php';
    } elseif (file_exists(__DIR__ . '/../app/models/' . $class . '.php')) {
        require_once __DIR__ . '/../app/models/' . $class . '.php';
    } elseif (file_exists(__DIR__ . '/../app/helpers/' . $class . '.php')) {
        require_once __DIR__ . '/../app/helpers/' . $class . '.php';
    }
});

// Router básico: index.php?url=auth/login
$url = $_GET['url'] ?? 'auth/login';
$url = trim($url, '/');
$parts = explode('/', $url);

$controllerName = ucfirst($parts[0]) . 'Controller'; // auth -> AuthController
$method = $parts[1] ?? 'login';

// Depuración: verificar que la clase existe
if (!class_exists($controllerName)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo json_encode(['error' => "Controlador '$controllerName' no encontrado", 'debug' => ['url' => $url, 'parts' => $parts, 'controller' => $controllerName]]);
    exit;
}

if (!method_exists($controller = new $controllerName(), $method)) {
    header('Content-Type: application/json; charset=utf-8');
    http_response_code(404);
    echo json_encode(['error' => "Método '$method' no encontrado en '$controllerName'"]);
    exit;
}

$controller->$method();

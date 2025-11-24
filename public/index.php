<?php
// ========== ERROR LOGGING ==========
error_reporting(E_ALL);
ini_set('display_errors', '0'); // No mostrar en página
ini_set('log_errors', '1'); // Activar logging
ini_set('error_log', __DIR__ . '/../error_log.txt'); // Guardar en la raíz del proyecto

// ========== HEADERS ==========
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

session_start();

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../app/core/Database.php';
require_once __DIR__ . '/../app/core/Model.php';
require_once __DIR__ . '/../app/core/Controller.php';
require_once __DIR__ . '/../app/helpers/MailHelper.php';
require_once __DIR__ . '/../app/helpers/ViewHelpers.php';

// Autocarga simple de controllers y models
spl_autoload_register(function ($class) {
    if (file_exists(__DIR__ . '/../app/controllers/' . $class . '.php')) {
        require_once __DIR__ . '/../app/controllers/' . $class . '.php';
    } elseif (file_exists(__DIR__ . '/../app/models/' . $class . '.php')) {
        require_once __DIR__ . '/../app/models/' . $class . '.php';
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

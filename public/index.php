<?php
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

if (!class_exists($controllerName)) {
    die("Controlador no encontrado");
}

$controller = new $controllerName();

if (!method_exists($controller, $method)) {
    die("Método no encontrado");
}

$controller->$method();

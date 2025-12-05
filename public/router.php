<?php

/**
 * Router para desarrollo local
 * Sirve archivos estáticos y redirige peticiones al index.php.
 */

// Obtener la ruta solicitada
$requested = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requested = str_replace('/proyecto_sena/public', '', $requested);

// Si es una solicitud de archivo estático, servirlo
if ($requested !== '/' && file_exists(__DIR__ . $requested)) {
    // Archivos estáticos: CSS, JS, imágenes, etc.
    return false;
}

// Si no es un archivo estático, redirigir al index.php
require __DIR__ . '/index.php';

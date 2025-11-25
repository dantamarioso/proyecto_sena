<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'inventario_db');
define('DB_USER', 'root');
define('DB_PASS', ''); // tu contraseña

// Ruta base - detecta automáticamente el servidor y protocolo
if (empty($_SERVER['HTTP_HOST'])) {
    define('BASE_URL', 'http://localhost:8000/proyecto_sena/public');
} else {
    // Detectar protocolo (soporta proxies como ngrok y Cloudflare)
    $protocol = 'http';
    
    // Prioridad de detección:
    // 1. ngrok usa X-Forwarded-Proto
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO'])) {
        $protocol = strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']);
    } 
    // 2. Cloudflare
    elseif (!empty($_SERVER['HTTP_CF_VISITOR'])) {
        $cf_visitor = json_decode($_SERVER['HTTP_CF_VISITOR']);
        if ($cf_visitor && isset($cf_visitor->scheme)) {
            $protocol = strtolower($cf_visitor->scheme);
        }
    }
    // 3. HTTPS estándar
    elseif (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        $protocol = 'https';
    }
    // 4. Puerto 443 indica HTTPS
    elseif (!empty($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] === '443') {
        $protocol = 'https';
    }
    
    $host = $_SERVER['HTTP_HOST'];
    define('BASE_URL', $protocol . '://' . $host . '/proyecto_sena/public');
}


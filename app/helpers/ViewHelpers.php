<?php
/**
 * Helpers globales para vistas
 */

/**
 * Formatea bytes a unidades legibles
 */
function formatearBytes($bytes, $precision = 2) {
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));
    
    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Obtiene el icono según la extensión del archivo
 */
function obtenerIconoArchivo($nombreArchivo) {
    $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));
    
    $iconos = [
        'pdf'  => 'bi-file-pdf',
        'doc'  => 'bi-file-word',
        'docx' => 'bi-file-word',
        'xls'  => 'bi-file-excel',
        'xlsx' => 'bi-file-excel',
        'ppt'  => 'bi-file-powerpoint',
        'pptx' => 'bi-file-powerpoint',
        'txt'  => 'bi-file-text',
        'csv'  => 'bi-file-earmark-spreadsheet'
    ];
    
    return $iconos[$ext] ?? 'bi-file-earmark';
}
?>

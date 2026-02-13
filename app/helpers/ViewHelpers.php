<?php

/**
 * Helpers globales para vistas.
 */

/**
 * Formatea bytes a unidades legibles.
 */
function formatearBytes($bytes, $precision = 2)
{
    $units = ['B', 'KB', 'MB', 'GB'];
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
    $bytes /= (1 << (10 * $pow));

    return round($bytes, $precision) . ' ' . $units[$pow];
}

/**
 * Obtiene el icono según la extensión del archivo.
 */
function obtenerIconoArchivo($nombreArchivo)
{
    $ext = strtolower(pathinfo($nombreArchivo, PATHINFO_EXTENSION));

    $iconos = [
        'pdf' => 'bi-file-pdf',
        'doc' => 'bi-file-word',
        'docx' => 'bi-file-word',
        'xls' => 'bi-file-excel',
        'xlsx' => 'bi-file-excel',
        'ppt' => 'bi-file-powerpoint',
        'pptx' => 'bi-file-powerpoint',
        'txt' => 'bi-file-text',
        'csv' => 'bi-file-earmark-spreadsheet',
    ];

    return $iconos[$ext] ?? 'bi-file-earmark';
}

/**
 * Formatea una cantidad (DECIMAL) para mostrarla sin ceros innecesarios.
 * Ejemplos:
 * - 1.140 -> 1.14
 * - 1.000 -> 1
 * - 1.234 -> 1.234
 */
function formatearCantidad($valor, $decimales = 3)
{
    if ($valor === null) {
        return '0';
    }

    $s = trim((string)$valor);
    if ($s === '') {
        return '0';
    }

    // Normalizar coma decimal a punto para consistencia
    $s = str_replace(',', '.', $s);

    if (!is_numeric($s)) {
        return $s;
    }

    // Evitar notacion cientifica en pantalla
    if (stripos($s, 'e') !== false) {
        $s = number_format((float)$s, (int)$decimales, '.', '');
    }

    if (strpos($s, '.') !== false) {
        $s = rtrim(rtrim($s, '0'), '.');
    }

    if ($s === '' || $s === '-0') {
        return '0';
    }

    return $s;
}

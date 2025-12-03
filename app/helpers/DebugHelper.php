<?php

/**
 * Helper para logging condicional en modo DEBUG
 * Uso: DebugHelper::log('Tu mensaje')
 * Solo registra en error_log si DEBUG está activado en config.php
 */
class DebugHelper
{
    /**
     * Log condicional
     * @param string $message Mensaje a registrar
     * @param string $level Nivel: 'info', 'warning', 'error' (solo para legibilidad)
     */
    public static function log($message, $level = 'info')
    {
        if (!defined('DEBUG') || DEBUG !== true) {
            return; // No registrar si DEBUG está desactivado
        }

        $prefix = '[' . strtoupper($level) . '] ';
        error_log($prefix . $message);
    }

    /**
     * Log de información
     */
    public static function info($message)
    {
        self::log($message, 'info');
    }

    /**
     * Log de advertencia
     */
    public static function warning($message)
    {
        self::log($message, 'warning');
    }

    /**
     * Log de error
     */
    public static function error($message)
    {
        self::log($message, 'error');
    }

    /**
     * Log de inicio de método/función
     */
    public static function start($methodName)
    {
        self::log("=== INICIO $methodName ===", 'info');
    }

    /**
     * Log de fin de método/función
     */
    public static function end($methodName)
    {
        self::log("=== FIN $methodName ===", 'info');
    }

    /**
     * Log de array para debugging
     */
    public static function dump($data, $name = 'Data')
    {
        if (!defined('DEBUG') || DEBUG !== true) {
            return;
        }

        self::log("$name: " . json_encode($data), 'info');
    }
}

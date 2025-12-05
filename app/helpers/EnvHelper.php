<?php

/**
 * Helper para cargar variables de entorno desde archivo .env
 * Uso: EnvHelper::load().
 */
class EnvHelper
{
    private static $vars = [];

    private static $loaded = false;

    /**
     * Cargar variables del archivo .env.
     */
    public static function load()
    {
        if (self::$loaded) {
            return;
        }

        $envFile = __DIR__ . '/../../.env';

        if (!file_exists($envFile)) {
            // Si no existe .env, usar valores por defecto
            self::setDefaults();
            self::$loaded = true;

            return;
        }

        $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            // Ignorar comentarios
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            // Separar key=value
            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);

                // Remover comillas si existen
                if (
                    (strpos($value, '"') === 0 && strrpos($value, '"') === strlen($value) - 1) ||
                    (strpos($value, "'") === 0 && strrpos($value, "'") === strlen($value) - 1)
                ) {
                    $value = substr($value, 1, -1);
                }

                self::$vars[$key] = $value;
                $_ENV[$key] = $value; // También establecer en $_ENV para compatibilidad
            }
        }

        self::$loaded = true;
    }

    /**
     * Obtener variable de entorno.
     * @param string $key Nombre de la variable
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public static function get($key, $default = null)
    {
        if (!self::$loaded) {
            self::load();
        }

        return self::$vars[$key] ?? $default;
    }

    /**
     * Verificar si existe una variable.
     */
    public static function has($key)
    {
        if (!self::$loaded) {
            self::load();
        }

        return isset(self::$vars[$key]);
    }

    /**
     * Valores por defecto (fallback).
     */
    private static function setDefaults()
    {
        self::$vars = [
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'inventario_db',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'MAIL_HOST' => 'smtp.gmail.com',
            'MAIL_PORT' => '587',
            'DEBUG' => 'false',
        ];

        foreach (self::$vars as $key => $value) {
            $_ENV[$key] = $value;
        }
    }
}

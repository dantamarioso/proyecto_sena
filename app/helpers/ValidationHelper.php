<?php

/**
 * ValidationHelper - Funciones de validación centralizadas
 * Evita duplicación de código en Controllers
 */
class ValidationHelper
{
    /**
     * Validar formato de email
     */
    public static function validarEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? true : false;
    }

    /**
     * Validar contraseña según requisitos
     * - Mínimo 8 caracteres
     * - Al menos 1 mayúscula
     * - Al menos 1 carácter especial
     */
    public static function validarContraseña($password)
    {
        $hasLength = strlen($password) >= 8;
        $hasUpper = preg_match('/[A-Z]/', $password);
        $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>_\-]/', $password);

        return [
            'valida' => $hasLength && $hasUpper && $hasSpecial,
            'minLongitud' => $hasLength,
            'tieneMaxuscula' => $hasUpper,
            'tieneEspecial' => $hasSpecial
        ];
    }

    /**
     * Obtener mensajes de validación de contraseña
     */
    public static function obtenerErroresContraseña($validacion)
    {
        $errores = [];

        if (!$validacion['minLongitud']) {
            $errores[] = "La contraseña debe tener mínimo 8 caracteres.";
        }

        if (!$validacion['tieneMaxuscula']) {
            $errores[] = "La contraseña debe contener al menos una letra mayúscula.";
        }

        if (!$validacion['tieneEspecial']) {
            $errores[] = "La contraseña debe contener al menos un carácter especial (!@#$%&*).";
        }

        return $errores;
    }

    /**
     * Validar celular (opcional, formato flexible)
     */
    public static function validarCelular($celular)
    {
        if (empty($celular)) {
            return true; // Es opcional
        }

        // Permite números, espacios, guiones, + (formato internacional)
        return preg_match('/^[\d\s\-+()]+$/', $celular) ? true : false;
    }

    /**
     * Validar nombre de usuario (alfanumérico, guiones, guiones bajos)
     */
    public static function validarNombreUsuario($nombre)
    {
        return preg_match('/^[a-zA-Z0-9_-]{3,}$/', $nombre) ? true : false;
    }

    /**
     * Validar URL/archivo
     */
    public static function validarExtensionArchivo($filename, $extensionesPermitidas = ['jpg', 'jpeg', 'png'])
    {
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return in_array($ext, $extensionesPermitidas) ? true : false;
    }

    /**
     * Sanitizar entrada de texto
     */
    public static function sanitizar($texto)
    {
        return htmlspecialchars(trim($texto), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Generar código aleatorio de verificación
     */
    public static function generarCodigo($longitud = 6)
    {
        return str_pad(random_int(0, 999999), $longitud, '0', STR_PAD_LEFT);
    }
}

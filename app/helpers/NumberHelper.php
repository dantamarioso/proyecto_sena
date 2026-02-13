<?php

/**
 * NumberHelper - Utilidades para parsear/normalizar numeros decimales.
 *
 * Objetivo:
 * - Aceptar entradas como: 1, 1.1, 1.14, 1.234 (y tambien 1,14)
 * - Normalizar a un string con escala fija (por defecto 3): 1.000, 1.140, 1.234
 * - Evitar errores por floats al comparar/guardar en DECIMAL.
 */
class NumberHelper
{
    /**
     * Normaliza un decimal a escala fija.
     *
     * @param mixed $value
     * @param int $scale Cantidad maxima/fija de decimales.
     * @param bool $allowNegative Permite signo negativo.
     * @param bool $required Si es true, vacio es error.
     * @param string|null $error Mensaje de error (salida).
     * @return string|null Decimal normalizado (ej: 1.140) o null si vacio y no requerido.
     */
    public static function normalizeDecimal($value, $scale = 3, $allowNegative = false, $required = false, &$error = null)
    {
        $error = null;
        $scale = (int)$scale;
        if ($scale < 0) {
            $scale = 0;
        }

        if ($value === null) {
            $value = '';
        }

        $raw = trim((string)$value);

        if ($raw === '') {
            if ($required) {
                $error = 'El valor es obligatorio.';
            }
            return null;
        }

        // Remover espacios y NBSP
        $raw = str_replace(["\xC2\xA0", "\u{00A0}", ' ', "\t"], '', $raw);

        // Soportar separadores locales: detectar decimal por el ultimo separador
        $sign = '';
        $first = substr($raw, 0, 1);
        if ($first === '-' || $first === '+') {
            if ($first === '-') {
                $sign = '-';
            }
            $raw = substr($raw, 1);
        }

        if ($sign === '-' && !$allowNegative) {
            $error = 'El valor no puede ser negativo.';
            return null;
        }

        if ($raw === '') {
            $error = 'Formato numerico invalido.';
            return null;
        }

        // Mantener solo digitos y separadores (.,)
        $raw = preg_replace('/[^0-9\.,]/', '', $raw);

        if ($raw === '' || $raw === '.' || $raw === ',') {
            $error = 'Formato numerico invalido.';
            return null;
        }

        $commaPos = strrpos($raw, ',');
        $dotPos = strrpos($raw, '.');

        if ($commaPos !== false && $dotPos !== false) {
            // Ambos presentes: el ultimo es el decimal
            $decimalSep = ($commaPos > $dotPos) ? ',' : '.';
            $thousandSep = ($decimalSep === ',') ? '.' : ',';
            $raw = str_replace($thousandSep, '', $raw);
            $raw = str_replace($decimalSep, '.', $raw);
        } elseif ($commaPos !== false) {
            // Solo coma: si parece decimal (<= scale), convertir; si no, tratar como miles
            $parts = explode(',', $raw);
            if (count($parts) === 2 && $parts[1] !== '' && strlen($parts[1]) <= $scale) {
                $raw = str_replace('.', '', $raw); // puntos como miles
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace(',', '', $raw);
            }
        } else {
            // Solo punto o ninguno: si hay multiples puntos, asumir miles
            if ($dotPos !== false) {
                $parts = explode('.', $raw);
                if (!(count($parts) === 2 && $parts[1] !== '' && strlen($parts[1]) <= $scale)) {
                    $raw = str_replace('.', '', $raw);
                }
            }
        }

        // Prefijar 0 si viene como .5
        if (substr($raw, 0, 1) === '.') {
            $raw = '0' . $raw;
        }

        // Validar formato final
        if (!preg_match('/^\d+(\.\d+)?$/', $raw)) {
            $error = 'Formato numerico invalido.';
            return null;
        }

        $intPart = $raw;
        $fracPart = '';
        if (strpos($raw, '.') !== false) {
            [$intPart, $fracPart] = explode('.', $raw, 2);
        }

        if ($intPart === '') {
            $intPart = '0';
        }

        // Limpiar ceros a la izquierda
        $intPart = ltrim($intPart, '0');
        if ($intPart === '') {
            $intPart = '0';
        }

        $fracPart = (string)$fracPart;
        if ($scale === 0) {
            return $sign . $intPart;
        }

        if (strlen($fracPart) > $scale) {
            $error = 'El valor debe tener maximo ' . $scale . ' decimales.';
            return null;
        }

        $fracPart = str_pad($fracPart, $scale, '0');

        return $sign . $intPart . '.' . $fracPart;
    }
}

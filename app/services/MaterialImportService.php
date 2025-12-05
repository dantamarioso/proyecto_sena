<?php

require_once __DIR__ . '/../../vendor/autoload.php';


/**
 * Servicio para manejar importaciones de materiales.
 * Extrae la lógica compleja de importación CSV/Excel del controlador.
 */
class MaterialImportService
{
    private $materialModel;
    private $lineaModel;
    private $nodoModel;

    private $cacheLineas = null;
    private $cacheNodos = null;
    private $inicio_tiempo;

    public function __construct()
    {
        $this->materialModel = new Material();
        $this->lineaModel = new Linea();
        $this->nodoModel = new Nodo();
        $this->inicio_tiempo = microtime(true);
    }

    /**
     * Importa materiales desde un archivo CSV o Excel.
     */
    public function importFromFile($file, $data)
    {
        // Detectar formato de manera segura según la extensión del archivo; el formulario puede no enviar "formato"
        $ext = strtolower(pathinfo($file['name'] ?? '', PATHINFO_EXTENSION));
        $extEsExcel = in_array($ext, ['xlsx', 'xls', 'xlsm', 'ods'], true);
        $extEsCsv = in_array($ext, ['csv', 'txt'], true);

        // Priorizar detección automática por extensión, luego caer al valor enviado por el formulario
        if ($extEsExcel) {
            $formato = 'excel';
        } elseif ($extEsCsv) {
            $formato = 'csv';
        } else {
            $formato = $data['formato'] ?? 'csv';
        }

        if ($formato === 'excel') {
            return $this->formatResult($this->importFromExcel($file));
        }

        return $this->formatResult($this->importFromCSV($file));
    }

    /**
     * Importa desde CSV.
     */
    private function importFromCSV($file)
    {
        $lineas = file($file['tmp_name'], FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        if (!$lineas || count($lineas) === 0) {
            return ['success' => false, 'message' => 'El archivo está vacío.'];
        }

        // Detectar delimitador
        $delimitador = $this->detectarDelimitador($lineas[0]);

        // Procesar encabezados
        $encabezados = str_getcsv($lineas[0], $delimitador);

        if (count($encabezados) <= 1) {
            return ['success' => false, 'message' => 'Error al procesar encabezados del archivo.'];
        }

        // Mapear encabezados
        $mapeo = $this->mapearEncabezados($encabezados);

        if (!$mapeo['success']) {
            return $mapeo;
        }

        // Procesar filas
        $resultado = $this->procesarFilas(array_slice($lineas, 1), $delimitador, $mapeo['indices']);

        return $resultado;
    }

    /**
     * Importa desde Excel usando PhpSpreadsheet.
     */
    private function importFromExcel($file)
    {
        try {
            if (!class_exists('\\PhpOffice\\PhpSpreadsheet\\IOFactory')) {
                return ['success' => false, 'message' => 'Librería PhpSpreadsheet no está disponible'];
            }

            $spreadsheet = call_user_func(['\\PhpOffice\\PhpSpreadsheet\\IOFactory', 'load'], $file['tmp_name']);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            if (count($data) < 2) {
                return ['success' => false, 'message' => 'El archivo no contiene datos.'];
            }

            // Primera fila = encabezados
            $encabezados = $data[0];
            $mapeo = $this->mapearEncabezados($encabezados);

            if (!$mapeo['success']) {
                return $mapeo;
            }

            // Procesar filas
            $resultado = $this->procesarFilasExcel(array_slice($data, 1), $mapeo['indices']);

            return $resultado;
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al procesar Excel: ' . $e->getMessage()];
        }
    }

    /**
     * Detecta el delimitador del CSV.
     */
    private function detectarDelimitador($linea)
    {
        $delimitadores = [',', ';', '\t', '|'];
        $maxCampos = 0;
        $mejorDelimitador = ',';

        foreach ($delimitadores as $delim) {
            $campos = str_getcsv($linea, $delim);
            if (count($campos) > $maxCampos) {
                $maxCampos = count($campos);
                $mejorDelimitador = $delim;
            }
        }

        return $mejorDelimitador;
    }

    /**
     * Mapea los encabezados del archivo con los campos del modelo.
     */
    private function mapearEncabezados($encabezados)
    {
        // Ahora solo se requieren: código, nodo, línea
        $camposRequeridos = [
            'codigo' => ['codigo', 'código', 'codigo de material', 'código de material', 'cod material', 'codigo material', 'código sap', 'codigo sap', 'sap', 'cod_sap', 'code'],
            'nodo' => ['nodo', 'id nodo', 'nodo id', 'centro', 'centro de formación', 'centro de formacion'],
            'linea' => ['linea', 'línea', 'linea id', 'línea id', 'id linea', 'id línea', 'line', 'línea de negocio', 'linea de negocio'],
        ];

        // Campos opcionales
        $camposOpcionales = [
            'descripcion' => ['descripcion', 'descripción', 'desc', 'descripción del material'],
            'cantidad' => ['cantidad', 'qty', 'stock', 'existencia', 'existencias'],
            'nombre' => ['nombre', 'nombre del material', 'product name'],
            'fecha_adquisicion' => ['fecha de adquisición', 'fecha adquisicion', 'fecha adquisición', 'fecha compra', 'fecha de compra', 'fecha', 'date'],
            'valor_compra' => ['valor de compra', 'valor compra', 'precio compra', 'precio de compra', 'costo', 'price', 'valor', 'cost'],
            'categoria' => ['categoría', 'categoria', 'categoria', 'product category', 'tipo'],
            'presentacion' => ['presentación', 'presentacion', 'presentation', 'formato'],
            'medida' => ['medida', 'unidad de medida', 'unidad medida', 'unit', 'um', 'unidad'],
            'proveedor' => ['proveedor', 'supplier', 'fabricante', 'manufacturer'],
            'marca' => ['marca', 'brand', 'fabricante'],
            'estado' => ['estado', 'status', 'estatus'],
        ];

        $indices = [];

        foreach ($encabezados as $idx => $header) {
            // Normalizar encabezados para que coincidan sin importar mayúsculas, tildes o espacios especiales
            $headerLimpio = $this->normalizarHeader($header);

            // Buscar en campos requeridos (normalizando variantes + coincidencia parcial)
            foreach ($camposRequeridos as $campo => $variantes) {
                if ($this->headerMatches($headerLimpio, $variantes)) {
                    $indices[$campo] = $idx;
                }
            }

            // Buscar en campos opcionales (normalizando variantes + coincidencia parcial)
            foreach ($camposOpcionales as $campo => $variantes) {
                if ($this->headerMatches($headerLimpio, $variantes)) {
                    $indices[$campo] = $idx;
                }
            }
        }

        // Verificar campos obligatorios: código, nodo, línea
        if (!isset($indices['codigo']) || !isset($indices['nodo']) || !isset($indices['linea'])) {
            $faltantes = [];
            if (!isset($indices['codigo'])) $faltantes[] = 'Código';
            if (!isset($indices['nodo'])) $faltantes[] = 'Nodo';
            if (!isset($indices['linea'])) $faltantes[] = 'Línea';
            // Depuración: mostrar encabezados normalizados detectados
            $headersNorm = array_map(fn($h) => $this->normalizarHeader($h), $encabezados);

            return [
                'success' => false,
                'message' => 'El archivo debe contener al menos las columnas: ' . implode(' y ', $faltantes) . '. Encabezados detectados: ' . implode(', ', $headersNorm),
            ];
        }

        return ['success' => true, 'indices' => $indices];
    }

    /**
     * Normaliza encabezados para comparación: recorta, reemplaza espacios duros, pasa a minúsculas y elimina tildes.
     */
    private function normalizarHeader($header)
    {
        // Convertir a string seguro
        $header = (string)$header;

        // Reemplazar espacios no separables (nbsp) y recortar
        $header = str_replace(['\xC2\xA0', "\u00A0"], ' ', $header);
        $header = trim($header);

        // Minusculas
        $lower = function_exists('mb_strtolower') ? mb_strtolower($header, 'UTF-8') : strtolower($header);

        // Quitar tildes
        $sinTildes = strtr($lower, [
            'á' => 'a',
            'é' => 'e',
            'í' => 'i',
            'ó' => 'o',
            'ú' => 'u',
            'ü' => 'u',
            'à' => 'a',
            'è' => 'e',
            'ì' => 'i',
            'ò' => 'o',
            'ù' => 'u',
            'Á' => 'a',
            'É' => 'e',
            'Í' => 'i',
            'Ó' => 'o',
            'Ú' => 'u',
            'Ü' => 'u'
        ]);

        // Reemplazar signos/puntuación por espacio
        $soloAlnum = preg_replace('/[^a-z0-9]+/u', ' ', $sinTildes);

        // Colapsar espacios
        $colapsado = preg_replace('/\s+/', ' ', $soloAlnum);

        return trim($colapsado);
    }

    /**
     * Verifica si un header normalizado coincide con alguna variante (exacto o contiene).
     */
    private function headerMatches($headerNormalizado, $variantes)
    {
        foreach ($variantes as $var) {
            $normVar = $this->normalizarHeader($var);
            if ($headerNormalizado === $normVar) {
                return true;
            }
            // Coincidencia parcial útil para "codigo de material"
            if ($normVar && str_contains($headerNormalizado, $normVar)) {
                return true;
            }
        }
        return false;
    }

    /** Limpia una celda: elimina nbsp, recorta espacios, devuelve string. */
    private function cleanCell($value)
    {
        if ($value === null) return '';
        $value = (string)$value;
        $value = str_replace(["\xC2\xA0", "\u00A0"], ' ', $value);
        return trim($value);
    }

    /** Resuelve nodo por id o nombre (case/tildes insensible). */
    private function resolveNodoId($valor)
    {
        if ($valor === '') return null;

        // Si es numérico, usar directamente
        if (is_numeric($valor)) {
            return (int)$valor;
        }

        // Cachear nodos
        if ($this->cacheNodos === null) {
            $this->cacheNodos = $this->nodoModel->all() ?? [];
        }

        $buscado = $this->normalizarHeader($valor);
        foreach ($this->cacheNodos as $nodo) {
            $nombreNodo = $this->normalizarHeader($nodo['nombre'] ?? '');
            if ($buscado !== '' && $nombreNodo === $buscado) {
                return (int)$nodo['id'];
            }
        }

        return null;
    }

    /** Resuelve línea por id o nombre (case/tildes insensible). */
    private function resolveLineaId($valor)
    {
        if ($valor === '') return null;

        if (is_numeric($valor)) {
            return (int)$valor;
        }

        if ($this->cacheLineas === null) {
            $this->cacheLineas = $this->lineaModel->all() ?? [];
        }

        $buscado = $this->normalizarHeader($valor);
        foreach ($this->cacheLineas as $linea) {
            $nombreLinea = $this->normalizarHeader($linea['nombre'] ?? '');
            if ($buscado !== '' && $nombreLinea === $buscado) {
                return (int)$linea['id'];
            }
        }

        return null;
    }

    /**
     * Procesa las filas del CSV.
     */
    private function procesarFilas($lineas, $delimitador, $indices)
    {
        $insertados = 0;
        $actualizados = 0;
        $errores = [];
        $advertencias = [];

        foreach ($lineas as $numeroLinea => $linea) {
            $campos = str_getcsv($linea, $delimitador);

            $material = $this->extraerDatosFila($campos, $indices);

            if (!$material['codigo']) {
                $advertencias[] = "Fila " . ($numeroLinea + 2) . ": código vacío, ignorada";
                continue;
            }

            // Validar material
            $validacion = $this->validarMaterial($material, $numeroLinea + 2);
            if (!$validacion['valido']) {
                $advertencias = array_merge($advertencias, $validacion['advertencias']);
                continue;
            }

            // Verificar referencias
            if (!$this->validarReferencias($material, $numeroLinea + 2, $advertencias)) {
                continue;
            }

            // Insertar o actualizar
            try {
                $existente = $this->materialModel->findByCodigoNodoLinea(
                    $material['codigo'],
                    $material['nodo_id'],
                    $material['linea_id']
                );

                if ($existente) {
                    $this->materialModel->update($existente['id'], $material);
                    $actualizados++;
                } else {
                    $this->materialModel->create($material);
                    $insertados++;
                }
            } catch (Exception $e) {
                $errores[] = "Fila " . ($numeroLinea + 2) . ": " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'errores' => $errores,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Procesa las filas de Excel.
     */
    private function procesarFilasExcel($filas, $indices)
    {
        $insertados = 0;
        $actualizados = 0;
        $errores = [];
        $advertencias = [];

        foreach ($filas as $numeroLinea => $campos) {
            $material = $this->extraerDatosFila($campos, $indices);

            if (!$material['codigo']) {
                $advertencias[] = "Fila " . ($numeroLinea + 2) . ": código vacío, ignorada";
                continue;
            }

            // Validar material
            $validacion = $this->validarMaterial($material, $numeroLinea + 2);
            if (!$validacion['valido']) {
                $advertencias = array_merge($advertencias, $validacion['advertencias']);
                continue;
            }

            // Verificar referencias
            if (!$this->validarReferencias($material, $numeroLinea + 2, $advertencias)) {
                continue;
            }

            try {
                $existente = $this->materialModel->findByCodigoNodoLinea(
                    $material['codigo'],
                    $material['nodo_id'],
                    $material['linea_id']
                );

                if ($existente) {
                    $this->materialModel->update($existente['id'], $material);
                    $actualizados++;
                } else {
                    $this->materialModel->create($material);
                    $insertados++;
                }
            } catch (Exception $e) {
                $errores[] = "Fila " . ($numeroLinea + 2) . ": " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'errores' => $errores,
            'advertencias' => $advertencias,
        ];
    }

    /**
     * Formatea la respuesta para el frontend (materiales.js) con los campos esperados.
     */
    private function formatResult($result)
    {
        if (!$result['success']) {
            return $result;
        }

        $totalProcesados = ($result['insertados'] ?? 0) + ($result['actualizados'] ?? 0);
        $duracion = round(microtime(true) - $this->inicio_tiempo, 2);

        return array_merge($result, [
            'message' => 'Importación completada: ' . $result['insertados'] . ' creados, ' . $result['actualizados'] . ' actualizados',
            'materiales_creados' => $result['insertados'] ?? 0,
            'materiales_actualizados' => $result['actualizados'] ?? 0,
            'total_procesados' => $totalProcesados,
            'advertencias' => $result['advertencias'] ?? [],
            'errores_por_linea' => $result['errores'] ?? [],
            'duracion_segundos' => $duracion,
            'total_warnings' => count($result['advertencias'] ?? []),
            'total_errors' => count($result['errores'] ?? [])
        ]);
    }

    /**
     * Valida un material antes de insertarlo/actualizarlo.
     */
    private function validarMaterial($material, $numeroLinea)
    {
        $advertencias = [];
        $valido = true;

        if (empty($material['nombre'])) {
            $advertencias[] = "Fila $numeroLinea: nombre no especificado (se ignorará esta fila)";
            $valido = false;
        }

        if (strlen($material['nombre'] ?? '') > 100) {
            $advertencias[] = "Fila $numeroLinea: nombre muy largo (>100 caracteres), se truncará";
        }

        if (strlen($material['codigo'] ?? '') > 50) {
            $advertencias[] = "Fila $numeroLinea: código muy largo (>50 caracteres), se truncará";
            $material['codigo'] = substr($material['codigo'], 0, 50);
        }

        return [
            'valido' => $valido,
            'advertencias' => $advertencias
        ];
    }

    /**
     * Valida que nodo_id y linea_id existan en BD.
     */
    private function validarReferencias($material, $numeroLinea, &$advertencias)
    {
        if (!$material['nodo_id']) {
            $advertencias[] = "Fila $numeroLinea: nodo no encontrado o inválido";
            return false;
        }

        if (!$material['linea_id']) {
            $advertencias[] = "Fila $numeroLinea: línea no encontrada o inválida";
            return false;
        }

        // Verificar que existan en BD
        $nodoValido = $this->nodoModel->getById($material['nodo_id']);
        $lineaValida = $this->lineaModel->getById($material['linea_id']);

        if (!$nodoValido) {
            $advertencias[] = "Fila $numeroLinea: nodo ID {$material['nodo_id']} no existe en la base de datos";
            return false;
        }

        if (!$lineaValida) {
            $advertencias[] = "Fila $numeroLinea: línea ID {$material['linea_id']} no existe en la base de datos";
            return false;
        }

        return true;
    }

    /**
     * Extrae los datos de una fila.
     */
    private function extraerDatosFila($campos, $indices)
    {
        $material = [
            'codigo' => $this->cleanCell($campos[$indices['codigo']] ?? ''),
            'descripcion' => isset($indices['descripcion']) ? $this->cleanCell($campos[$indices['descripcion']] ?? '') : '',
            'cantidad' => isset($indices['cantidad']) ? intval($this->cleanCell($campos[$indices['cantidad']] ?? 0)) : 0,
            'nombre' => isset($indices['nombre']) ? $this->cleanCell($campos[$indices['nombre']] ?? '') : '',
            'estado' => 1,
        ];

        // Fallback de nombre con descripción si falta nombre
        if (!$material['nombre'] && !empty($material['descripcion'])) {
            $material['nombre'] = $material['descripcion'];
        }

        // Agregar campos opcionales si existen
        if (isset($indices['fecha_adquisicion'])) {
            $fecha = $this->cleanCell($campos[$indices['fecha_adquisicion']] ?? '');
            if ($fecha) {
                // Intentar convertir a formato Y-m-d
                $timestamp = strtotime($fecha);
                if ($timestamp !== false) {
                    $material['fecha_adquisicion'] = date('Y-m-d', $timestamp);
                }
            }
        }

        if (isset($indices['valor_compra'])) {
            $valor = $this->cleanCell($campos[$indices['valor_compra']] ?? '');
            if ($valor) {
                // Limpiar y convertir a número decimal
                $valor = str_replace([',', '.'], ['', '.'], $valor); // Normalizar decimales
                $valor = floatval($valor);
                if ($valor > 0) {
                    $material['valor_compra'] = $valor;
                }
            }
        }

        if (isset($indices['categoria'])) {
            $material['categoria'] = $this->cleanCell($campos[$indices['categoria']] ?? '');
        }

        if (isset($indices['presentacion'])) {
            $material['presentacion'] = $this->cleanCell($campos[$indices['presentacion']] ?? '');
        }

        if (isset($indices['medida'])) {
            $material['medida'] = $this->cleanCell($campos[$indices['medida']] ?? '');
        }

        if (isset($indices['proveedor'])) {
            $material['proveedor'] = $this->cleanCell($campos[$indices['proveedor']] ?? '');
        }

        if (isset($indices['marca'])) {
            $material['marca'] = $this->cleanCell($campos[$indices['marca']] ?? '');
        }

        // Resolver nodo y línea (id o nombre)
        $nodoVal = $this->cleanCell($campos[$indices['nodo']] ?? '');
        $lineaVal = $this->cleanCell($campos[$indices['linea']] ?? '');
        $material['nodo_id'] = $this->resolveNodoId($nodoVal);
        $material['linea_id'] = $this->resolveLineaId($lineaVal);

        if (isset($indices['estado'])) {
            $material['estado'] = $this->parseEstado($this->cleanCell($campos[$indices['estado']] ?? ''));
        }

        return $material;
    }

    /**
     * Parsea el campo estado: solo "activo" es 1, todo lo demás es 0 (inactivo).
     */
    private function parseEstado($valor)
    {
        $valor = trim(strtolower($valor));
        // Solo es activo si es exactamente uno de estos valores
        $activos = ['activo', 'active', '1', 'true', 'si', 'yes', 's', 'sí'];
        return in_array($valor, $activos, true) ? 1 : 0;
    }
}

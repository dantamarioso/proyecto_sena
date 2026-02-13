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

    private $contextNodoId = null;
    private $contextLineaId = null;

    private $importRol = 'usuario';

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
        $this->setContextFromRequest($data);

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
     * Contexto de importacion:
     * - Si el archivo no incluye Nodo/Línea, se toma de POST o de la sesión.
     */
    private function setContextFromRequest($data)
    {
        $this->importRol = $_SESSION['user']['rol'] ?? 'usuario';

        $nodoId = $data['nodo_id'] ?? ($data['nodo'] ?? null);
        $lineaId = $data['linea_id'] ?? ($data['linea'] ?? null);

        $sessionNodoId = isset($_SESSION['user']['nodo_id']) ? (int)$_SESSION['user']['nodo_id'] : null;
        $sessionLineaId = isset($_SESSION['user']['linea_id']) ? (int)$_SESSION['user']['linea_id'] : null;

        // Dinamizador: siempre restringido a su nodo
        if ($this->importRol === 'dinamizador') {
            $this->contextNodoId = $sessionNodoId;
        } else {
            if (!empty($nodoId) && is_numeric($nodoId)) {
                $this->contextNodoId = (int)$nodoId;
            } else {
                $this->contextNodoId = $sessionNodoId;
            }
        }

        if (!empty($lineaId) && is_numeric($lineaId)) {
            $this->contextLineaId = (int)$lineaId;
        } else {
            $this->contextLineaId = $sessionLineaId;
        }
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

        // Detectar delimitador usando varias lineas (soporta que la primera sea el titulo)
        $delimitador = $this->detectarDelimitadorEnLineas($lineas);

        // Encontrar encabezados (saltando titulo/filas vacias)
        $headerIndex = null;
        $mapeo = null;
        foreach ($lineas as $idx => $linea) {
            $campos = str_getcsv($linea, $delimitador);
            if ($this->isEmptyRow($campos)) {
                continue;
            }
            if ($this->isTitleRow($campos)) {
                continue;
            }

            $mapeoTry = $this->mapearEncabezados($campos);
            if ($mapeoTry['success']) {
                $headerIndex = $idx;
                $mapeo = $mapeoTry;
                break;
            }
        }

        if ($headerIndex === null || !$mapeo) {
            return ['success' => false, 'message' => 'No se encontraron encabezados validos en el archivo.'];
        }

        if (!isset($mapeo['indices']['nodo']) && empty($this->contextNodoId)) {
            return [
                'success' => false,
                'message' => 'No se pudo determinar el nodo para la importación. Seleccione un nodo en los filtros o incluya una columna "Nodo" en el archivo.',
            ];
        }

        if (!isset($mapeo['indices']['linea']) && empty($this->contextLineaId)) {
            return [
                'success' => false,
                'message' => 'No se pudo determinar la línea para la importación. Seleccione una línea en los filtros o incluya una columna "Línea" en el archivo.',
            ];
        }

        // Procesar filas (linea real = indice + 1)
        $dataLines = array_slice($lineas, $headerIndex + 1);
        $primerNumeroLinea = ($headerIndex + 2);

        return $this->procesarFilas($dataLines, $delimitador, $mapeo['indices'], $primerNumeroLinea);
    }

    /** Detecta el delimitador del CSV usando varias lineas. */
    private function detectarDelimitadorEnLineas($lineas)
    {
        $delimitadores = [',', ';', "\t", '|'];
        $maxCampos = 0;
        $mejorDelimitador = ';';

        $muestra = array_slice($lineas, 0, 10);
        foreach ($delimitadores as $delim) {
            foreach ($muestra as $linea) {
                $campos = str_getcsv($linea, $delim);
                if (count($campos) > $maxCampos) {
                    $maxCampos = count($campos);
                    $mejorDelimitador = $delim;
                }
            }
        }

        return $mejorDelimitador;
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
            $rows = $sheet->toArray();

            if (count($rows) < 1) {
                return ['success' => false, 'message' => 'El archivo no contiene datos.'];
            }

            // Encontrar encabezados (saltando titulo/filas vacias)
            $headerIndex = null;
            $mapeo = null;
            foreach ($rows as $idx => $row) {
                if ($this->isEmptyRow($row)) {
                    continue;
                }
                if ($this->isTitleRow($row)) {
                    continue;
                }

                $mapeoTry = $this->mapearEncabezados($row);
                if ($mapeoTry['success']) {
                    $headerIndex = $idx;
                    $mapeo = $mapeoTry;
                    break;
                }
            }

            if ($headerIndex === null || !$mapeo) {
                return ['success' => false, 'message' => 'No se encontraron encabezados validos en el archivo Excel.'];
            }

            if (!isset($mapeo['indices']['nodo']) && empty($this->contextNodoId)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo determinar el nodo para la importación. Seleccione un nodo en los filtros o incluya una columna "Nodo" en el archivo.',
                ];
            }

            if (!isset($mapeo['indices']['linea']) && empty($this->contextLineaId)) {
                return [
                    'success' => false,
                    'message' => 'No se pudo determinar la línea para la importación. Seleccione una línea en los filtros o incluya una columna "Línea" en el archivo.',
                ];
            }

            $dataRows = array_slice($rows, $headerIndex + 1);
            $primerNumeroLinea = ($headerIndex + 2);

            return $this->procesarFilasExcel($dataRows, $mapeo['indices'], $primerNumeroLinea);
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al procesar Excel: ' . $e->getMessage()];
        }
    }

    /**
     * Detecta el delimitador del CSV.
     */
    private function detectarDelimitador($linea)
    {
        $delimitadores = [',', ';', "\t", '|'];
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
        // Formatos soportados:
        // - Lista Maestra (Excel/CSV) con titulo + estos encabezados
        // - Export anterior del sistema (compatibilidad)

        $camposRequeridos = [
            'nombre' => [
                'nombre',
                'nombre del material',
                'nombre material',
                'product name'
            ],
        ];

        $camposOpcionales = [
            'id' => ['no de consecutivo', 'no consecutivo', 'consecutivo', '#', 'id'],
            'codigo' => ['codigo', 'código', 'codigo de material', 'código de material', 'codigo material', 'código sap', 'codigo sap', 'sap', 'cod_sap', 'code'],
            'descripcion' => ['descripcion material', 'descripción material', 'descripcion', 'descripción', 'desc'],
            'fecha_adquisicion' => ['fecha de compra', 'fecha compra', 'fecha de adquisicion', 'fecha de adquisición', 'fecha adquisicion', 'fecha adquisición', 'fecha', 'date'],
            'valor_compra' => ['valor de compra', 'valor compra', 'precio compra', 'precio de compra', 'costo', 'price', 'valor', 'cost'],
            'fecha_fabricacion' => ['fecha de fabricacion', 'fecha fabricación', 'fecha fabricacion'],
            'fecha_vencimiento' => ['fecha de vencimiento', 'fecha vencimiento', 'vencimiento'],
            'fabricante' => ['fabricante', 'manufacturer', 'marca'],
            'medida' => ['unidad de medida', 'unidad medida', 'medida', 'unit', 'um', 'unidad'],
            'presentacion' => ['presentacion', 'presentación', 'presentation', 'formato'],
            'categoria' => ['categoría', 'categoria', 'product category', 'tipo'],
            'cantidad_requerida' => ['cantidad requerida', 'cant requerida', 'requerida', 'qty requerida'],
            'cantidad' => ['cantidad en stock', 'cantidad stock', 'stock', 'existencia', 'existencias', 'cantidad'],
            'cantidad_faltante' => ['cantidad faltante', 'faltante'],
            'ubicacion' => ['ubicación', 'ubicacion', 'lugar', 'localizacion', 'localización'],
            'observacion' => ['observación', 'observacion', 'observaciones', 'nota', 'notas', 'comentario', 'comentarios'],

            // Compatibilidad con export anterior
            'nodo' => ['nodo', 'id nodo', 'nodo id', 'centro', 'centro de formación', 'centro de formacion'],
            'linea' => ['linea', 'línea', 'linea id', 'línea id', 'id linea', 'id línea', 'line', 'línea de negocio', 'linea de negocio'],
            'proveedor' => ['proveedor', 'supplier'],
            'marca' => ['marca', 'brand'],
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

        // Verificar campos obligatorios
        foreach (array_keys($camposRequeridos) as $campoReq) {
            if (!isset($indices[$campoReq])) {
                $headersNorm = array_map(fn($h) => $this->normalizarHeader($h), $encabezados);
                return [
                    'success' => false,
                    'message' => 'El archivo debe contener al menos la columna: Nombre del material. Encabezados detectados: ' . implode(', ', $headersNorm),
                    'encabezados_esperados' => [
                        'No de consecutivo',
                        'Código',
                        'Nodo',
                        'Línea',
                        'Nombre del material',
                        'Descripción material',
                        'Fecha de compra',
                        'Valor de compra',
                        'Fecha de fabricación',
                        'Fecha de vencimiento',
                        'Fabricante',
                        'Unidad de medida',
                        'Presentación',
                        'Categoría',
                        'Cantidad requerida',
                        'Cantidad en Stock',
                        'Cantidad faltante',
                        'Ubicación',
                        'Observación'
                    ],
                ];
            }
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

        // Remover BOM UTF-8 si viene en el primer encabezado
        $header = preg_replace('/^\xEF\xBB\xBF/', '', $header);

        // Reemplazar espacios no separables (nbsp) y recortar
        $header = str_replace(["\xC2\xA0", "\u{00A0}"], ' ', $header);
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
        $value = preg_replace('/^\xEF\xBB\xBF/', '', $value);
        $value = str_replace(["\xC2\xA0", "\u{00A0}"], ' ', $value);
        return trim($value);
    }

    /** True si una fila esta vacia (todas las celdas en blanco). */
    private function isEmptyRow($row)
    {
        if (!is_array($row)) {
            return true;
        }

        foreach ($row as $cell) {
            if ($this->cleanCell($cell) !== '') {
                return false;
            }
        }

        return true;
    }

    /** True si la fila corresponde al titulo "Lista maestra..." (para saltarla). */
    private function isTitleRow($row)
    {
        if (!is_array($row)) {
            return false;
        }

        $nonEmpty = [];
        foreach ($row as $cell) {
            $val = $this->cleanCell($cell);
            if ($val !== '') {
                $nonEmpty[] = $val;
            }
        }

        if (count($nonEmpty) !== 1) {
            return false;
        }

        $text = $this->normalizarHeader($nonEmpty[0]);
        return $text !== '' && str_contains($text, 'lista maestra de materiales');
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
        $matches = [];

        foreach ($this->cacheNodos as $nodo) {
            $nombreNodo = $this->normalizarHeader($nodo['nombre'] ?? '');
            if ($buscado !== '' && $nombreNodo === $buscado) {
                return (int)$nodo['id'];
            }
            if ($buscado !== '' && $nombreNodo !== '' && str_contains($nombreNodo, $buscado)) {
                $matches[] = (int)$nodo['id'];
            }
            if ($buscado !== '' && $nombreNodo !== '' && str_contains($buscado, $nombreNodo)) {
                $matches[] = (int)$nodo['id'];
            }
        }

        $matches = array_values(array_unique($matches));

        // Si solo hay un match parcial, usarlo
        if (count($matches) === 1) {
            return $matches[0];
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
        $matches = [];

        foreach ($this->cacheLineas as $linea) {
            $nombreLinea = $this->normalizarHeader($linea['nombre'] ?? '');
            if ($buscado !== '' && $nombreLinea === $buscado) {
                return (int)$linea['id'];
            }
            if ($buscado !== '' && $nombreLinea !== '' && str_contains($nombreLinea, $buscado)) {
                $matches[] = (int)$linea['id'];
            }
            if ($buscado !== '' && $nombreLinea !== '' && str_contains($buscado, $nombreLinea)) {
                $matches[] = (int)$linea['id'];
            }
        }

        $matches = array_values(array_unique($matches));

        if (count($matches) === 1) {
            return $matches[0];
        }

        return null;
    }

    /** Parsea enteros tolerando separadores (1.000 / 1,000). */
    private function parseIntValue($value, $default = 0)
    {
        $value = $this->cleanCell($value);
        if ($value === '') return $default;

        $raw = preg_replace('/[^0-9\-]+/', '', $value);
        if ($raw === '' || $raw === '-') return $default;

        return (int)$raw;
    }

    /**
     * Parsea decimales (hasta 3) tolerando separadores locales.
     * Retorna string normalizado con 3 decimales (ej: 1.140) para guardar en DECIMAL.
     */
    private function parseDecimalValue($value, $default = '0.000', $scale = 3)
    {
        $value = $this->cleanCell($value);
        if ($value === '') return $default;

        $lower = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
        if (in_array($lower, ['na', 'n/a', 'no aplica'], true)) {
            return $default;
        }

        $err = null;
        $norm = NumberHelper::normalizeDecimal($value, (int)$scale, false, false, $err);
        if ($norm === null || $err) {
            return $default;
        }

        return $norm;
    }

    /** Parsea moneda tolerando $ y separadores locales. Retorna float|null. */
    private function parseMoney($value)
    {
        $value = $this->cleanCell($value);
        if ($value === '') return null;

        $lower = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
        if (in_array($lower, ['na', 'n/a', 'no aplica'], true)) {
            return null;
        }

        $raw = preg_replace('/[^0-9,\.\-]+/', '', $value);
        if ($raw === '' || $raw === '-' || $raw === '.' || $raw === ',') {
            return null;
        }

        $commaPos = strrpos($raw, ',');
        $dotPos = strrpos($raw, '.');

        if ($commaPos !== false && $dotPos !== false) {
            $decimalSep = ($commaPos > $dotPos) ? ',' : '.';
            $thousandSep = ($decimalSep === ',') ? '.' : ',';
            $raw = str_replace($thousandSep, '', $raw);
            $raw = str_replace($decimalSep, '.', $raw);
        } elseif ($commaPos !== false) {
            $parts = explode(',', $raw);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                $raw = str_replace('.', '', $raw);
                $raw = str_replace(',', '.', $raw);
            } else {
                $raw = str_replace(',', '', $raw);
            }
        } elseif ($dotPos !== false) {
            $parts = explode('.', $raw);
            if (count($parts) === 2 && strlen($parts[1]) <= 2) {
                $raw = str_replace(',', '', $raw);
            } else {
                $raw = str_replace('.', '', $raw);
            }
        }

        return (float)$raw;
    }

    /** Convierte una fecha a Y-m-d (soporta dd/mm/yyyy). Retorna null si no es valida. */
    private function parseDateToYmd($value)
    {
        $value = $this->cleanCell($value);
        if ($value === '') return null;

        $lower = function_exists('mb_strtolower') ? mb_strtolower($value, 'UTF-8') : strtolower($value);
        if (in_array($lower, ['na', 'n/a', 'no aplica'], true)) {
            return null;
        }

        if (is_numeric($value)) {
            if (class_exists('\\PhpOffice\\PhpSpreadsheet\\Shared\\Date')) {
                try {
                    $dt = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float)$value);
                    return $dt->format('Y-m-d');
                } catch (Exception $e) {
                    // continuar
                }
            }
        }

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        if (preg_match('#^(\d{1,2})[\/-](\d{1,2})[\/-](\d{2,4})$#', $value, $m)) {
            $day = (int)$m[1];
            $month = (int)$m[2];
            $year = (int)$m[3];
            if ($year < 100) {
                $year += 2000;
            }

            if (checkdate($month, $day, $year)) {
                return sprintf('%04d-%02d-%02d', $year, $month, $day);
            }
        }

        $ts = strtotime($value);
        if ($ts !== false) {
            return date('Y-m-d', $ts);
        }

        return null;
    }

    /**
     * Procesa las filas del CSV.
     */
    private function procesarFilas($lineas, $delimitador, $indices, $primerNumeroLinea = 2)
    {
        $insertados = 0;
        $actualizados = 0;
        $errores = [];
        $advertencias = [];

        foreach ($lineas as $numeroLinea => $linea) {
            $campos = str_getcsv($linea, $delimitador);

            if ($this->isEmptyRow($campos)) {
                continue;
            }

            $numeroArchivo = $primerNumeroLinea + $numeroLinea;

            $material = $this->extraerDatosFila($campos, $indices);

            // Validar material
            $validacion = $this->validarMaterial($material, $numeroArchivo);
            if (!$validacion['valido']) {
                $advertencias = array_merge($advertencias, $validacion['advertencias']);
                continue;
            }

            // Verificar referencias
            if (!$this->validarReferencias($material, $numeroArchivo, $advertencias)) {
                continue;
            }

            // Insertar o actualizar
            try {
                $idArchivo = (int)($material['id'] ?? 0);
                if ($idArchivo > 0) {
                    $existente = $this->materialModel->getById($idArchivo);
                    if ($existente) {
                        $this->materialModel->update($idArchivo, $material);
                        $actualizados++;
                    } else {
                        $this->materialModel->create($material);
                        $insertados++;
                    }
                } else {
                    $codigoKey = trim((string)($material['codigo'] ?? ''));
                    $nodoKey = (int)($material['nodo_id'] ?? 0);
                    $lineaKey = (int)($material['linea_id'] ?? 0);

                    $existente = null;
                    if ($codigoKey !== '' && strcasecmp($codigoKey, 'Pendiente') !== 0) {
                        $existente = $this->materialModel->findByCodigoNodoLinea($codigoKey, $nodoKey, $lineaKey);
                    }

                    if (!$existente && strcasecmp($codigoKey, 'Pendiente') === 0 && !empty($material['nombre'])) {
                        $existente = $this->materialModel->findByNombreNodoLinea($material['nombre'], $nodoKey, $lineaKey);
                    }

                    if ($existente) {
                        $this->materialModel->update($existente['id'], $material);
                        $actualizados++;
                    } else {
                        $this->materialModel->create($material);
                        $insertados++;
                    }
                }
            } catch (Exception $e) {
                $errores[] = "Fila " . $numeroArchivo . ": " . $e->getMessage();
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
    private function procesarFilasExcel($filas, $indices, $primerNumeroLinea = 2)
    {
        $insertados = 0;
        $actualizados = 0;
        $errores = [];
        $advertencias = [];

        foreach ($filas as $numeroLinea => $campos) {
            $numeroArchivo = $primerNumeroLinea + $numeroLinea;
            if ($this->isEmptyRow($campos)) {
                continue;
            }

            $material = $this->extraerDatosFila($campos, $indices);

            // Validar material
            $validacion = $this->validarMaterial($material, $numeroArchivo);
            if (!$validacion['valido']) {
                $advertencias = array_merge($advertencias, $validacion['advertencias']);
                continue;
            }

            // Verificar referencias
            if (!$this->validarReferencias($material, $numeroArchivo, $advertencias)) {
                continue;
            }

            try {
                $idArchivo = (int)($material['id'] ?? 0);
                if ($idArchivo > 0) {
                    $existente = $this->materialModel->getById($idArchivo);
                    if ($existente) {
                        $this->materialModel->update($idArchivo, $material);
                        $actualizados++;
                    } else {
                        $this->materialModel->create($material);
                        $insertados++;
                    }
                } else {
                    $codigoKey = trim((string)($material['codigo'] ?? ''));
                    $nodoKey = (int)($material['nodo_id'] ?? 0);
                    $lineaKey = (int)($material['linea_id'] ?? 0);

                    $existente = null;
                    if ($codigoKey !== '' && strcasecmp($codigoKey, 'Pendiente') !== 0) {
                        $existente = $this->materialModel->findByCodigoNodoLinea($codigoKey, $nodoKey, $lineaKey);
                    }

                    if (!$existente && strcasecmp($codigoKey, 'Pendiente') === 0 && !empty($material['nombre'])) {
                        $existente = $this->materialModel->findByNombreNodoLinea($material['nombre'], $nodoKey, $lineaKey);
                    }

                    if ($existente) {
                        $this->materialModel->update($existente['id'], $material);
                        $actualizados++;
                    } else {
                        $this->materialModel->create($material);
                        $insertados++;
                    }
                }
            } catch (Exception $e) {
                $errores[] = "Fila " . $numeroArchivo . ": " . $e->getMessage();
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

        // Verificar que el nodo exista
        $nodoValido = $this->nodoModel->getById($material['nodo_id']);
        if (!$nodoValido) {
            $advertencias[] = "Fila $numeroLinea: nodo ID {$material['nodo_id']} no existe en la base de datos";
            return false;
        }

        $lineaValida = $this->lineaModel->getById($material['linea_id']);
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
        $material = [];

        $material['id'] = isset($indices['id']) ? $this->parseIntValue($campos[$indices['id']] ?? 0, 0) : 0;

        $material['codigo'] = isset($indices['codigo']) ? $this->cleanCell($campos[$indices['codigo']] ?? '') : '';
        if ($material['codigo'] === '') {
            $material['codigo'] = 'Pendiente';
        }

        if (strlen($material['codigo']) > 50) {
            $material['codigo'] = substr($material['codigo'], 0, 50);
        }

        $material['nombre'] = $this->cleanCell($campos[$indices['nombre']] ?? '');

        $material['descripcion'] = isset($indices['descripcion']) ? $this->cleanCell($campos[$indices['descripcion']] ?? '') : '';
        if ($material['nombre'] === '' && $material['descripcion'] !== '') {
            $material['nombre'] = $material['descripcion'];
        }

        if (strlen($material['nombre']) > 100) {
            $material['nombre'] = substr($material['nombre'], 0, 100);
        }

        $material['fecha_adquisicion'] = isset($indices['fecha_adquisicion']) ? ($this->parseDateToYmd($campos[$indices['fecha_adquisicion']] ?? '') ?? null) : null;
        $material['valor_compra'] = isset($indices['valor_compra']) ? $this->parseMoney($campos[$indices['valor_compra']] ?? '') : null;

        $material['fecha_fabricacion'] = isset($indices['fecha_fabricacion']) ? ($this->parseDateToYmd($campos[$indices['fecha_fabricacion']] ?? '') ?? null) : null;
        $material['fecha_vencimiento'] = isset($indices['fecha_vencimiento']) ? ($this->parseDateToYmd($campos[$indices['fecha_vencimiento']] ?? '') ?? null) : null;

        $material['fabricante'] = isset($indices['fabricante']) ? $this->cleanCell($campos[$indices['fabricante']] ?? '') : '';
        $material['medida'] = isset($indices['medida']) ? $this->cleanCell($campos[$indices['medida']] ?? '') : '';
        $material['presentacion'] = isset($indices['presentacion']) ? $this->cleanCell($campos[$indices['presentacion']] ?? '') : '';
        $material['categoria'] = isset($indices['categoria']) ? $this->cleanCell($campos[$indices['categoria']] ?? '') : '';

        if (strlen($material['fabricante']) > 200) $material['fabricante'] = substr($material['fabricante'], 0, 200);
        if (strlen($material['medida']) > 50) $material['medida'] = substr($material['medida'], 0, 50);
        if (strlen($material['presentacion']) > 100) $material['presentacion'] = substr($material['presentacion'], 0, 100);
        if (strlen($material['categoria']) > 100) $material['categoria'] = substr($material['categoria'], 0, 100);

        $material['cantidad_requerida'] = isset($indices['cantidad_requerida']) ? $this->parseDecimalValue($campos[$indices['cantidad_requerida']] ?? '', '0.000', 3) : '0.000';
        $material['cantidad'] = isset($indices['cantidad']) ? $this->parseDecimalValue($campos[$indices['cantidad']] ?? '', '0.000', 3) : '0.000';

        $material['ubicacion'] = isset($indices['ubicacion']) ? $this->cleanCell($campos[$indices['ubicacion']] ?? '') : '';
        $material['observacion'] = isset($indices['observacion']) ? $this->cleanCell($campos[$indices['observacion']] ?? '') : '';

        if (strlen($material['ubicacion']) > 200) $material['ubicacion'] = substr($material['ubicacion'], 0, 200);

        // Compatibilidad si el archivo trae proveedor/marca
        if (isset($indices['proveedor'])) {
            $material['proveedor'] = $this->cleanCell($campos[$indices['proveedor']] ?? '');
        }
        if (isset($indices['marca'])) {
            $material['marca'] = $this->cleanCell($campos[$indices['marca']] ?? '');
        }

        // Resolver nodo y línea (si vienen en el archivo), si no usar contexto
        $nodoId = null;
        if (isset($indices['nodo'])) {
            $nodoRaw = $this->cleanCell($campos[$indices['nodo']] ?? '');
            if ($nodoRaw === '') {
                $nodoId = $this->contextNodoId;
            } else {
                $nodoId = $this->resolveNodoId($nodoRaw);
            }
        } else {
            $nodoId = $this->contextNodoId;
        }

        // Restriccion de seguridad: dinamizador solo puede importar a su nodo
        if ($this->importRol === 'dinamizador' && !empty($this->contextNodoId)) {
            $nodoId = $this->contextNodoId;
        }
        $material['nodo_id'] = $nodoId;

        $lineaId = null;
        if (isset($indices['linea'])) {
            $lineaRaw = $this->cleanCell($campos[$indices['linea']] ?? '');
            if ($lineaRaw === '') {
                $lineaId = $this->contextLineaId;
            } else {
                $lineaId = $this->resolveLineaId($lineaRaw);
            }
        } else {
            $lineaId = $this->contextLineaId;
        }
        $material['linea_id'] = $lineaId;

        $material['estado'] = 1;
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

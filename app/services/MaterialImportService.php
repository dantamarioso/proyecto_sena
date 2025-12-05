<?php

/**
 * Servicio para manejar importaciones de materiales.
 * Extrae la lógica compleja de importación CSV/Excel del controlador.
 */
class MaterialImportService
{
    private $materialModel;

    public function __construct()
    {
        $this->materialModel = new Material();
    }

    /**
     * Importa materiales desde un archivo CSV o Excel.
     */
    public function importFromFile($file, $data)
    {
        $formato = $data['formato'] ?? 'csv';
        
        if ($formato === 'excel') {
            return $this->importFromExcel($file);
        }

        return $this->importFromCSV($file);
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
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file['tmp_name']);
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
        $camposRequeridos = [
            'codigo_sap' => ['codigo sap', 'codigo', 'sap', 'cod_sap'],
            'descripcion' => ['descripcion', 'descripción', 'desc', 'nombre'],
            'cantidad' => ['cantidad', 'qty', 'stock', 'existencia'],
        ];

        $indices = [];

        foreach ($encabezados as $idx => $header) {
            $headerLimpio = strtolower(trim($header));

            foreach ($camposRequeridos as $campo => $variantes) {
                if (in_array($headerLimpio, $variantes)) {
                    $indices[$campo] = $idx;
                }
            }
        }

        // Verificar campos obligatorios
        if (!isset($indices['codigo_sap']) || !isset($indices['descripcion'])) {
            return [
                'success' => false,
                'message' => 'El archivo debe contener al menos las columnas: Código SAP y Descripción',
            ];
        }

        return ['success' => true, 'indices' => $indices];
    }

    /**
     * Procesa las filas del CSV.
     */
    private function procesarFilas($lineas, $delimitador, $indices)
    {
        $insertados = 0;
        $actualizados = 0;
        $errores = [];

        foreach ($lineas as $numeroLinea => $linea) {
            $campos = str_getcsv($linea, $delimitador);

            $material = $this->extraerDatosFila($campos, $indices);

            if (!$material['codigo_sap']) {
                continue; // Saltar filas sin código
            }

            // Insertar o actualizar
            try {
                $existente = $this->materialModel->findByCodigoSAP($material['codigo_sap']);

                if ($existente) {
                    $this->materialModel->update($existente['id'], $material);
                    $actualizados++;
                } else {
                    $this->materialModel->create($material);
                    $insertados++;
                }
            } catch (Exception $e) {
                $errores[] = "Línea {$numeroLinea}: " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'errores' => $errores,
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

        foreach ($filas as $numeroLinea => $campos) {
            $material = $this->extraerDatosFila($campos, $indices);

            if (!$material['codigo_sap']) {
                continue;
            }

            try {
                $existente = $this->materialModel->findByCodigoSAP($material['codigo_sap']);

                if ($existente) {
                    $this->materialModel->update($existente['id'], $material);
                    $actualizados++;
                } else {
                    $this->materialModel->create($material);
                    $insertados++;
                }
            } catch (Exception $e) {
                $errores[] = "Fila {$numeroLinea}: " . $e->getMessage();
            }
        }

        return [
            'success' => true,
            'insertados' => $insertados,
            'actualizados' => $actualizados,
            'errores' => $errores,
        ];
    }

    /**
     * Extrae los datos de una fila.
     */
    private function extraerDatosFila($campos, $indices)
    {
        return [
            'codigo_sap' => trim($campos[$indices['codigo_sap']] ?? ''),
            'descripcion' => trim($campos[$indices['descripcion']] ?? ''),
            'cantidad' => intval($campos[$indices['cantidad']] ?? 0),
            'unidad_medida' => trim($campos[$indices['unidad_medida']] ?? 'UND'),
            'ubicacion' => trim($campos[$indices['ubicacion']] ?? ''),
            'estado' => 'disponible',
        ];
    }
}

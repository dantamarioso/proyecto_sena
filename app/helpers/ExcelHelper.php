<?php

/**
 * Clase helper para generar archivos Excel (XML/CSV) sin dependencias externas
 * Soporta múltiples sheets
 */
class ExcelHelper
{
    private $sheets = []; // Array de sheets: ['nombre' => [...], 'headers' => [...], 'data' => [...]]
    private $currentSheet = null;
    private $useXml = false;

    /**
     * Crear una nueva sheet o cambiar de sheet
     */
    public function createSheet($name = 'Sheet1')
    {
        $this->currentSheet = $name;
        if (!isset($this->sheets[$name])) {
            $this->sheets[$name] = [
                'headers' => [],
                'data' => [],
                'validations' => []
            ];
        }
        return $this;
    }

    /**
     * Establecer encabezados para la sheet actual
     */
    public function setHeaders($headers)
    {
        if ($this->currentSheet === null) {
            $this->createSheet('Sheet1');
        }
        $this->sheets[$this->currentSheet]['headers'] = $headers;
        return $this;
    }

    /**
     * Agregar fila de datos a la sheet actual
     */
    public function addRow($row)
    {
        if ($this->currentSheet === null) {
            $this->createSheet('Sheet1');
        }
        $this->sheets[$this->currentSheet]['data'][] = $row;
        return $this;
    }

    /**
     * Agregar múltiples filas a la sheet actual
     */
    public function addRows($rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }
        return $this;
    }

    /**
     * Agregar validación dropdown a una columna
     */
    public function addValidation($column, $values, $startRow = 2)
    {
        if ($this->currentSheet === null) {
            $this->createSheet('Sheet1');
        }
        
        $this->sheets[$this->currentSheet]['validations'][$column] = [
            'values' => $values,
            'startRow' => $startRow,
            'endRow' => count($this->sheets[$this->currentSheet]['data']) + 1
        ];
        return $this;
    }

    /**
     * Usar formato CSV en lugar de XML
     */
    public function useCSVFormat()
    {
        $this->useXml = false;
        return $this;
    }

    /**
     * Obtener cantidad de sheets creadas
     */
    public function getSheetCount()
    {
        return count($this->sheets);
    }

    /**
     * Obtener el archivo Excel como string binario
     */
    public function generate()
    {
        if (empty($this->sheets)) {
            $this->createSheet('Sheet1');
        }
        
        // Si hay múltiples sheets, usar XML forzosamente
        if (count($this->sheets) > 1) {
            return $this->generateXml();
        }
        
        if ($this->useXml) {
            return $this->generateXml();
        } else {
            return $this->generateCsv();
        }
    }

    /**
     * Generar CSV con BOM para Excel (solo la primera sheet)
     */
    private function generateCsv()
    {
        if (empty($this->sheets)) {
            return '';
        }

        // Usar solo la primera sheet para CSV
        $sheetName = key($this->sheets);
        $sheet = $this->sheets[$sheetName];
        
        $output = fopen('php://memory', 'r+');
        
        // Escribir encabezados
        fputcsv($output, $sheet['headers'], ';');
        
        // Escribir datos
        foreach ($sheet['data'] as $row) {
            fputcsv($output, $row, ';');
        }
        
        // Obtener contenido
        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);
        
        // BOM para que Excel reconozca UTF-8
        return "\xEF\xBB\xBF" . $csv;
    }

    /**
     * Generar XML para Excel (formato .xml que Excel abre como spreadsheet)
     * Soporta múltiples sheets
     */
    private function generateXml()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<?mso-application progid="Excel.Sheet"?>' . "\n";
        $xml .= '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" ' . "\n";
        $xml .= '          xmlns:o="urn:schemas-microsoft-com:office:office" ' . "\n";
        $xml .= '          xmlns:x="urn:schemas-microsoft-com:office:excel" ' . "\n";
        $xml .= '          xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" ' . "\n";
        $xml .= '          xmlns:html="http://www.w3.org/TR/REC-html40">' . "\n";
        
        // Estilos
        $xml .= '<Styles>' . "\n";
        $xml .= '  <Style ss:ID="Default" ss:Name="Normal">' . "\n";
        $xml .= '    <Alignment ss:Vertical="Bottom"/>' . "\n";
        $xml .= '  </Style>' . "\n";
        $xml .= '  <Style ss:ID="Header">' . "\n";
        $xml .= '    <Font ss:Bold="1" ss:Color="#FFFFFF"/>' . "\n";
        $xml .= '    <Interior ss:Color="#4472C4" ss:Pattern="Solid"/>' . "\n";
        $xml .= '    <Alignment ss:Horizontal="Center" ss:Vertical="Center"/>' . "\n";
        $xml .= '  </Style>' . "\n";
        $xml .= '</Styles>' . "\n";
        
        // Múltiples worksheets
        foreach ($this->sheets as $sheetName => $sheet) {
            $xml .= '<Worksheet ss:Name="' . htmlspecialchars($sheetName) . '">' . "\n";
            
            // Anchos de columna
            $columnCount = count($sheet['headers']);
            $xml .= '  <Table ss:ExpandedColumnCount="' . $columnCount . '" ss:ExpandedRowCount="' . (count($sheet['data']) + 1) . '">' . "\n";
            for ($i = 0; $i < $columnCount; $i++) {
                $xml .= '    <Column ss:Width="120"/>' . "\n";
            }
            
            // Encabezados
            $xml .= '    <Row ss:Height="20">' . "\n";
            foreach ($sheet['headers'] as $header) {
                $xml .= '      <Cell ss:StyleID="Header"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>' . "\n";
            }
            $xml .= '    </Row>' . "\n";
            
            // Datos
            foreach ($sheet['data'] as $row) {
                $xml .= '    <Row>' . "\n";
                foreach ($row as $value) {
                    $type = is_numeric($value) && !is_string($value) ? 'Number' : 'String';
                    $xml .= '      <Cell><Data ss:Type="' . $type . '">' . htmlspecialchars($value) . '</Data></Cell>' . "\n";
                }
                $xml .= '    </Row>' . "\n";
            }
            
            $xml .= '  </Table>' . "\n";
            $xml .= '</Worksheet>' . "\n";
        }
        
        $xml .= '</Workbook>' . "\n";
        
        return $xml;
    }
}

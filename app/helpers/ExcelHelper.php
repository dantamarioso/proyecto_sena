<?php

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Clase helper para generar archivos Excel XLSX usando PhpSpreadsheet
 * Soporta múltiples sheets con estilos profesionales.
 */
class ExcelHelper
{
    private $spreadsheet;

    private $sheets = []; // Array de sheets: ['nombre' => [...], 'headers' => [...], 'data' => [...]]

    private $currentSheet = null;

    public function __construct()
    {
        $this->spreadsheet = new Spreadsheet();
        // Eliminar la hoja por defecto
        $this->spreadsheet->removeSheetByIndex(0);
    }

    /**
     * Establecer formatos para columnas específicas.
     * @param array $formats Array asociativo: ['columna' => 'tipo'] donde tipo puede ser 'date', 'currency', 'number'
     */
    public function setColumnFormats($formats)
    {
        if ($this->currentSheet === null) {
            $this->createSheet('Sheet1');
        }
        $this->sheets[$this->currentSheet]['formats'] = $formats;

        return $this;
    }

    /**
     * Crear una nueva sheet o cambiar de sheet.
     */
    public function createSheet($name = 'Sheet1')
    {
        $this->currentSheet = $name;
        if (!isset($this->sheets[$name])) {
            $this->sheets[$name] = [
                'headers' => [],
                'data' => [],
                'validations' => [],
                'formats' => [],
            ];

            // Crear worksheet en PhpSpreadsheet
            $worksheet = $this->spreadsheet->createSheet();
            $worksheet->setTitle($name);
        }

        return $this;
    }

    /**
     * Establecer encabezados para la sheet actual.
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
     * Agregar fila de datos a la sheet actual.
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
     * Agregar múltiples filas a la sheet actual.
     */
    public function addRows($rows)
    {
        foreach ($rows as $row) {
            $this->addRow($row);
        }

        return $this;
    }

    /**
     * Método rápido para agregar una sheet completa con headers y data.
     */
    public function addSheet($name, $headers, $data)
    {
        $this->createSheet($name);
        $this->setHeaders($headers);
        $this->addRows($data);

        return $this;
    }

    /**
     * Descargar el archivo Excel generado.
     */
    public function download($filename = 'export.xlsx')
    {
        $content = $this->generate();

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        echo $content;
        exit;
    }

    /**
     * Agregar validación dropdown a una columna.
     */
    public function addValidation($column, $values, $startRow = 2)
    {
        if ($this->currentSheet === null) {
            $this->createSheet('Sheet1');
        }

        $this->sheets[$this->currentSheet]['validations'][$column] = [
            'values' => $values,
            'startRow' => $startRow,
            'endRow' => count($this->sheets[$this->currentSheet]['data']) + 1,
        ];

        return $this;
    }

    /**
     * Obtener cantidad de sheets creadas.
     */
    public function getSheetCount()
    {
        return count($this->sheets);
    }

    /**
     * Generar archivo Excel XLSX.
     */
    public function generate()
    {
        if (empty($this->sheets)) {
            $this->createSheet('Sheet1');
        }

        // Procesar cada sheet
        $sheetIndex = 0;
        foreach ($this->sheets as $sheetName => $sheetData) {
            $worksheet = $this->spreadsheet->getSheet($sheetIndex);

            // Escribir encabezados
            $colIndex = 1;
            foreach ($sheetData['headers'] as $header) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                $cellCoordinate = $columnLetter . '1';

                $worksheet->setCellValue($cellCoordinate, $header);

                // Estilo del encabezado
                $worksheet->getStyle($cellCoordinate)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 12,
                        'name' => 'Calibri',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '4472C4'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => '2E5C8A'],
                        ],
                    ],
                ]);

                $colIndex++;
            }

            // Ajustar altura de fila de encabezado
            $worksheet->getRowDimension(1)->setRowHeight(30);

            // Escribir datos
            $rowIndex = 2;
            foreach ($sheetData['data'] as $rowData) {
                $colIndex = 1;
                foreach ($rowData as $value) {
                    $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $cellCoordinate = $columnLetter . $rowIndex;

                    // Aplicar formato específico según el tipo de columna
                    $formatType = $sheetData['formats'][$colIndex] ?? null;

                    if ($formatType === 'date' && !empty($value)) {
                        // Convertir fecha a timestamp de Excel
                        try {
                            $dateTime = new DateTime($value);
                            $excelDate = \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel($dateTime);
                            $worksheet->setCellValue($cellCoordinate, $excelDate);
                            $worksheet->getStyle($cellCoordinate)->getNumberFormat()
                                ->setFormatCode('DD/MM/YYYY');
                        } catch (Exception $e) {
                            $worksheet->setCellValue($cellCoordinate, $value);
                        }
                    } elseif ($formatType === 'currency' && !empty($value)) {
                        // Formato de moneda
                        $worksheet->setCellValue($cellCoordinate, floatval($value));
                        $worksheet->getStyle($cellCoordinate)->getNumberFormat()
                            ->setFormatCode('$#,##0.00');
                    } elseif ($formatType === 'number' && !empty($value)) {
                        // Formato numérico
                        $worksheet->setCellValue($cellCoordinate, floatval($value));
                        $worksheet->getStyle($cellCoordinate)->getNumberFormat()
                            ->setFormatCode('#,##0.00');
                    } else {
                        $worksheet->setCellValue($cellCoordinate, $value);
                    }

                    // Estilo de datos con filas alternadas
                    $bgColor = ($rowIndex % 2 == 0) ? 'F2F2F2' : 'FFFFFF';
                    $worksheet->getStyle($cellCoordinate)->applyFromArray([
                        'font' => [
                            'size' => 11,
                            'name' => 'Calibri',
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => ['rgb' => $bgColor],
                        ],
                        'alignment' => [
                            'vertical' => Alignment::VERTICAL_CENTER,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                                'color' => ['rgb' => 'D0D0D0'],
                            ],
                        ],
                    ]);

                    $colIndex++;
                }

                // Ajustar altura de fila
                $worksheet->getRowDimension($rowIndex)->setRowHeight(18);
                $rowIndex++;
            }

            // Ajustar ancho de columnas automáticamente
            foreach (range(1, count($sheetData['headers'])) as $col) {
                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $worksheet->getColumnDimension($columnLetter)->setAutoSize(true);
            }

            // Aplicar validaciones si existen
            if (!empty($sheetData['validations'])) {
                foreach ($sheetData['validations'] as $column => $validationData) {
                    $columnLetter = $column;
                    $startRow = $validationData['startRow'];
                    $endRow = $rowIndex - 1;

                    $validation = $worksheet->getCell("{$columnLetter}{$startRow}")->getDataValidation();
                    $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
                    $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
                    $validation->setAllowBlank(false);
                    $validation->setShowInputMessage(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setShowDropDown(true);
                    $validation->setFormula1('"' . implode(',', $validationData['values']) . '"');

                    // Aplicar a todas las filas
                    for ($row = $startRow; $row <= $endRow; $row++) {
                        $worksheet->getCell("{$columnLetter}{$row}")->setDataValidation(clone $validation);
                    }
                }
            }

            $sheetIndex++;
        }

        // Activar la primera hoja
        $this->spreadsheet->setActiveSheetIndex(0);

        // Generar archivo en memoria
        $writer = new Xlsx($this->spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return $content;
    }
}

<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../vendor/tecnickcom/tcpdf/tcpdf.php';

// Define constantes de TCPDF si no existen
if (!defined('PDF_FONT_MONOSPACED')) {
    define('PDF_FONT_MONOSPACED', 'courier');
}

/**
 * Helper para generar PDFs usando TCPDF
 * Genera un PDF profesional con tabla de materiales.
 */
class PdfHelper
{
    private $headers = [];

    private $data = [];

    private $title = '';

    public function __construct($title = 'Reporte')
    {
        $this->title = $title;
    }

    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    public function addPage()
    {
        // Método placeholder para compatibilidad
        return $this;
    }

    public function addTable($headers, $data)
    {
        $this->setHeaders($headers);
        $this->setData($data);

        return $this;
    }

    public function download($filename = 'reporte.pdf')
    {
        return $this->output($filename);
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;

        return $this;
    }

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function generate()
    {
        return $this->generateTCPDF();
    }

    /**
     * Generar PDF usando TCPDF
     */
    private function generateTCPDF()
    {
        // Crear instancia TCPDF
        /** @noinspection PhpUndefinedClassInspection */
        $pdf = new \TCPDF();
        $pdf->SetCreator('Sistema de Inventario');
        $pdf->SetAuthor('SENA');
        $pdf->SetTitle($this->title);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetAutoPageBreak(TRUE, 10);

        // Agregar página
        $pdf->AddPage('L', 'A4'); // Landscape para mejor visualización de tablas

        // Configurar fuente
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, $this->title, 0, TRUE, 'C');

        $pdf->SetFont('helvetica', '', 8);
        $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i:s'), 0, TRUE, 'C');
        $pdf->Ln(3);

        // Tabla
        $pdf->SetFont('helvetica', 'B', 7);

        // Ancho de columnas automático
        $col_width = (280) / count($this->headers); // 280mm para landscape

        // Encabezados
        $pdf->SetFillColor(68, 114, 196); // Azul
        $pdf->SetTextColor(255, 255, 255); // Blanco
        foreach ($this->headers as $header) {
            $pdf->Cell($col_width, 6, substr($header, 0, 20), 1, 0, 'C', TRUE);
        }
        $pdf->Ln();

        // Datos
        $pdf->SetFillColor(249, 249, 249);
        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('helvetica', '', 6);

        $fill = FALSE;
        foreach ($this->data as $row) {
            foreach ($row as $cell) {
                $pdf->Cell($col_width, 5, substr($cell, 0, 20), 1, 0, 'L', $fill);
            }
            $pdf->Ln();
            $fill = !$fill; // Alternancia de colores
        }

        // Footer
        $pdf->SetFont('helvetica', '', 7);
        $pdf->Ln(5);
        $pdf->Cell(0, 5, 'Este documento fue generado automáticamente por el sistema de inventario.', 0, TRUE, 'C');

        return $pdf->Output('', 'S'); // Retornar como string
    }

    /**
     * Enviar PDF como descarga
     */
    public function output($filename = 'reporte.pdf')
    {
        try {
            $content = $this->generateTCPDF();

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
            header('Content-Length: ' . strlen($content));
            header('Cache-Control: no-cache, no-store, must-revalidate');
            header('Pragma: no-cache');
            header('Expires: 0');

            echo $content;
        } catch (Exception $e) {
            header('Content-Type: text/plain');
            echo 'Error al generar PDF: ' . $e->getMessage();
        }
    }
}

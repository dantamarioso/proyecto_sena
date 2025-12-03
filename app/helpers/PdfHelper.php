<?php

/**
 * Helper simple para generar PDFs sin dependencias externas
 * Genera un PDF básico con tabla de materiales
 */
class PdfHelper
{
    private $headers = [];
    private $data = [];
    private $title = '';
    private $pageWidth = 210; // mm (A4)
    private $pageHeight = 297; // mm (A4)
    private $marginLeft = 10;
    private $marginRight = 10;
    private $marginTop = 15;
    private $marginBottom = 15;

    public function __construct($title = 'Reporte')
    {
        $this->title = $title;
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
        // Crear instancia FPDF simplificada usando generador HTML2PDF via PHP
        // Como no tenemos librerías, usamos una solución alternativa
        return $this->generateHtmlToPdf();
    }

    /**
     * Generar HTML que se puede enviar como PDF usando navegadores o convertir
     * Por ahora retornamos HTML con estilos de impresión
     */
    private function generateHtmlToPdf()
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . htmlspecialchars($this->title) . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            font-size: 10pt; 
            line-height: 1.4;
            padding: 10mm;
            background: white;
        }
        .header { 
            text-align: center; 
            margin-bottom: 20px; 
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h1 { 
            font-size: 18pt; 
            margin-bottom: 5px;
            color: #333;
        }
        .header p { 
            font-size: 9pt; 
            color: #666;
        }
        table { 
            width: 100%; 
            border-collapse: collapse; 
            margin-top: 10px;
        }
        thead { 
            background-color: #4472C4; 
            color: white; 
        }
        th, td { 
            border: 1px solid #999; 
            padding: 6px; 
            text-align: left; 
            font-size: 9pt;
        }
        th { 
            font-weight: bold; 
            text-align: center;
        }
        tbody tr:nth-child(even) { 
            background-color: #f9f9f9; 
        }
        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 8pt;
            color: #999;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
        @media print {
            body { padding: 0; }
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>' . htmlspecialchars($this->title) . '</h1>
        <p>Generado el ' . date('d/m/Y H:i:s') . '</p>
    </div>
    
    <table>
        <thead>
            <tr>';

        foreach ($this->headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }

        $html .= '            </tr>
        </thead>
        <tbody>';

        foreach ($this->data as $row) {
            $html .= '            <tr>';
            foreach ($row as $cell) {
                $html .= '<td>' . htmlspecialchars($cell) . '</td>';
            }
            $html .= '</tr>' . "\n";
        }

        $html .= '        </tbody>
    </table>
    
    <div class="footer">
        <p>Este documento fue generado automáticamente por el sistema de inventario.</p>
    </div>
</body>
</html>';

        return $html;
    }

    /**
     * Convertir a PDF usando wkhtmltopdf si está disponible
     * Si no, retorna HTML para que el navegador lo imprima a PDF
     */
    public function output($filename = 'reporte.pdf')
    {
        $html = $this->generateHtmlToPdf();
        
        // Intentar usar wkhtmltopdf si está disponible
        if (shell_exec('which wkhtmltopdf 2>/dev/null') || file_exists('C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe')) {
            $tmpHtml = tempnam(sys_get_temp_dir(), 'pdf_');
            file_put_contents($tmpHtml, $html);
            
            $tmpPdf = tempnam(sys_get_temp_dir(), 'pdf_');
            
            // Intentar ejecutar wkhtmltopdf
            if (PHP_OS_FAMILY === 'Windows') {
                $cmd = "\"C:\\Program Files\\wkhtmltopdf\\bin\\wkhtmltopdf.exe\" \"$tmpHtml\" \"$tmpPdf\" 2>nul";
            } else {
                $cmd = "wkhtmltopdf \"$tmpHtml\" \"$tmpPdf\" 2>/dev/null";
            }
            
            @shell_exec($cmd);
            
            if (file_exists($tmpPdf) && filesize($tmpPdf) > 100) {
                $content = file_get_contents($tmpPdf);
                unlink($tmpHtml);
                unlink($tmpPdf);
                
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($filename) . '"');
                header('Content-Length: ' . strlen($content));
                header('Cache-Control: no-cache, no-store, must-revalidate');
                header('Pragma: no-cache');
                header('Expires: 0');
                echo $content;
                return;
            }
            
            unlink($tmpHtml);
            if (file_exists($tmpPdf)) unlink($tmpPdf);
        }
        
        // Fallback: enviar como HTML para imprimir/ver
        header('Content-Type: text/html; charset=UTF-8');
        header('Content-Disposition: inline; filename="' . basename($filename) . '.html"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        echo $html;
    }
}

<?php

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

// Cargar autoload de Composer si no está cargado
$autoloadPath = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class MailHelper
{
    public static function sendCode($correo, $asunto, $codigo, $tipo = 'recuperacion')
    {
        try {
            if (!class_exists(PHPMailer::class)) {
                error_log('MailHelper error: PHPMailer no esta disponible. Ejecute "composer install".');
                return false;
            }

            // Instanciar dentro del try para evitar fatales por clases faltantes
            $mail = new PHPMailer(true);

            // Cargar variables de entorno
            $mailHost = EnvHelper::get('MAIL_HOST', 'smtp.gmail.com');
            $mailPort = EnvHelper::get('MAIL_PORT', '587');
            $mailUsername = EnvHelper::get('MAIL_USERNAME', '');
            $mailPassword = EnvHelper::get('MAIL_PASSWORD', '');
            $mailFrom = EnvHelper::get('MAIL_FROM', '');

            $mail->isSMTP();
            $mail->Host = $mailHost;
            $mail->SMTPAuth = true;
            $mail->Username = $mailUsername;
            $mail->Password = $mailPassword;
            $mail->SMTPSecure = 'tls';
            $mail->Port = intval($mailPort);

            $mail->setFrom($mailFrom, 'Sistema Inventario');
            $mail->addAddress($correo);

            // Configurar charset a UTF-8
            $mail->CharSet = 'UTF-8';

            $mail->isHTML(true);
            $mail->Subject = $asunto;

            // Generar HTML profesional
            $htmlBody = self::generarPlantillaHTML($codigo, $tipo);
            $mail->Body = $htmlBody;

            return $mail->send();
        } catch (Exception $e) {
            error_log('MailHelper PHPMailer Exception: ' . $e->getMessage());
            return false;
        } catch (\Throwable $e) {
            error_log('MailHelper Throwable: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generar plantilla HTML profesional para el correo.
     */
    private static function generarPlantillaHTML($codigo, $tipo = 'recuperacion')
    {
        $titulo = $tipo === 'recuperacion' ? 'Recuperación de Contraseña' : 'Verificación de Email';
        $descripcion = $tipo === 'recuperacion'
            ? 'Has solicitado recuperar tu contraseña. Usa el código a continuación para continuar con el proceso.'
            : 'Completa tu registro usando el código de verificación a continuación.';

        return "
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>{$titulo}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Work Sans', system-ui, -apple-system, 'Segoe UI', sans-serif; background-color: #f5f5f5; }
        .container { max-width: 600px; margin: 0 auto; background-color: #ffffff; padding: 40px 20px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { color: #00304D; font-size: 28px; margin-bottom: 10px; }
        .logo { font-size: 24px; font-weight: bold; color: #00304D; }
        .content { text-align: center; }
        .content p { color: #666; font-size: 16px; line-height: 1.6; margin-bottom: 20px; }
        .code-box { 
            background: linear-gradient(135deg, #00304D 0%, #0a58ca 100%);
            border-radius: 8px;
            padding: 30px;
            margin: 30px 0;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.2);
        }
        .code { 
            font-size: 48px; 
            font-weight: bold; 
            color: #fff; 
            letter-spacing: 8px;
            font-family: 'Courier New', monospace;
        }
        .code-label { color: rgba(255, 255, 255, 0.8); font-size: 14px; margin-bottom: 10px; }
        .expiration { 
            background-color: #fff3cd; 
            border-left: 4px solid #ffc107;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
            color: #856404;
            font-size: 14px;
        }
        .footer { 
            margin-top: 40px; 
            padding-top: 20px; 
            border-top: 1px solid #e0e0e0;
            text-align: center;
            color: #999;
            font-size: 12px;
        }
        .footer p { margin: 8px 0; }
        .divider { height: 1px; background-color: #e0e0e0; margin: 20px 0; }
    </style>
</head>
<body>
    <div class='container'>
        <!-- Header -->
        <div class='header'>
            <div class='logo'>🏢 Sistema Inventario</div>
            <h1>{$titulo}</h1>
        </div>

        <!-- Contenido -->
        <div class='content'>
            <p>{$descripcion}</p>

            <div class='code-box'>
                <div class='code-label'>Tu código de verificación:</div>
                <div class='code'>{$codigo}</div>
            </div>

            <div class='expiration'>
                ⏰ <strong>Importante:</strong> Este código expira en 10 minutos. No lo compartas con nadie.
            </div>

            <p>Si no solicitaste esto, puedes ignorar este correo de forma segura.</p>
        </div>

        <div class='divider'></div>

        <!-- Footer -->
        <div class='footer'>
            <p><strong>Sistema de Gestión de Inventario</strong></p>
            <p>© 2025 Todos los derechos reservados.</p>
            <p>Este es un correo automático, por favor no responda a esta dirección.</p>
        </div>
    </div>
</body>
</html>
        ";
    }
}

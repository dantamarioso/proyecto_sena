<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class MailHelper
{
    public static function sendCode($correo, $asunto, $mensaje)
    {
        $mail = new PHPMailer(true);

        try {
            $mail->isSMTP();
            $mail->Host       = "smtp.gmail.com";
            $mail->SMTPAuth   = true;
            $mail->Username   = "dantamarioso@gmail.com";
            $mail->Password   = "xhmr ymwd xjlt mdas";
            $mail->SMTPSecure = "tls";
            $mail->Port       = 587;

            $mail->setFrom("TU_CORREO@gmail.com", "Sistema Inventario");
            $mail->addAddress($correo);

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = nl2br($mensaje);

            return $mail->send();
        } catch (Exception $e) {
            return false;
        }
    }
}

<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

/**
 * Servicio para manejar la verificación de email.
 */
class EmailVerificationService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Verifica el código de email.
     */
    public function verifyEmailCode($correo, $codigo)
    {
        $user = $this->userModel->findByCorreo($correo);

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        $isValid = $this->userModel->verifyCode($user['id'], $codigo);

        if (!$isValid) {
            return ['success' => false, 'message' => 'Código incorrecto o expirado.'];
        }

        // Marcar email como verificado
        $verified = $this->userModel->markEmailAsVerified($user['id']);

        return [
            'success' => $verified,
            'message' => $verified ? 'Email verificado exitosamente.' : 'Error al verificar email.',
            'user' => $user,
        ];
    }

    /**
     * Reenvía el código de verificación de email.
     */
    public function resendVerificationCode($correo)
    {
        $user = $this->userModel->findByCorreo($correo);

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        // Verificar si ya está verificado
        if ($user['email_verified'] == 1) {
            return ['success' => false, 'message' => 'Este email ya ha sido verificado.'];
        }

        // Verificar cooldown
        if (!$this->userModel->canResendVerificationCode($user['id'])) {
            return [
                'success' => false,
                'message' => 'Debes esperar 60 segundos antes de reenviar el código.',
                'cooldown' => true,
            ];
        }

        $code = $this->generateCode();
        $this->userModel->saveRecoveryCode($user['id'], $code);

        $sent = MailHelper::sendCode(
            $correo,
            'Verificación de Email - Sistema Inventario',
            $code,
            'verificacion'
        );

        return [
            'success' => $sent,
            'message' => $sent ? 'Código reenviado exitosamente al correo.' : 'Error al enviar el código',
        ];
    }

    /**
     * Genera un código aleatorio de 6 dígitos.
     */
    private function generateCode()
    {
        return rand(100000, 999999);
    }
}

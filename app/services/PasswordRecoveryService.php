<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

/**
 * Servicio para manejar la lógica de recuperación de contraseña.
 */
class PasswordRecoveryService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Inicia el proceso de recuperación de contraseña.
     */
    public function initiateRecovery($correo)
    {
        $user = $this->userModel->findByCorreo($correo);

        if (!$user) {
            return ['success' => false, 'message' => 'No existe un usuario con ese correo.'];
        }

        $code = $this->generateCode();
        $this->userModel->saveRecoveryCode($user['id'], $code);

        $sent = MailHelper::sendCode(
            $correo,
            'Código de recuperación - Sistema Inventario',
            $code,
            'recuperacion'
        );

        return [
            'success' => $sent,
            'message' => $sent ? 'Código enviado exitosamente' : 'Error al enviar el código',
            'user' => $user,
        ];
    }

    /**
     * Reenvía el código de recuperación.
     */
    public function resendRecoveryCode($correo)
    {
        $user = $this->userModel->findByCorreo($correo);

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        // Verificar cooldown usando el método específico para recuperación
        if (!$this->userModel->canResendRecoveryCode($user['id'])) {
            $remaining = $this->userModel->getRemainingRecoveryCooldownTime($user['id']);
            return [
                'success' => false,
                'message' => "Debes esperar {$remaining} segundos antes de reenviar el código.",
                'cooldown' => true,
                'remaining' => $remaining,
            ];
        }

        $code = $this->generateCode();
        $this->userModel->saveRecoveryCode($user['id'], $code);

        $sent = MailHelper::sendCode(
            $correo,
            'Código de recuperación - Sistema Inventario',
            $code,
            'recuperacion'
        );

        return [
            'success' => $sent,
            'message' => $sent ? 'Código reenviado exitosamente al correo.' : 'Error al enviar el código',
        ];
    }

    /**
     * Verifica el código de recuperación.
     */
    public function verifyRecoveryCode($correo, $codigo)
    {
        $user = $this->userModel->findByCorreo($correo);

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        $isValid = $this->userModel->verifyCode($user['id'], $codigo);

        if (!$isValid) {
            return ['success' => false, 'message' => 'Código incorrecto o expirado.'];
        }

        return ['success' => true, 'user' => $user];
    }

    /**
     * Restablece la contraseña del usuario.
     */
    public function resetPassword($correo, $newPassword)
    {
        $user = $this->userModel->findByCorreo($correo);

        if (!$user) {
            return ['success' => false, 'message' => 'Usuario no encontrado.'];
        }

        // Validar contraseña
        if (strlen($newPassword) < 8) {
            return ['success' => false, 'message' => 'La contraseña debe tener al menos 8 caracteres.'];
        }
        if (!preg_match('/[A-Z]/', $newPassword)) {
            return ['success' => false, 'message' => 'La contraseña debe contener al menos una mayúscula.'];
        }
        if (!preg_match('/[@$!%*?&#]/', $newPassword)) {
            return ['success' => false, 'message' => 'La contraseña debe contener al menos un carácter especial.'];
        }

        $updated = $this->userModel->setNewPassword($user['id'], $newPassword);

        if ($updated) {
            // Limpiar código de recuperación
            $this->userModel->clearRecoveryCode($user['id']);
        }

        return [
            'success' => $updated,
            'message' => $updated ? 'Contraseña actualizada exitosamente.' : 'Error al actualizar la contraseña.',
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

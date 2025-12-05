<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Model.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../helpers/MailHelper.php';

/**
 * Servicio para manejar la autenticación de usuarios.
 */
class AuthenticationService
{
    private $userModel;

    public function __construct()
    {
        $this->userModel = new User();
    }

    /**
     * Autentica un usuario con sus credenciales.
     */
    public function authenticate($correo, $password)
    {
        // Buscar usuario por correo
        $user = $this->userModel->findByCorreo($correo);

        if (!$user) {
            return ['success' => false, 'message' => 'Correo o contraseña incorrectos.'];
        }

        // Verificar contraseña
        if (!password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Correo o contraseña incorrectos.'];
        }

        // Verificar estado
        if ($user['estado'] != 1) {
            return ['success' => false, 'message' => 'Tu cuenta está inactiva. Contacta al administrador.'];
        }

        // Verificar email
        if ($user['email_verified'] != 1) {
            return [
                'success' => false,
                'message' => 'Debes verificar tu correo electrónico antes de iniciar sesión.',
                'email_not_verified' => true,
                'correo' => $user['correo'],
            ];
        }

        return ['success' => true, 'user' => $user];
    }

    /**
     * Registra un nuevo usuario.
     */
    public function register($data)
    {
        // Validar datos
        $validation = $this->validateRegistration($data);
        if (!$validation['valid']) {
            return ['success' => false, 'errors' => $validation['errors']];
        }

        // Verificar si ya existe
        if ($this->userModel->findByCorreo($data['correo'])) {
            return ['success' => false, 'errors' => ['El correo ya está registrado.']];
        }

        if ($this->userModel->findByUsuario($data['usuario'])) {
            return ['success' => false, 'errors' => ['El nombre de usuario ya está en uso.']];
        }

        // Hash de contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Subir foto si existe
        $fotoPath = null;
        if (isset($data['foto']) && $data['foto']['error'] === UPLOAD_ERR_OK) {
            $fotoPath = $this->uploadPhoto($data['foto']);
            if (!$fotoPath) {
                return ['success' => false, 'errors' => ['Error al subir la foto.']];
            }
        }

        // Crear usuario
        $userId = $this->userModel->create([
            'nombre' => $data['nombre'],
            'correo' => $data['correo'],
            'usuario' => $data['usuario'],
            'password' => $hashedPassword,
            'celular' => $data['celular'] ?? null,
            'cargo' => $data['cargo'] ?? null,
            'foto' => $fotoPath,
            'rol' => 'usuario',
            'estado' => 1,
            'email_verified' => 0,
        ]);

        if (!$userId) {
            return ['success' => false, 'errors' => ['Error al crear el usuario.']];
        }

        // Enviar código de verificación
        $code = rand(100000, 999999);
        $this->userModel->saveRecoveryCode($userId, $code);

        MailHelper::sendCode(
            $data['correo'],
            'Verificación de Email - Sistema Inventario',
            $code,
            'verificacion'
        );

        return [
            'success' => true,
            'message' => 'Usuario registrado exitosamente. Verifica tu correo.',
            'user_id' => $userId,
            'correo' => $data['correo'],
        ];
    }

    /**
     * Valida los datos de registro.
     */
    private function validateRegistration($data)
    {
        $errors = [];

        // Nombre
        if (empty($data['nombre'])) {
            $errors[] = 'El nombre es obligatorio.';
        }

        // Correo
        if (empty($data['correo']) || !filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'El correo no es válido.';
        }

        // Usuario
        if (empty($data['usuario']) || strlen($data['usuario']) < 3) {
            $errors[] = 'El usuario debe tener al menos 3 caracteres.';
        }

        // Contraseña
        if (empty($data['password']) || strlen($data['password']) < 8) {
            $errors[] = 'La contraseña debe tener al menos 8 caracteres.';
        } elseif (!preg_match('/[A-Z]/', $data['password'])) {
            $errors[] = 'La contraseña debe contener al menos una mayúscula.';
        } elseif (!preg_match('/[@$!%*?&#]/', $data['password'])) {
            $errors[] = 'La contraseña debe contener al menos un carácter especial.';
        }

        // Confirmar contraseña
        if (empty($data['password_confirm']) || $data['password'] !== $data['password_confirm']) {
            $errors[] = 'Las contraseñas no coinciden.';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    /**
     * Sube una foto de perfil.
     */
    private function uploadPhoto($foto)
    {
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        $extension = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $allowedExtensions)) {
            return false;
        }

        $uploadDir = __DIR__ . '/../../public/uploads/fotos/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $filename = uniqid() . '.' . $extension;
        $filepath = $uploadDir . $filename;

        if (move_uploaded_file($foto['tmp_name'], $filepath)) {
            return 'uploads/fotos/' . $filename;
        }

        return false;
    }

    /**
     * Crea la sesión del usuario.
     */
    public function createSession($user)
    {
        session_regenerate_id(true);

        $_SESSION['user'] = [
            'id' => $user['id'],
            'nombre' => $user['nombre'],
            'usuario' => $user['usuario'],
            'correo' => $user['correo'],
            'cargo' => $user['cargo'],
            'foto' => $user['foto'],
            'rol' => $user['rol'],
        ];
    }

    /**
     * Destruye la sesión del usuario.
     */
    public function destroySession()
    {
        session_destroy();
        session_start();
        session_regenerate_id(true);
    }
}

<?php
class AuthController extends Controller
{

    /* ============================================================
       RECOVERY - OLVIDAR CONTRASEÑA
    ============================================================ */

    public function forgot()
    {
        $this->view("auth/forgot", [
            'isLoginPage' => true,
            'isRecoveryPage' => true,
            'pageStyles'  => ['login', 'recovery'],
            'pageScripts' => ['recovery']
        ]);
    }

    public function sendCode()
    {
        $correo = trim($_POST['correo'] ?? '');
        $userModel = new User();
        $user = $userModel->findByCorreo($correo);

        if (!$user) {
            $_SESSION['flash_error'] = "No existe un usuario con ese correo.";
            $this->redirect("auth/forgot");
        }

        $code = rand(100000, 999999);
        $userModel->saveRecoveryCode($user['id'], $code);

        MailHelper::sendCode(
            $correo,
            "Código de recuperación - Sistema Inventario",
            $code,
            'recuperacion'
        );

        $_SESSION['recovery_correo'] = $correo;
        $this->redirect("auth/verifyCode");
    }

    public function resendCode()
    {
        $correo = $_SESSION['recovery_correo'] ?? null;
        
        if (!$correo) {
            $this->redirect("auth/forgot");
        }

        $userModel = new User();
        $user = $userModel->findByCorreo($correo);

        if (!$user) {
            $_SESSION['flash_error'] = "Usuario no encontrado.";
            $this->redirect("auth/forgot");
        }

        // Verificar si puede reenviar (90 segundos de cooldown)
        if (!$userModel->canResendVerificationCode($user['id'])) {
            $_SESSION['flash_error'] = "Debes esperar 90 segundos antes de reenviar el código.";
            $this->redirect("auth/verifyCode");
        }

        $code = rand(100000, 999999);
        $userModel->saveRecoveryCode($user['id'], $code);

        MailHelper::sendCode(
            $correo,
            "Código de recuperación - Sistema Inventario",
            $code,
            'recuperacion'
        );

        $_SESSION['flash_success'] = "Código reenviado correctamente.";
        $this->redirect("auth/verifyCode");
    }

    public function verifyCode()
    {
        if (!isset($_SESSION['recovery_correo'])) {
            $this->redirect("auth/forgot");
        }

        $userModel = new User();
        $user = $userModel->findByCorreo($_SESSION['recovery_correo']);
        $remainingCooldown = 0;

        if ($user) {
            $remainingCooldown = $userModel->getRemainingCooldownTime($user['id']);
        }

        $this->view("auth/verifyCode", [
            'isLoginPage' => true,
            'isRecoveryPage' => true,
            'pageStyles'  => ['login', 'recovery'],
            'pageScripts' => ['recovery'],
            'remainingCooldown' => $remainingCooldown
        ]);
    }

    public function verifyCodePost()
    {
        $correo = $_SESSION['recovery_correo'] ?? null;
        $code   = trim($_POST['code'] ?? '');

        $userModel = new User();
        $user = $userModel->verifyCode($correo, $code);

        if ($user) {
            $_SESSION['reset_user'] = $user['id'];
            $this->redirect("auth/resetPassword");
        } else {
            $_SESSION['flash_error'] = "Código incorrecto o expirado.";
            $this->redirect("auth/verifyCode");
        }
    }

    public function resetPassword()
    {
        if (!isset($_SESSION['reset_user'])) {
            $this->redirect("auth/login");
        }

        $this->view("auth/reset", [
            'isLoginPage' => true,
            'isRecoveryPage' => true,
            'pageStyles'  => ['login', 'recovery'],
            'pageScripts' => ['recovery']
        ]);
    }

    public function resetPasswordPost()
    {
        $id   = $_SESSION['reset_user'];
        $pass = $_POST['password'];
        $pass2 = $_POST['password2'];

        if ($pass !== $pass2) {
            $_SESSION['flash_error'] = "Las contraseñas no coinciden.";
            $this->redirect("auth/resetPassword");
        }

        $userModel = new User();
        $userModel->setNewPassword($id, $pass);
        $userModel->clearRecoveryCode($id);

        unset($_SESSION['reset_user']);
        unset($_SESSION['recovery_correo']);

        $_SESSION['flash_success'] = "Contraseña actualizada.";
        $this->redirect("auth/login");
    }


    /* ============================================================
       LOGIN
    ============================================================ */
    public function login()
    {
        if (isset($_SESSION['user'])) {
            $this->redirect('home/index');
            exit;
        }

        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $login    = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($login === '' || $password === '') {
                $errores[] = "Ingresa correo o nombre de usuario y la contraseña.";
            } else {

                $userModel = new User();
                $user = $userModel->findByCorreoOrUsername($login);

                if ($user && password_verify($password, $user['password'])) {

                    // Verificar si el usuario está activo
                    if ($user['estado'] == 0) {
                        $errores[] = "Tu cuenta ha sido desactivada. Contacta al administrador.";
                    } else {
                        session_regenerate_id(true);

                        $_SESSION['user'] = [
                            'id'     => $user['id'],
                            'nombre' => $user['nombre'],
                            'cargo'  => $user['cargo'],
                            'foto'   => $user['foto'],
                            'rol'    => $user['rol'] ?? 'usuario',
                        ];

                        $this->redirectReplace('home/index');
                        exit;
                    }
                } else {
                    $errores[] = "Credenciales inválidas.";
                }
            }
        }

        $this->view('auth/login', [
            'errores'     => $errores,
            'pageStyles'  => ['login'],
            'pageScripts' => ['login'],
            'isLoginPage' => true
        ]);
    }


    /* ============================================================
       REGISTER - CON VERIFICACIÓN DE EMAIL
    ============================================================ */
    public function register()
    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre_completo = trim($_POST['nombre_completo'] ?? '');
            $correo          = trim($_POST['correo'] ?? '');
            $password        = $_POST['password'] ?? '';
            $password2       = $_POST['password2'] ?? '';
            $terminos        = isset($_POST['terminos']);

            if ($nombre_completo === '' || $correo === '' || $password === '' || $password2 === '') {
                $errores[] = "Todos los campos son obligatorios.";
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El correo no es válido.";
            }

            if ($password !== $password2) {
                $errores[] = "Las contraseñas no coinciden.";
            }

            if (!$terminos) {
                $errores[] = "Debes aceptar los términos y condiciones.";
            }

            $hasLength  = strlen($password) >= 8;
            $hasUpper   = preg_match('/[A-Z]/', $password);
            $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>_\-]/', $password);

            if (!$hasLength || !$hasUpper || !$hasSpecial) {
                $errores[] = "La contraseña no cumple los requisitos mínimos.";
            }

            $userModel = new User();

            if ($userModel->existsByCorreo($correo)) {
                $errores[] = "Ya existe un usuario registrado con ese correo.";
            }

            if (empty($errores)) {

                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Crear usuario con email sin verificar
                $userModel->create([
                    'nombre'         => $nombre_completo,
                    'correo'         => $correo,
                    'nombre_usuario' => $correo,
                    'password'       => $hash,
                    'estado'         => 1,
                    'rol'            => 'usuario',
                ]);

                // Obtener el ID del usuario creado
                $user = $userModel->findByCorreo($correo);
                $verificationCode = rand(100000, 999999);
                $userModel->saveVerificationCode($user['id'], $verificationCode);

                // Enviar código de verificación
                MailHelper::sendCode(
                    $correo,
                    "Código de verificación de email - Sistema Inventario",
                    $verificationCode,
                    'verificacion'
                );

                $_SESSION['register_correo'] = $correo;
                $this->redirect('auth/verifyEmail');
                exit;
            }
        }

        $this->view('auth/register', [
            'errores'        => $errores,
            'pageStyles'     => ['register'],
            'pageScripts'    => ['register'],
            'isRegisterPage' => true
        ]);
    }

    public function verifyEmail()
    {
        if (!isset($_SESSION['register_correo'])) {
            $this->redirect("auth/register");
        }

        $userModel = new User();
        $user = $userModel->findByCorreo($_SESSION['register_correo']);
        $remainingCooldown = 0;

        if ($user) {
            $remainingCooldown = $userModel->getRemainingCooldownTime($user['id']);
        }

        $this->view("auth/verifyEmail", [
            'isLoginPage' => true,
            'pageStyles'  => ['login', 'recovery'],
            'pageScripts' => ['recovery'],
            'remainingCooldown' => $remainingCooldown
        ]);
    }

    public function verifyEmailPost()
    {
        $correo = $_SESSION['register_correo'] ?? null;
        $code   = trim($_POST['code'] ?? '');

        $userModel = new User();
        $user = $userModel->verifyEmailCode($correo, $code);

        if ($user) {
            $userModel->markEmailAsVerified($user['id']);
            
            unset($_SESSION['register_correo']);

            $_SESSION['flash_success'] = "Email verificado correctamente. ¡Ahora puedes iniciar sesión!";
            $this->redirect("auth/login");
        } else {
            $_SESSION['flash_error'] = "Código incorrecto o expirado.";
            $this->redirect("auth/verifyEmail");
        }
    }

    public function resendVerificationEmail()
    {
        $correo = $_SESSION['register_correo'] ?? null;
        
        if (!$correo) {
            $this->redirect("auth/register");
        }

        $userModel = new User();
        $user = $userModel->findByCorreo($correo);

        if (!$user) {
            $_SESSION['flash_error'] = "Usuario no encontrado.";
            $this->redirect("auth/register");
        }

        if (!$userModel->canResendVerificationCode($user['id'])) {
            $_SESSION['flash_error'] = "Espera 90 segundos antes de reenviar.";
            $this->redirect("auth/verifyEmail");
        }

        $verificationCode = rand(100000, 999999);
        $userModel->saveVerificationCode($user['id'], $verificationCode);

        MailHelper::sendCode(
            $correo,
            "Código de verificación de email - Sistema Inventario",
            $verificationCode,
            'verificacion'
        );

        $_SESSION['flash_success'] = "Código reenviado al correo.";
        $this->redirect("auth/verifyEmail");
    }


    /* ============================================================
       LOGOUT
    ============================================================ */
    public function logout()
    {
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        $this->redirect('auth/login');
    }
}

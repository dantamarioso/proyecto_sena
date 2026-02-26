<?php

require_once __DIR__ . '/../core/Database.php';
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../services/AuthenticationService.php';
require_once __DIR__ . '/../services/PasswordRecoveryService.php';
require_once __DIR__ . '/../services/EmailVerificationService.php';

/**
 * Controlador de autenticación refactorizado.
 * Usa servicios para reducir complejidad ciclomática.
 */
class AuthController extends Controller
{
    private $authService;

    private $recoveryService;

    private $verificationService;

    public function __construct()
    {
        $this->authService = new AuthenticationService();
        $this->recoveryService = new PasswordRecoveryService();
        $this->verificationService = new EmailVerificationService();
    }

    /* ============================================================
       RECOVERY - OLVIDAR CONTRASEÑA
    ============================================================ */

    public function forgot()
    {
        $this->view('auth/forgot', [
            'isLoginPage' => true,
            'isRecoveryPage' => true,
            'pageStyles' => ['login', 'recovery'],
            'pageScripts' => [],
        ]);
    }

    public function sendCode()
    {
        $correo = trim($_POST['correo'] ?? '');
        $result = $this->recoveryService->initiateRecovery($correo);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            $this->redirect('auth/forgot');
        }

        $_SESSION['recovery_correo'] = $correo;
        $this->redirect('auth/verifyCode');
    }

    public function resendCode()
    {
        $isAjax = $this->isAjaxRequest();
        $correo = $_SESSION['recovery_correo'] ?? null;

        if (!$correo) {
            return $this->handleResendError($isAjax, 'Sesión expirada. Por favor, comienza de nuevo.', 400, 'auth/forgot');
        }

        $result = $this->recoveryService->resendRecoveryCode($correo);

        if (!$result['success']) {
            $statusCode = isset($result['cooldown']) && $result['cooldown'] ? 429 : 400;

            return $this->handleResendError($isAjax, $result['message'], $statusCode, 'auth/verifyCode');
        }

        if ($isAjax) {
            $this->jsonResponse(['success' => true, 'message' => $result['message']], 200);
        }

        $_SESSION['flash_success'] = 'Código reenviado correctamente.';
        $this->redirect('auth/verifyCode');
    }

    public function verifyCode()
    {
        if (!isset($_SESSION['recovery_correo'])) {
            $this->redirect('auth/forgot');
        }

        $userModel = new User();
        $user = $userModel->findByCorreo($_SESSION['recovery_correo']);
        $remainingCooldown = $user ? $userModel->getRemainingRecoveryCooldownTime($user['id']) : 0;

        $this->view('auth/verifyCode', [
            'isLoginPage' => true,
            'isRecoveryPage' => true,
            'pageStyles' => ['login', 'recovery'],
            'pageScripts' => [],
            'remainingCooldown' => $remainingCooldown,
        ]);
    }

    public function verifyCodePost()
    {
        $correo = $_SESSION['recovery_correo'] ?? null;
        $code = trim($_POST['code'] ?? '');

        $result = $this->recoveryService->verifyRecoveryCode($correo, $code);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            $this->redirect('auth/verifyCode');
        }

        $_SESSION['reset_user'] = $result['user']['id'];
        $this->redirect('auth/resetPassword');
    }

    public function resetPassword()
    {
        if (!isset($_SESSION['recovery_correo'])) {
            $this->redirect('auth/forgot');
        }

        $this->view('auth/reset', [
            'isLoginPage' => true,
            'isRecoveryPage' => true,
            'pageStyles' => ['login', 'recovery'],
            'pageScripts' => [],
        ]);
    }

    public function resetPasswordPost()
    {
        $password = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';
        $correo = $_SESSION['recovery_correo'] ?? null;

        if ($password !== $password2) {
            $_SESSION['flash_error'] = 'Las contraseñas no coinciden.';
            $this->redirect('auth/resetPassword');
        }

        $result = $this->recoveryService->resetPassword($correo, $password);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            $this->redirect('auth/resetPassword');
        }

        unset($_SESSION['reset_user'], $_SESSION['recovery_correo']);

        $_SESSION['flash_success'] = 'Contraseña actualizada correctamente. ¡Ya puedes iniciar sesión!';
        $this->redirect('auth/login');
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
            $login = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($login === '' || $password === '') {
                $errores[] = 'Ingresa tu correo electrónico y contraseña.';
            } else {
                $result = $this->authService->authenticate($login, $password);

                if (!$result['success']) {
                    if (isset($result['email_not_verified']) && $result['email_not_verified']) {
                        $_SESSION['register_correo'] = $result['correo'];
                        $_SESSION['flash_error'] = $result['message'];
                        $this->redirect('auth/verifyEmail');
                        exit;
                    }
                    $errores[] = $result['message'];
                } else {
                    $user = $result['user'];

                    // Verificar asignaciones según rol
                    if (!$this->validateUserAssignments($user, $errores)) {
                        // Errores ya agregados
                    } else {
                        $this->authService->createSession($user);
                        $this->redirectReplace('home/index');
                        exit;
                    }
                }
            }
        }

        $this->view('auth/login', [
            'errores' => $errores,
            'pageStyles' => ['login'],
            'pageScripts' => ['login'],
            'isLoginPage' => true,
        ]);
    }

    /* ============================================================
       REGISTER - CON VERIFICACIÓN DE EMAIL
    ============================================================ */
    public function register()
    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'nombre' => trim($_POST['nombre_completo'] ?? ''),
                'correo' => trim($_POST['correo'] ?? ''),
                'usuario' => trim($_POST['correo'] ?? ''),
                'password' => $_POST['password'] ?? '',
                'password_confirm' => $_POST['password2'] ?? '',
                'terminos' => isset($_POST['terminos']),
            ];

            if (!$data['terminos']) {
                $errores[] = 'Debes aceptar los términos y condiciones.';
            }

            if (empty($errores)) {
                $result = $this->authService->register($data);

                if (!$result['success']) {
                    $errores = $result['errors'];
                } else {
                    // Registrar en auditoría
                    $this->registerAudit($result['user_id'], $data);

                    $_SESSION['register_correo'] = $result['correo'];
                    $this->redirect('auth/verifyEmail');
                    exit;
                }
            }
        }

        $this->view('auth/register', [
            'errores' => $errores,
            'pageStyles' => ['register'],
            'pageScripts' => ['register'],
            'isRegisterPage' => true,
        ]);
    }

    public function terminos()
    {
        $this->view('auth/terminos', [
            'isRegisterPage' => true,
            'pageStyles' => ['terminos'],
            'pageScripts' => [],
        ]);
    }

    public function verifyEmail()
    {
        if (!isset($_SESSION['register_correo'])) {
            $this->redirect('auth/register');
        }

        $userModel = new User();
        $user = $userModel->findByCorreo($_SESSION['register_correo']);
        $remainingCooldown = $user ? $userModel->getRemainingCooldownTime($user['id']) : 0;

        $this->view('auth/verifyEmail', [
            'isLoginPage' => true,
            'pageStyles' => ['login', 'recovery'],
            'pageScripts' => [],
            'remainingCooldown' => $remainingCooldown,
        ]);
    }

    public function verifyEmailPost()
    {
        $correo = $_SESSION['register_correo'] ?? null;
        $code = trim($_POST['code'] ?? '');

        $result = $this->verificationService->verifyEmailCode($correo, $code);

        if (!$result['success']) {
            $_SESSION['flash_error'] = $result['message'];
            $this->redirect('auth/verifyEmail');
        }

        unset($_SESSION['register_correo']);

        $_SESSION['flash_success'] = 'Email verificado correctamente. Tu cuenta está pendiente de activación por un administrador.';
        $this->redirect('auth/login');
    }

    public function resendVerificationEmail()
    {
        $isAjax = $this->isAjaxRequest();

        if ($isAjax) {
            header('Content-Type: application/json; charset=utf-8');

            $correo = $_SESSION['register_correo'] ?? null;

            if (!$correo) {
                $this->jsonResponse(['success' => false, 'message' => 'Correo no encontrado en sesión.'], 400);
            }

            $result = $this->verificationService->resendVerificationCode($correo);

            $statusCode = !$result['success'] && isset($result['cooldown']) ? 429 : ($result['success'] ? 200 : 400);
            $this->jsonResponse($result, $statusCode);
        }

        $this->redirect('auth/verifyEmail');
    }

    /* ============================================================
       LOGOUT
    ============================================================ */
    public function logout()
    {
        $this->authService->destroySession();
        $this->redirect('auth/login');
    }

    /* ============================================================
       MÉTODOS AUXILIARES PRIVADOS
    ============================================================ */

    private function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
               $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    private function jsonResponse($data, $statusCode = 200)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    private function handleResendError($isAjax, $message, $statusCode, $redirectUrl)
    {
        if ($isAjax) {
            $this->jsonResponse(['success' => false, 'message' => $message], $statusCode);
        }

        $_SESSION['flash_error'] = $message;
        $this->redirect($redirectUrl);
    }

    private function validateUserAssignments($user, &$errores)
    {
        // Admin puede iniciar sesión sin nodo/línea
        if ($user['rol'] === 'admin') {
            return true;
        }

        // Verificar nodo
        if (empty($user['nodo_id'])) {
            $errores[] = 'Tu cuenta está pendiente de activación por un administrador. Se te asignará un nodo pronto.';

            return false;
        }

        // Solo usuarios normales requieren línea (dinamizadores no)
        if ($user['rol'] === 'usuario' && empty($user['linea_id'])) {
            $errores[] = 'Tu cuenta está pendiente de activación por un administrador. Se te asignará una línea pronto.';

            return false;
        }

        return true;
    }

    private function registerAudit($userId, $data)
    {
        $auditModel = new Audit();
        $auditModel->registrarCambio(
            $userId,
            'usuarios',
            $userId,
            'crear',
            [
                'nombre' => ['anterior' => 'N/A', 'nuevo' => $data['nombre']],
                'correo' => ['anterior' => 'N/A', 'nuevo' => $data['correo']],
                'nombre_usuario' => ['anterior' => 'N/A', 'nuevo' => $data['usuario']],
                'rol' => ['anterior' => 'N/A', 'nuevo' => 'usuario'],
                'estado' => ['anterior' => 'N/A', 'nuevo' => 'Activo'],
                'email_verificado' => ['anterior' => 'N/A', 'nuevo' => 'Pendiente'],
            ],
            null
        );
    }
}

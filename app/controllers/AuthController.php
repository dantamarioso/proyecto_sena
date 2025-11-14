<?php
class AuthController extends Controller
{

    /* ==================================================
       LOGIN
    ================================================== */
    public function login()
    {
        // Si ya está logueado → enviar al home
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

                    // Regenerar id de sesión para evitar session fixation
                    session_regenerate_id(true);

                    $_SESSION['user'] = [
                        'id'     => $user['id'],
                        'nombre' => $user['nombre'],
                        'cargo'  => $user['cargo'],
                        'foto'   => $user['foto'],
                        'rol'    => $user['rol'] ?? 'usuario',
                    ];

                    // Redirigir reemplazando la entrada de historial (evita volver al login con Atrás)
                    $this->redirectReplace('home/index');
                    exit;
                } else {
                    $errores[] = "Credenciales inválidas.";
                }
            }
        }

        // Cargar vista login
        $this->view('auth/login', [
            'errores'     => $errores,
            'pageStyles'  => ['login'],
            'pageScripts' => ['login'],
            'isLoginPage' => true
        ]);
    }


    /* ==================================================
       REGISTER
    ================================================== */
    public function register()
    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre_completo = trim($_POST['nombre_completo'] ?? '');
            $correo          = trim($_POST['correo'] ?? '');
            $password        = $_POST['password'] ?? '';
            $password2       = $_POST['password2'] ?? '';
            $terminos        = isset($_POST['terminos']);

            /* ------------------------------
               VALIDACIONES
            ------------------------------ */
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

            // Validación de checklist
            $hasLength  = strlen($password) >= 8;
            $hasUpper   = preg_match('/[A-Z]/', $password);
            $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>_\-]/', $password);

            if (!$hasLength || !$hasUpper || !$hasSpecial) {
                $errores[] = "La contraseña no cumple los requisitos mínimos.";
            }

            $userModel = new User();

            // Verificar correo ya registrado
            if ($userModel->existsByCorreo($correo)) {
                $errores[] = "Ya existe un usuario registrado con ese correo.";
            }

            /* ------------------------------
               SI TODO OK → REGISTRAR
            ------------------------------ */
            if (empty($errores)) {

                // Puedes separar nombre / apellido si quieres,
                // pero tu BD usa nombre único así que lo dejamos así.
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $userModel->create([
                    'nombre'         => $nombre_completo,
                    'correo'         => $correo,
                    'nombre_usuario' => $correo,
                    'celular'        => null,
                    'cargo'          => null,
                    'foto'           => null,
                    'password'       => $hash,
                    'estado'         => 1,
                    'rol'            => 'usuario',
                ]);

                $_SESSION['flash_success'] = "Registro exitoso. ¡Ahora puedes iniciar sesión!";
                $this->redirect('auth/login');
                exit;
            }
        }

        // Cargar vista register
        $this->view('auth/register', [
            'errores'        => $errores,
            'pageStyles'     => ['register'],
            'pageScripts'    => ['register'],
            'isRegisterPage' => true
        ]);
    }


    /* ==================================================
       LOGOUT
    ================================================== */
    public function logout()
    {
        // Limpia la sesión de forma segura (vacía array, borra cookie y destruye la sesión)
        $_SESSION = [];

        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();

        // Redirige al login
        $this->redirect('auth/login');
    }
}

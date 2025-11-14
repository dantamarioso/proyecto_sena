<?php
class AuthController extends Controller
{

    public function login()

    {
    // 🔥 Si ya está logueado, no mostrar login
    if (isset($_SESSION['user'])) {
        $this->redirect('home/index');
        exit;
    }

    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Puede ser correo o nombre de usuario
            $login    = trim($_POST['login'] ?? '');
            $password = $_POST['password'] ?? '';

            if ($login === '' || $password === '') {
                $errores[] = "Ingresa correo o nombre de usuario y la contraseña.";
            } else {
                $userModel = new User();
                $user = $userModel->findByCorreoOrUsername($login);

                if ($user && password_verify($password, $user['password'])) {
                    $_SESSION['user'] = [
                        'id'       => $user['id'],
                        'nombre'   => $user['nombre'],
                        'cargo'    => $user['cargo']    ?? null,
                        'foto'     => $user['foto']     ?? null,
                    ];

                    $this->redirect('home/index');
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
    }

    public function register()
    {
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre_completo = trim($_POST['nombre_completo'] ?? '');
            $correo          = trim($_POST['correo'] ?? '');
            $password        = $_POST['password']  ?? '';
            $password2       = $_POST['password2'] ?? '';
            $terminos        = isset($_POST['terminos']);

            // ---------- Validaciones básicas ----------
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

            // Reglas de contraseña (mismas que el checklist)
            $hasLength  = strlen($password) >= 8;
            $hasUpper   = preg_match('/[A-Z]/', $password);
            $hasSpecial = preg_match('/[!@#$%^&*(),.?":{}|<>_\-]/', $password);

            if (!$hasLength || !$hasUpper || !$hasSpecial) {
                $errores[] = "La contraseña no cumple con los requisitos mínimos.";
            }

            $userModel = new User();

            // Verificar que el correo no exista
            if ($userModel->existsByCorreo($correo)) {
                $errores[] = "Ya existe un usuario registrado con ese correo.";
            }

            // ---------- Si todo está OK, preparamos nombre y apellido ----------
            if (empty($errores)) {

                // Separar nombre completo en nombre y apellido (simple)
                $nombre   = '';
                $apellido = '';

                if ($nombre_completo !== '') {
                    // separa por espacios
                    $partes = preg_split('/\s+/', $nombre_completo);
                    $nombre = array_shift($partes);          // primer nombre
                    $apellido = implode(' ', $partes);       // resto como apellido (puede quedar vacío)
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);

                $nombre = $nombre_completo;

                $userModel->create([
                    'nombre'         => $nombre,
                    'correo'         => $correo,
                    'nombre_usuario' => $correo,   // o genera uno a partir del nombre si quieres
                    'celular'        => null,
                    'cargo'          => null,
                    'foto'           => null,
                    'password'       => $hash,
                ]);

                $_SESSION['flash_success'] = "Registro exitoso. ¡Ahora puedes iniciar sesión!";
                $this->redirect('auth/login');
            }
        }

        $this->view('auth/register', [
            'errores'        => $errores,
            'pageStyles'     => ['register'],
            'pageScripts'    => ['register'],
            'isRegisterPage' => true
        ]);
    }

    public function logout()
    {
        session_destroy();
        $this->redirect('auth/login');
    }
}

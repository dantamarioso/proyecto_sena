<?php
class UsuariosController extends Controller
{
    /* =========================================================
       HELPERS
    ========================================================== */

    private function requireAdmin()
    {
        if (!isset($_SESSION['user']) || ($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
            http_response_code(403);
            echo "Acceso denegado.";
            exit;
        }
    }

    /* =========================================================
       LISTAR / INDEX
    ========================================================== */
    public function index()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            return;
        }

        // Carga la vista de usuarios y asegura que los estilos y scripts
        // específicos de la página se incluyan (usuarios.css, usuarios.js)
        $this->view('usuarios/index', [
            'pageStyles'  => ['usuarios'],
            'pageScripts' => ['usuarios'],
        ]);
    }

    /* =========================================================
       CREAR USUARIO
    ========================================================== */
    public function crear()
    {
        $this->requireAdmin(); // solo admin crea usuarios

        $errores   = [];
        $userModel = new User();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $nombre_completo = trim($_POST['nombre_completo'] ?? '');
            $correo          = trim($_POST['correo'] ?? '');
            $nombreUsuario   = trim($_POST['nombre_usuario'] ?? '');
            $celular         = trim($_POST['celular'] ?? '');
            $cargo           = trim($_POST['cargo'] ?? '');
            $password        = $_POST['password']  ?? '';
            $password2       = $_POST['password2'] ?? '';
            $estado          = intval($_POST['estado'] ?? 1);
            $rol             = $_POST['rol'] ?? 'usuario';

            /* =========================
               VALIDACIONES
            ========================== */
            if (
                $nombre_completo === '' || $correo === '' || $nombreUsuario === '' ||
                $password === '' || $password2 === ''
            ) {
                $errores[] = "Todos los campos obligatorios deben estar completos.";
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El correo no es válido.";
            }

            if ($password !== $password2) {
                $errores[] = "Las contraseñas no coinciden.";
            }

            // Reglas de contraseña
            $hasLength  = strlen($password) >= 8;
            $hasUpper   = preg_match('/[A-Z]/', $password);
            $hasSpecial = preg_match('/[!@#$%^&*(),.?\":{}|<>_\-]/', $password);

            if (!$hasLength || !$hasUpper || !$hasSpecial) {
                $errores[] = "La contraseña no cumple con los requisitos mínimos.";
            }

            // Duplicados
            if ($userModel->existsByCorreo($correo)) {
                $errores[] = "Ese correo ya está registrado.";
            }

            if ($userModel->existsByNombreUsuario($nombreUsuario)) {
                $errores[] = "Ese nombre de usuario ya existe.";
            }

            /* =========================
               SUBIR FOTO (OPCIONAL)
            ========================== */
            $fotoRuta = null;

            if (!empty($_FILES['foto']['name'])) {

                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png'];

                if (!in_array($ext, $permitidas)) {
                    $errores[] = "Formato de imagen no permitido (solo JPG y PNG).";
                } else {
                    $nombreFoto  = "uploads/fotos/" . uniqid("foto_") . "." . $ext;
                    $rutaSistema = __DIR__ . "/../../public/" . $nombreFoto;

                    if (!is_dir(__DIR__ . "/../../public/uploads/fotos")) {
                        mkdir(__DIR__ . "/../../public/uploads/fotos", 0777, true);
                    }

                    move_uploaded_file($_FILES['foto']['tmp_name'], $rutaSistema);
                    $fotoRuta = $nombreFoto;
                }
            }

            /* =========================
               SI TODO OK → CREAR USUARIO
            ========================== */
            if (empty($errores)) {

                $userModel->create([
                    'nombre'         => $nombre_completo,
                    'correo'         => $correo,
                    'nombre_usuario' => $nombreUsuario,
                    'celular'        => $celular,
                    'cargo'          => $cargo,
                    'foto'           => $fotoRuta,
                    'password'       => password_hash($password, PASSWORD_DEFAULT),
                    'estado'         => $estado,
                    'rol'            => $rol,
                ]);

                $this->redirect('home/index');
                return;
            }
        }

        $this->view('usuarios/crear', [
            'errores'     => $errores,
            'pageStyles'  => ['usuarios'],
            'pageScripts' => ['usuarios'],
        ]);
    }

    /* =========================================================
       EDITAR USUARIO
    ========================================================== */
    public function editar()
    {
        $this->requireAdmin();

        $userModel = new User();
        $errores   = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id            = intval($_POST['id'] ?? 0);
            $nombre        = trim($_POST['nombre'] ?? '');
            $correo        = trim($_POST['correo'] ?? '');
            $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
            $celular       = trim($_POST['celular'] ?? '');
            $cargo         = trim($_POST['cargo'] ?? '');
            $estado        = intval($_POST['estado'] ?? 1);
            $password      = trim($_POST['password'] ?? '');
            $rol           = $_POST['rol'] ?? 'usuario';

            if ($id <= 0) {
                $errores[] = "Usuario inválido.";
            }

            if ($nombre === '' || $correo === '' || $nombreUsuario === '') {
                $errores[] = "Nombre, correo y usuario son obligatorios.";
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Correo inválido.";
            }

            /* ==========================
               SUBIR FOTO (OPCIONAL)
            =========================== */
            $fotoRuta = null;

            if (!empty($_FILES['foto']['name'])) {

                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png'];

                if (!in_array($ext, $permitidas)) {
                    $errores[] = "Formato de imagen no permitido. Solo JPG o PNG.";
                } else {
                    $nombreFoto      = "uploads/fotos/" . uniqid("foto_") . "." . $ext;
                    $fotoRutaSistema = __DIR__ . "/../../public/" . $nombreFoto;

                    if (!is_dir(__DIR__ . "/../../public/uploads/fotos")) {
                        mkdir(__DIR__ . "/../../public/uploads/fotos", 0777, true);
                    }

                    move_uploaded_file($_FILES['foto']['tmp_name'], $fotoRutaSistema);
                    $fotoRuta = $nombreFoto;
                }
            }

            if (empty($errores)) {

                $userModel->updateFull($id, [
                    'nombre'         => $nombre,
                    'correo'         => $correo,
                    'nombre_usuario' => $nombreUsuario,
                    'celular'        => $celular,
                    'cargo'          => $cargo,
                    'estado'         => $estado,
                    'password'       => $password,  // si viene vacío NO se cambia
                    'foto'           => $fotoRuta,  // si es null NO se cambia
                    'rol'            => $rol,
                ]);

                $this->redirect('home/index');
                return;
            }

            $usuario = $userModel->findById($id);
        } else {

            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {
                $this->redirect('home/index');
            }

            $usuario = $userModel->findById($id);
        }

        $this->view('usuarios/editar', [
            'usuario'     => $usuario,
            'errores'     => $errores,
            'pageStyles'  => ['usuarios'],
            'pageScripts' => ['usuarios'],
        ]);
    }

    /* =========================================================
       BLOQUEAR
    ========================================================== */
    public function bloquear()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);

            if ($id > 0) {
                $userModel = new User();
                $userModel->updateEstado($id, 0);
            }
        }

        $this->redirect('home/index');
    }

    /* =========================================================
       DESBLOQUEAR
    ========================================================== */
    public function desbloquear()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);

            if ($id > 0) {
                $userModel = new User();
                $userModel->updateEstado($id, 1);
            }
        }

        $this->redirect('home/index');
    }

    /* =========================================================
       ELIMINAR
    ========================================================== */
    public function eliminar()
    {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);

            if ($id > 0) {
                $userModel = new User();
                $userModel->deleteById($id);
            }
        }

        $this->redirect('home/index');
    }

    /* =========================================================
       BUSCAR (AJAX) — Búsqueda, filtros, paginación
    ========================================================== */
    public function buscar()
    {
        $this->requireAdmin();

        header('Content-Type: application/json; charset=utf-8');

        $q      = trim($_GET['q']      ?? '');
        $estado = $_GET['estado']      ?? '';
        $rol    = $_GET['rol']         ?? '';
        $page   = max(1, (int)($_GET['page'] ?? 1));
        $perPage = 10;

        $offset = ($page - 1) * $perPage;

        $userModel = new User();

        $data  = $userModel->searchUsers($q, $estado, $rol, $perPage, $offset);
        $total = $userModel->countUsersFiltered($q, $estado, $rol);

        echo json_encode([
            'data'       => $data,
            'total'      => $total,
            'page'       => $page,
            'perPage'    => $perPage,
            'totalPages' => max(1, ceil($total / $perPage)),
        ]);
        exit;
    }
}

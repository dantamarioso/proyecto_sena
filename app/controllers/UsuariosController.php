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
        // Redirige a gestionDeUsuarios para mantener una sola vista consistente
        $this->redirect('usuarios/gestionDeUsuarios');
    }

    /* =========================================================
       GESTIÓN DE USUARIOS
    ========================================================== */
    public function gestionDeUsuarios()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            return;
        }

        // Verificar que sea admin
        if (($_SESSION['user']['rol'] ?? 'usuario') !== 'admin') {
            http_response_code(403);
            echo "Acceso denegado. Solo administradores pueden acceder a la gestión de usuarios.";
            exit;
        }

        require_once __DIR__ . "/../models/User.php";
        $userModel = new User();
        $currentId = $_SESSION['user']['id'] ?? null;

        // Carga inicial (page 1, sin filtros)
        if ($currentId) {
            $usuarios = $userModel->allExceptId($currentId);
        } else {
            $usuarios = $userModel->all();
        }

        $this->view('usuarios/gestion_de_usuarios', [
            'usuarios'    => $usuarios,
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

                $nuevoUsuarioId = $userModel->create([
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

                // Registrar en auditoría
                $auditModel = new Audit();
                $auditModel->registrarCambio(
                    $nuevoUsuarioId,
                    'usuarios',
                    $nuevoUsuarioId,
                    'crear',
                    [
                        'nombre' => [
                            'anterior' => 'N/A',
                            'nuevo' => $nombre_completo
                        ],
                        'correo' => [
                            'anterior' => 'N/A',
                            'nuevo' => $correo
                        ],
                        'nombre_usuario' => [
                            'anterior' => 'N/A',
                            'nuevo' => $nombreUsuario
                        ],
                        'rol' => [
                            'anterior' => 'N/A',
                            'nuevo' => $rol
                        ],
                        'estado' => [
                            'anterior' => 'N/A',
                            'nuevo' => $estado == 1 ? 'Activo' : 'Bloqueado'
                        ]
                    ],
                    $_SESSION['user']['id'] ?? null
                );

                $this->redirect('usuarios/gestionDeUsuarios');
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

            // Validar nombre de usuario (no debe existir otro igual, excepto el actual)
            $usuarioActual = $userModel->findById($id);
            if ($usuarioActual['nombre_usuario'] !== $nombreUsuario && $userModel->existsByNombreUsuario($nombreUsuario)) {
                $errores[] = "Ese nombre de usuario ya existe.";
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

                // Obtener datos anteriores antes de actualizar
                $usuarioAnterior = $userModel->findById($id);

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

                // Registrar en auditoría con antes y después
                $auditModel = new Audit();
                $cambios = [];

                // Comparar y registrar solo lo que cambió
                if ($usuarioAnterior['nombre'] !== $nombre) {
                    $cambios['nombre'] = [
                        'anterior' => $usuarioAnterior['nombre'],
                        'nuevo' => $nombre
                    ];
                }
                if ($usuarioAnterior['correo'] !== $correo) {
                    $cambios['correo'] = [
                        'anterior' => $usuarioAnterior['correo'],
                        'nuevo' => $correo
                    ];
                }
                if ($usuarioAnterior['nombre_usuario'] !== $nombreUsuario) {
                    $cambios['nombre_usuario'] = [
                        'anterior' => $usuarioAnterior['nombre_usuario'],
                        'nuevo' => $nombreUsuario
                    ];
                }
                if ($usuarioAnterior['celular'] !== $celular) {
                    $cambios['celular'] = [
                        'anterior' => $usuarioAnterior['celular'] ?? '(vacío)',
                        'nuevo' => $celular ?: '(vacío)'
                    ];
                }
                if ($usuarioAnterior['cargo'] !== $cargo) {
                    $cambios['cargo'] = [
                        'anterior' => $usuarioAnterior['cargo'] ?? '(vacío)',
                        'nuevo' => $cargo ?: '(vacío)'
                    ];
                }
                if ($usuarioAnterior['rol'] !== $rol) {
                    $cambios['rol'] = [
                        'anterior' => $usuarioAnterior['rol'],
                        'nuevo' => $rol
                    ];
                }
                if ($usuarioAnterior['estado'] != $estado) {
                    $cambios['estado'] = [
                        'anterior' => $usuarioAnterior['estado'] == 1 ? 'Activo' : 'Bloqueado',
                        'nuevo' => $estado == 1 ? 'Activo' : 'Bloqueado'
                    ];
                }
                if (!empty($password)) {
                    $cambios['contraseña'] = [
                        'anterior' => '***',
                        'nuevo' => '***'
                    ];
                }
                if (!empty($fotoRuta)) {
                    $cambios['foto'] = [
                        'anterior' => $usuarioAnterior['foto'] ?? '(ninguna)',
                        'nuevo' => $fotoRuta
                    ];
                }

                if (!empty($cambios)) {
                    $auditModel->registrarCambio(
                        $id,
                        'usuarios',
                        $id,
                        'actualizar',
                        $cambios,
                        $_SESSION['user']['id'] ?? null
                    );
                }

                $this->redirect('usuarios/gestionDeUsuarios');
                return;
            }

            $usuario = $userModel->findById($id);
        } else {

            $id = intval($_GET['id'] ?? 0);

            if ($id <= 0) {
                $this->redirect('usuarios/gestionDeUsuarios');
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
                $usuarioActual = $userModel->findById($id);
                
                $userModel->updateEstado($id, 0);
                
                // Registrar en auditoría con acción "desactivar/activar"
                $auditModel = new Audit();
                $auditModel->registrarCambio(
                    $id,
                    'usuarios',
                    $id,
                    'desactivar/activar',
                    [
                        'Acción' => 'Desactivado',
                        'Estado Anterior' => 'Activo',
                        'Estado Nuevo' => 'Inactivo'
                    ],
                    $_SESSION['user']['id']
                );
            }
        }

        $this->redirect('usuarios/gestionDeUsuarios');
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
                $usuarioActual = $userModel->findById($id);
                
                $userModel->updateEstado($id, 1);
                
                // Registrar en auditoría con acción "desactivar/activar"
                $auditModel = new Audit();
                $auditModel->registrarCambio(
                    $id,
                    'usuarios',
                    $id,
                    'desactivar/activar',
                    [
                        'Acción' => 'Activado',
                        'Estado Anterior' => 'Inactivo',
                        'Estado Nuevo' => 'Activo'
                    ],
                    $_SESSION['user']['id']
                );
            }
        }

        $this->redirect('usuarios/gestionDeUsuarios');
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
                $usuario = $userModel->findById($id);
                
                // Registrar en auditoría ANTES de eliminar el usuario
                $auditModel = new Audit();
                $auditModel->registrarCambio(
                    $id,
                    'usuarios',
                    $id,
                    'eliminar',
                    [
                        'nombre' => [
                            'anterior' => $usuario['nombre'] ?? 'Desconocido',
                            'nuevo' => 'N/A'
                        ],
                        'correo' => [
                            'anterior' => $usuario['correo'] ?? '',
                            'nuevo' => 'N/A'
                        ],
                        'nombre_usuario' => [
                            'anterior' => $usuario['nombre_usuario'] ?? '',
                            'nuevo' => 'N/A'
                        ],
                        'rol' => [
                            'anterior' => $usuario['rol'] ?? 'usuario',
                            'nuevo' => 'N/A'
                        ],
                        'estado' => [
                            'anterior' => ($usuario['estado'] ?? 1) == 1 ? 'Activo' : 'Bloqueado',
                            'nuevo' => 'N/A'
                        ]
                    ],
                    $_SESSION['user']['id'] ?? null
                );
                
                // DESPUÉS eliminar el usuario
                $userModel->deleteById($id);
            }
        }

        $this->redirect('usuarios/gestionDeUsuarios');
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

    /* =========================================================
       VERIFICAR NOMBRE DE USUARIO (AJAX)
    ========================================================== */
    public function verificarNombreUsuario()
    {
        $this->requireAdmin();

        $nombreUsuario = trim($_GET['nombre_usuario'] ?? '');
        $usuarioActualId = intval($_GET['usuario_id'] ?? 0);

        if (empty($nombreUsuario)) {
            echo json_encode(['existe' => false, 'mensaje' => '']);
            exit;
        }

        $userModel = new User();

        // Si es edición, no marcar como duplicado el mismo usuario
        if ($usuarioActualId > 0) {
            $usuarioActual = $userModel->findById($usuarioActualId);
            if ($usuarioActual && $usuarioActual['nombre_usuario'] === $nombreUsuario) {
                echo json_encode(['existe' => false, 'mensaje' => '']);
                exit;
            }
        }

        // Verificar si existe otro usuario con ese nombre
        $existe = $userModel->existsByNombreUsuario($nombreUsuario);

        echo json_encode([
            'existe' => (bool) $existe,
            'mensaje' => $existe ? 'Este nombre de usuario ya existe' : 'Nombre disponible'
        ]);
        exit;
    }
}

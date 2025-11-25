<?php
require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/Nodo.php";
require_once __DIR__ . "/../models/Material.php";
require_once __DIR__ . "/../helpers/PermissionHelper.php";

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

        $userModel = new User();
        $nodoModel = new Nodo();
        $currentId = $_SESSION['user']['id'] ?? null;

        // Carga inicial (page 1, sin filtros)
        if ($currentId) {
            $usuarios = $userModel->allExceptId($currentId);
        } else {
            $usuarios = $userModel->all();
        }

        // Obtener nodos con líneas para asignación
        $nodos = $nodoModel->getActivosConLineas();

        $this->view('usuarios/gestion_de_usuarios', [
            'usuarios'    => $usuarios,
            'nodos'       => $nodos,
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
        $nodoModel = new Nodo();

        // Obtener nodos con líneas para el formulario
        $nodos = $nodoModel->getActivosConLineas();

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
            $nodo_id         = !empty($_POST['nodo_id']) ? intval($_POST['nodo_id']) : null;
            $linea_id        = !empty($_POST['linea_id']) ? intval($_POST['linea_id']) : null;

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

            // Validar nodo si no es admin
            if ($rol !== 'admin' && $nodo_id === null) {
                $errores[] = "Debe asignar un nodo al usuario.";
            }

            // Validar línea si es usuario
            if ($rol === 'usuario' && $linea_id === null) {
                $errores[] = "Debe asignar una línea a los usuarios.";
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
                        mkdir(__DIR__ . "/../../public/uploads/fotos", 0755, true);
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
                    'nodo_id'        => $nodo_id,
                    'linea_id'       => $linea_id,
                    'email_verified' => 1
                ]);

                // Registrar en auditoría (no llamar asignarNodo aquí)
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
            'nodos'       => $nodos,
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
            $nodo_id       = !empty($_POST['nodo_id']) ? intval($_POST['nodo_id']) : null;
            $linea_id      = !empty($_POST['linea_id']) ? intval($_POST['linea_id']) : null;

            if ($id <= 0) {
                $errores[] = "Usuario inválido.";
            }

            if ($nombre === '' || $correo === '' || $nombreUsuario === '') {
                $errores[] = "Nombre, correo y usuario son obligatorios.";
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "Correo inválido.";
            }

            // Validar nodo y línea según rol
            if ($rol === 'admin') {
                // admin no necesita nodo/línea
            } elseif ($rol === 'dinamizador') {
                if (empty($nodo_id)) {
                    $errores[] = "Dinamizador debe tener un nodo asignado.";
                }
            } elseif ($rol === 'usuario') {
                if (empty($nodo_id) || empty($linea_id)) {
                    $errores[] = "Usuario debe tener nodo y línea asignados.";
                }
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
                        mkdir(__DIR__ . "/../../public/uploads/fotos", 0755, true);
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
                    'nodo_id'        => $nodo_id,
                    'linea_id'       => $linea_id,
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

                // Registrar cambios de nodo y línea
                if ($nodo_id !== null && $usuarioAnterior['nodo_id'] != $nodo_id) {
                    $cambios['nodo_id'] = [
                        'anterior' => $usuarioAnterior['nodo_id'] ?? 'Sin asignar',
                        'nuevo' => $nodo_id
                    ];
                }
                if ($linea_id !== null && $usuarioAnterior['linea_id'] != $linea_id) {
                    $cambios['linea_id'] = [
                        'anterior' => $usuarioAnterior['linea_id'] ?? 'Sin asignar',
                        'nuevo' => $linea_id
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

        // Obtener nodos con líneas para el formulario
        $nodoModel = new Nodo();
        $nodos = $nodoModel->getActivosConLineas();

        $this->view('usuarios/editar', [
            'usuario'     => $usuario,
            'nodos'       => $nodos,
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
                
                // Registrar en auditoría
                $auditModel = new Audit();
                $auditModel->registrarCambio(
                    $id,
                    'usuarios',
                    $id,
                    'actualizar',
                    [
                        'estado' => [
                            'anterior' => 'Activo',
                            'nuevo' => 'Inactivo'
                        ]
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
                
                // Registrar en auditoría
                $auditModel = new Audit();
                $auditModel->registrarCambio(
                    $id,
                    'usuarios',
                    $id,
                    'actualizar',
                    [
                        'estado' => [
                            'anterior' => 'Inactivo',
                            'nuevo' => 'Activo'
                        ]
                    ],
                    $_SESSION['user']['id']
                );
            }
        }

        $this->redirect('usuarios/gestionDeUsuarios');
    }

    /* =========================================================
       ASIGNAR NODO Y LÍNEA
    ========================================================== */
    public function asignarNodo()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $usuario_id = intval($_POST['usuario_id'] ?? 0);
        $nodo_id = intval($_POST['nodo_id'] ?? 0);
        $linea_id = !empty($_POST['linea_id']) ? intval($_POST['linea_id']) : null;

        // Log de debug
        file_put_contents(__DIR__ . '/../asignarNodo.log', date('Y-m-d H:i:s') . " - usuario_id: $usuario_id, nodo_id: $nodo_id, linea_id: " . ($linea_id ?? 'NULL') . "\n", FILE_APPEND);

        if ($usuario_id <= 0 || $nodo_id <= 0) {
            echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
            exit;
        }

        $userModel = new User();
        
        // Verificar que el usuario existe
        $usuario = $userModel->findById($usuario_id);
        if (!$usuario) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        // Validar que si es usuario, tiene línea asignada
        if ($usuario['rol'] === 'usuario' && $linea_id === null) {
            echo json_encode(['success' => false, 'message' => 'Los usuarios deben tener una línea asignada']);
            exit;
        }

        // Asignar nodo y línea
        if ($userModel->asignarNodo($usuario_id, $nodo_id, $linea_id)) {
            // Registrar en auditoría
            $auditModel = new Audit();
            $auditModel->registrarCambio(
                $usuario_id,
                'usuarios',
                $usuario_id,
                'actualizar',
                [
                    'nodo_id' => ['anterior' => $usuario['nodo_id'] ?? 'Sin asignar', 'nuevo' => $nodo_id],
                    'linea_id' => ['anterior' => $usuario['linea_id'] ?? 'Sin asignar', 'nuevo' => $linea_id ?? 'Sin asignar']
                ],
                $_SESSION['user']['id']
            );

            echo json_encode(['success' => true, 'message' => 'Nodo y línea asignados correctamente']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al asignar nodo y línea']);
        }
        exit;
    }

    /* =========================================================
       ELIMINAR
    ========================================================== */
    public function eliminar()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = intval($_POST['id'] ?? 0);

            if ($id <= 0) {
                echo json_encode(['success' => false, 'message' => 'ID de usuario inválido']);
                exit;
            }

            $userModel = new User();
            $usuario = $userModel->findById($id);
            
            if (!$usuario) {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
                exit;
            }
            
            // Registrar en auditoría ANTES de eliminar el usuario
            require_once __DIR__ . '/../models/Audit.php';
            $auditModel = new Audit();
            $auditModel->registrarCambio(
                $_SESSION['user']['id'],
                'usuarios',
                $id,
                'eliminar',
                json_encode([
                    'id' => $usuario['id'],
                    'nombre' => $usuario['nombre'] ?? 'Desconocido',
                    'correo' => $usuario['correo'] ?? '',
                    'nombre_usuario' => $usuario['nombre_usuario'] ?? '',
                    'rol' => $usuario['rol'] ?? 'usuario',
                    'estado' => ($usuario['estado'] ?? 1) == 1 ? 'Activo' : 'Bloqueado'
                ]),
                $_SESSION['user']['id']
            );
            
            // DESPUÉS eliminar el usuario
            $userModel->deleteById($id);
            
            echo json_encode(['success' => true, 'message' => 'Usuario eliminado exitosamente']);
            exit;
        }

        echo json_encode(['success' => false, 'message' => 'Método no permitido']);
        exit;
    }

    /* =========================================================
       BUSCAR (AJAX) — Búsqueda, filtros, paginación
    ========================================================== */
    public function buscar()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAdmin();

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
       VERIFICAR EMAIL (AJAX)
    ========================================================== */
    public function verificarEmail()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAdmin();

        $email = trim($_GET['email'] ?? '');
        $usuarioActualId = intval($_GET['usuario_id'] ?? 0);

        if (empty($email)) {
            echo json_encode(['existe' => false, 'mensaje' => '']);
            exit;
        }

        $userModel = new User();

        // Si es edición, no marcar como duplicado el mismo usuario
        if ($usuarioActualId > 0) {
            $usuarioActual = $userModel->findById($usuarioActualId);
            if ($usuarioActual && $usuarioActual['correo'] === $email) {
                echo json_encode(['existe' => false, 'mensaje' => '']);
                exit;
            }
        }

        // Verificar si existe otro usuario con ese email
        $existe = $userModel->existsByCorreo($email);

        echo json_encode([
            'existe' => (bool) $existe,
            'mensaje' => $existe ? 'Este correo ya está registrado' : 'Email disponible'
        ]);
        exit;
    }

    /* =========================================================
       VERIFICAR NOMBRE DE USUARIO (AJAX)
    ========================================================== */
    public function verificarNombreUsuario()
    {
        header('Content-Type: application/json; charset=utf-8');
        
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

    /* =========================================================
       VER DETALLES DE USUARIO
    ========================================================== */
    public function detalles()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            return;
        }

        $userId = intval($_GET['id'] ?? 0);
        
        // Validar que sea admin o que vea su propio perfil
        if (($_SESSION['user']['rol'] ?? 'usuario') !== 'admin' && $_SESSION['user']['id'] !== $userId) {
            http_response_code(403);
            echo "Acceso denegado.";
            exit;
        }

        if ($userId <= 0) {
            http_response_code(404);
            echo "Usuario no encontrado.";
            exit;
        }

        $userModel = new User();
        $usuario = $userModel->findById($userId);

        if (!$usuario) {
            http_response_code(404);
            echo "Usuario no encontrado.";
            exit;
        }

        // Obtener información de nodo y línea
        $nodo_nombre = '';
        $linea_nombre = '';
        
        if ($usuario['nodo_id']) {
            $nodoModel = new Nodo();
            $nodo = $nodoModel->getById($usuario['nodo_id']);
            $nodo_nombre = $nodo ? $nodo['nombre'] : '';
        }

        if ($usuario['linea_id']) {
            $materialModel = new Material();
            $linea = $materialModel->getLineaById($usuario['linea_id']);
            $linea_nombre = $linea ? $linea['nombre'] : '';
        }

        // Obtener archivos subidos por este usuario
        require_once __DIR__ . '/../models/MaterialArchivo.php';
        $archivoModel = new MaterialArchivo();
        $archivos = $archivoModel->getByUsuario($userId);

        // Obtener historial de cambios de auditoría para este usuario
        $auditModel = new Audit();
        $historialCambios = $auditModel->obtenerHistorialCompleto(500, 0, [
            'usuario_id' => $userId,
            'tabla' => 'usuarios'
        ]);

        $this->view('usuarios/detalles', [
            'usuario'     => $usuario,
            'archivos'    => $archivos,
            'nodo_nombre' => $nodo_nombre,
            'linea_nombre' => $linea_nombre,
            'historialCambios' => $historialCambios,
            'pageStyles'  => ['perfil'],
            'pageScripts' => [],
        ]);
    }

    /* =========================================================
       CONTAR DOCUMENTOS POR USUARIO (AJAX)
    ========================================================== */
    public function contarDocumentos()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAdmin();

        $usuarioId = intval($_GET['usuario_id'] ?? 0);

        if ($usuarioId <= 0) {
            echo json_encode(['count' => 0, 'error' => 'Usuario inválido']);
            exit;
        }

        require_once __DIR__ . '/../models/MaterialArchivo.php';
        $archivoModel = new MaterialArchivo();
        $count = $archivoModel->countByUsuario($usuarioId);

        echo json_encode(['count' => $count]);
        exit;
    }

    /* =========================================================
       DIAGNOSTICAR NODOS Y LÍNEAS (AJAX DEBUG)
    ========================================================== */
    public function diagnostico()
    {
        header('Content-Type: application/json; charset=utf-8');
        
        $this->requireAdmin();

        try {
            $nodoModel = new Nodo();
            
            // Obtener todos los nodos con líneas
            $nodos = $nodoModel->getActivosConLineas();
            
            // Información de diagnóstico
            $diagnostico = [
                'total_nodos' => count($nodos),
                'nodos_data' => $nodos,
                'problemas' => []
            ];
            
            // Verificar problemas comunes
            if (empty($nodos)) {
                $diagnostico['problemas'][] = 'No hay nodos activos en la base de datos';
            }
            
            foreach ($nodos as $idx => $nodo) {
                if (!isset($nodo['lineas'])) {
                    $diagnostico['problemas'][] = "Nodo {$nodo['nombre']} (ID: {$nodo['id']}) no tiene la propiedad 'lineas'";
                }
                if (empty($nodo['lineas'])) {
                    $diagnostico['problemas'][] = "Nodo {$nodo['nombre']} (ID: {$nodo['id']}) no tiene líneas asociadas";
                }
            }
            
            echo json_encode($diagnostico);
        } catch (Exception $e) {
            echo json_encode([
                'error' => $e->getMessage(),
                'total_nodos' => 0,
                'nodos_data' => [],
                'problemas' => [$e->getMessage()]
            ]);
        }
        exit;
    }
}

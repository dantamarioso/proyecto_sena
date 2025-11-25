<?php

class PerfilController extends Controller
{
    /* =========================================================
       VER PERFIL DEL USUARIO ACTUAL
    ========================================================== */
    public function ver()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            return;
        }

        $userModel = new User();
        $userId = $_SESSION['user']['id'];
        $usuario = $userModel->findById($userId);

        $this->view('perfil/ver', [
            'usuario' => $usuario,
            'pageStyles'  => ['perfil'],
            'pageScripts' => ['perfil']
        ]);
    }

    /* =========================================================
       EDITAR PERFIL DEL USUARIO
    ========================================================== */
    public function editar()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            return;
        }

        $userModel = new User();
        $currentId = $_SESSION['user']['id'];
        $currentRol = $_SESSION['user']['rol'] ?? 'usuario';
        $errores = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {

            $id = intval($_POST['id'] ?? 0);

            // Validar que el usuario solo pueda editar su propio perfil
            // A menos que sea admin
            if ($id !== $currentId && $currentRol !== 'admin') {
                http_response_code(403);
                echo "Acceso denegado. Solo puedes editar tu propio perfil.";
                exit;
            }

            $nombre = trim($_POST['nombre'] ?? '');
            $correo = trim($_POST['correo'] ?? '');
            $nombreUsuario = trim($_POST['nombre_usuario'] ?? '');
            $celular = trim($_POST['celular'] ?? '');
            $cargo = trim($_POST['cargo'] ?? '');
            $password = trim($_POST['password'] ?? '');

            // Las restricciones para no-admin
            $estado = $currentRol === 'admin' ? intval($_POST['estado'] ?? 1) : null;
            $rol = $currentRol === 'admin' ? ($_POST['rol'] ?? 'usuario') : null;

            // Obtener datos anteriores para comparación
            $usuarioAnterior = $userModel->findById($id);

            // Validaciones básicas
            if ($nombre === '' || $correo === '') {
                $errores[] = "Nombre y correo son obligatorios.";
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                $errores[] = "El correo no es válido.";
            }

            // Verificar si el correo ya existe (solo si es diferente al actual)
            if ($correo !== $usuarioAnterior['correo'] && $userModel->existsByCorreo($correo)) {
                $errores[] = "Este correo ya está registrado en otra cuenta.";
            }

            // Subir foto (opcional)
            $fotoRuta = null;

            if (!empty($_FILES['foto']['name'])) {

                $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg', 'jpeg', 'png'];

                if (!in_array($ext, $permitidas)) {
                    $errores[] = "Formato de imagen no permitido. Solo JPG o PNG.";
                } else {
                    $nombreFoto = "uploads/fotos/" . uniqid("foto_") . "." . $ext;
                    $fotoRutaSistema = __DIR__ . "/../../public/" . $nombreFoto;

                    if (!is_dir(__DIR__ . "/../../public/uploads/fotos")) {
                        mkdir(__DIR__ . "/../../public/uploads/fotos", 0777, true);
                    }

                    move_uploaded_file($_FILES['foto']['tmp_name'], $fotoRutaSistema);
                    $fotoRuta = $nombreFoto;
                }
            }

            if (empty($errores)) {

                // Detectar si el correo cambió
                $correoAhora = $usuarioAnterior['correo'];
                $correoCambio = ($correo !== $correoAhora);

                // Preparar datos para actualizar
                $dataActualizar = [
                    'nombre' => $nombre,
                    'nombre_usuario' => $nombreUsuario,
                    'celular' => $celular,
                    'cargo' => $cargo,
                    'password' => $password,
                    'foto' => $fotoRuta
                ];

                // Solo actualizar correo si no cambió
                if (!$correoCambio) {
                    $dataActualizar['correo'] = $correo;
                }

                // Si es admin, puede cambiar estado y rol
                if ($currentRol === 'admin') {
                    $dataActualizar['estado'] = $estado;
                    $dataActualizar['rol'] = $rol;
                }

                $userModel->updateFull($id, $dataActualizar);

                // Si el correo cambió, enviar verificación y redirigir
                if ($correoCambio) {
                    $verificationCode = rand(100000, 999999);
                    $userModel->saveVerificationCode($id, $verificationCode);

                    MailHelper::sendCode(
                        $correo,
                        "Código de verificación de nuevo email - Sistema Inventario",
                        $verificationCode,
                        'verificacion'
                    );

                    $_SESSION['pending_email_change'] = [
                        'user_id' => $id,
                        'new_email' => $correo,
                        'old_email' => $correoAhora
                    ];

                    $_SESSION['flash_success'] = "Se ha enviado un código de verificación al nuevo correo. Verifica tu bandeja de entrada.";
                    $this->redirect("perfil/verificarCambioCorreo");
                    return;
                }

                // Registrar en auditoría
                $auditModel = new Audit();
                $cambios = [];

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

                // Solo si es admin y cambió algo admin-specific
                if ($currentRol === 'admin') {
                    if ($usuarioAnterior['estado'] != $estado) {
                        $cambios['estado'] = [
                            'anterior' => $usuarioAnterior['estado'] == 1 ? 'Activo' : 'Bloqueado',
                            'nuevo' => $estado == 1 ? 'Activo' : 'Bloqueado'
                        ];
                    }
                    if ($usuarioAnterior['rol'] !== $rol) {
                        $cambios['rol'] = [
                            'anterior' => $usuarioAnterior['rol'],
                            'nuevo' => $rol
                        ];
                    }
                }

                if (!empty($cambios)) {
                    $auditModel->registrarCambio(
                        $id,
                        'usuarios',
                        $id,
                        'actualizar',
                        $cambios,
                        $currentId
                    );
                }

                // Actualizar sesión si es el usuario actual
                if ($id === $currentId) {
                    $_SESSION['user']['nombre'] = $nombre;
                    // SIEMPRE actualizar la foto: si hay nueva, usar esa; si no, usar la existente
                    if (!empty($fotoRuta)) {
                        $_SESSION['user']['foto'] = $fotoRuta . '?t=' . time();
                    } else {
                        // Asegurar que la foto en sesión esté sincronizada con BD
                        $usuarioActualizado = $userModel->findById($id);
                        if ($usuarioActualizado && !empty($usuarioActualizado['foto'])) {
                            // Agregar timestamp para evitar caché
                            $_SESSION['user']['foto'] = $usuarioActualizado['foto'] . '?t=' . time();
                        }
                    }
                }

                $_SESSION['flash_success'] = "Perfil actualizado exitosamente.";
                $this->redirect("perfil/ver");
                return;
            }

            $usuario = $userModel->findById($id);
        } else {

            $id = intval($_GET['id'] ?? $currentId);

            $usuario = $userModel->findById($id);

            // Validar acceso
            if ($id !== $currentId && $currentRol !== 'admin') {
                http_response_code(403);
                echo "Acceso denegado.";
                exit;
            }
        }

        $this->view('perfil/editar', [
            'usuario' => $usuario,
            'errores' => $errores,
            'isOwnProfile' => ($id === $currentId),
            'currentRol' => $currentRol,
            'pageStyles'  => ['perfil'],
            'pageScripts' => ['perfil']
        ]);
    }

    /* =========================================================
       CAMBIAR FOTO DE PERFIL (MODAL RÁPIDO)
    ========================================================== */
    public function cambiarFoto()
    {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autenticado']);
            exit;
        }

        header('Content-Type: application/json; charset=utf-8');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_FILES['foto'])) {
            echo json_encode(['success' => false, 'message' => 'No se envió archivo']);
            exit;
        }

        $userModel = new User();
        $userId = $_SESSION['user']['id'];
        $usuario = $userModel->findById($userId);

        $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png'];

        if (!in_array($ext, $permitidas)) {
            echo json_encode(['success' => false, 'message' => 'Formato no permitido. Solo JPG o PNG.']);
            exit;
        }

        $nombreFoto = "uploads/fotos/" . uniqid("foto_") . "." . $ext;
        $rutaSistema = __DIR__ . "/../../public/" . $nombreFoto;

        if (!is_dir(__DIR__ . "/../../public/uploads/fotos")) {
            mkdir(__DIR__ . "/../../public/uploads/fotos", 0777, true);
        }

        if (move_uploaded_file($_FILES['foto']['tmp_name'], $rutaSistema)) {

            // Eliminar foto anterior si existe
            if (!empty($usuario['foto'])) {
                $fotoAnterior = __DIR__ . "/../../public/" . $usuario['foto'];
                if (file_exists($fotoAnterior)) {
                    unlink($fotoAnterior);
                }
            }

            // Actualizar foto en BD
            $userModel->updateFull($userId, [
                'foto' => $nombreFoto
            ]);

            // Actualizar sesión con timestamp para evitar caché
            $_SESSION['user']['foto'] = $nombreFoto . '?t=' . time();

            // Registrar en auditoría
            $auditModel = new Audit();
            $auditModel->registrarCambio(
                $userId,
                'usuarios',
                $userId,
                'actualizar',
                [
                    'foto' => [
                        'anterior' => $usuario['foto'] ?? '(ninguna)',
                        'nuevo' => $nombreFoto
                    ]
                ],
                $userId
            );

            echo json_encode([
                'success' => true,
                'message' => 'Foto actualizada exitosamente',
                'foto' => BASE_URL . '/' . $nombreFoto
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al subir la foto']);
        }
        exit;
    }

    /* =========================================================
       VERIFICAR CAMBIO DE CORREO
    ========================================================== */
    public function verificarCambioCorreo()
    {
        if (!isset($_SESSION['user'])) {
            $this->redirect('auth/login');
            return;
        }

        if (!isset($_SESSION['pending_email_change'])) {
            $this->redirect('perfil/ver');
            return;
        }

        $userModel = new User();
        $remainingCooldown = 0;
        $userId = $_SESSION['pending_email_change']['user_id'];
        $newEmail = $_SESSION['pending_email_change']['new_email'];

        $user = $userModel->findById($userId);
        if ($user) {
            $remainingCooldown = $userModel->getRemainingCooldownTime($userId);
        }

        // POST: Verificar código
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = trim($_POST['codigo'] ?? '');

            if ($codigo === '') {
                $_SESSION['flash_error'] = "Ingresa el código de verificación.";
                $this->redirect('perfil/verificarCambioCorreo');
                return;
            }

            // Verificar código
            if ($userModel->verifyEmailCodeById($userId, $codigo)) {
                // Actualizar correo en la BD
                $userModel->updateFull($userId, [
                    'correo' => $newEmail
                ]);

                // Limpiar los campos de verificación
                $userModel->clearVerificationCode($userId);

                // Actualizar sesión
                $_SESSION['user']['correo'] = $newEmail;

                // Registrar en auditoría
                $auditModel = new Audit();
                $auditModel->registrarCambio(
                    $userId,
                    'usuarios',
                    $userId,
                    'actualizar',
                    [
                        'correo' => [
                            'anterior' => $_SESSION['pending_email_change']['old_email'],
                            'nuevo' => $newEmail
                        ]
                    ],
                    $_SESSION['user']['id']
                );

                unset($_SESSION['pending_email_change']);
                $_SESSION['flash_success'] = "Correo verificado y actualizado exitosamente.";
                $this->redirect('perfil/ver');
                return;
            } else {
                $_SESSION['flash_error'] = "Código inválido o expirado.";
            }
        }

        // GET: Reenviar código
        if (isset($_GET['reenviar'])) {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
                      $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';

            if (!$userModel->canResendVerificationCode($userId)) {
                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(429);
                    echo json_encode(['success' => false, 'message' => 'Debes esperar 60 segundos antes de reenviar el código.']);
                    exit;
                }
                $_SESSION['flash_error'] = "Debes esperar 60 segundos antes de reenviar el código.";
            } else {
                $verificationCode = rand(100000, 999999);
                $userModel->saveVerificationCode($userId, $verificationCode);

                MailHelper::sendCode(
                    $newEmail,
                    "Código de verificación de nuevo email - Sistema Inventario",
                    $verificationCode,
                    'verificacion'
                );

                if ($isAjax) {
                    header('Content-Type: application/json; charset=utf-8');
                    http_response_code(200);
                    echo json_encode(['success' => true, 'message' => 'Código reenviado exitosamente al correo.']);
                    exit;
                }

                $_SESSION['flash_success'] = "Código reenviado al correo.";
            }
            
            if (!$isAjax) {
                $this->redirect('perfil/verificarCambioCorreo');
            }
            return;
        }

        $this->view('perfil/verificarCambioCorreo', [
            'newEmail' => $newEmail,
            'remainingCooldown' => $remainingCooldown,
            'pageStyles'  => ['login', 'recovery'],
            'pageScripts' => []
        ]);
    }
}
